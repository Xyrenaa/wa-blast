<?php

namespace App\Http\Controllers;

use App\Exports\BlastReportExport;
use App\Jobs\SendBlastMessage;
use App\Models\Blast;
use App\Models\BlastMessage;
use App\Models\MessageTemplate;
use App\Models\Student;
use App\Services\WhatsAppService;
use App\Support\Phone;
use App\Support\TemplateRenderer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class BlastController extends Controller
{
    public function index()
    {
        return view('blasts.index', ['blasts' => Blast::latest()->paginate(10)]);
    }

    public function create()
    {
        return view('blasts.create', [
            'templates' => MessageTemplate::orderBy('nama')->get(['id', 'nama', 'isi']),
            'angkatanList' => Student::select('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
            'jurusanList' => Student::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
            'studentVars' => TemplateRenderer::STUDENT_VARS,
            'sample' => TemplateRenderer::sample(),
        ]);
    }

    // Hitung penerima untuk filter yang dipilih (dipanggil lewat JavaScript)
    public function recipients(Request $r)
    {
        $q = $this->recipientQuery($r->angkatan, $r->jurusan);

        return response()->json([
            'total' => (clone $q)->count(),
            'invalid' => (clone $q)->waInvalid()->count(),
        ]);
    }

    public function store(Request $r, WhatsAppService $wa)
    {
        $data = $r->validate([
            'judul' => ['required', 'string', 'max:255'],
            'message_template_id' => ['nullable', 'exists:message_templates,id'],
            'isi_pesan' => ['required', 'string', 'max:4000'],
            'angkatan' => ['nullable', 'integer'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'delay_min' => ['required', 'integer', 'between:3,120'],
            'delay_max' => ['required', 'integer', 'gte:delay_min', 'max:255'],
            'kirim' => ['required', 'in:sekarang,jadwal'],
            'jadwal' => ['required_if:kirim,jadwal', 'nullable', 'date', 'after:now'],
            'vars' => ['nullable', 'array'],
            'vars.*' => ['nullable', 'string', 'max:500'],
        ], [
            'jadwal.required_if' => 'Pilih tanggal dan jam pengiriman.',
            'jadwal.date' => 'Format tanggal dan jam tidak valid.',
            'jadwal.after' => 'Waktu jadwal harus di masa depan.',
        ]);

        $scheduled = $data['kirim'] === 'jadwal' ? Carbon::parse($data['jadwal']) : null;
        $custom = $this->customValues($data['isi_pesan'], $data['vars'] ?? []);
        $query = $this->recipientQuery($data['angkatan'] ?? null, $data['jurusan'] ?? null);
        $total = (clone $query)->count();

        if ($total === 0) {
            return back()->withInput()->withErrors(['penerima' => 'Tidak ada mahasiswa aktif yang cocok dengan filter.']);
        }
        if (! $scheduled && ($wa->status()['state'] ?? 'offline') !== 'connected') {
            return back()->withInput()->withErrors(['wa' => 'WhatsApp belum terhubung. Hubungkan dulu di Dashboard.']);
        }

        $blast = DB::transaction(function () use ($data, $custom, $query, $total, $scheduled) {
            $blast = Blast::create([
                'judul' => $data['judul'],
                'message_template_id' => $data['message_template_id'] ?? null,
                'isi_pesan' => $data['isi_pesan'],
                'filter' => ['angkatan' => $data['angkatan'] ?? null, 'jurusan' => $data['jurusan'] ?? null, 'vars' => $custom],
                'status' => $scheduled ? 'scheduled' : 'queued',
                'scheduled_at' => $scheduled,
                'total' => $total,
                'delay_min' => $data['delay_min'],
                'delay_max' => $data['delay_max'],
                'created_by' => auth()->id(),
            ]);

            $now = now();
            $query->chunkById(500, function ($students) use ($blast, $custom, $data, $now) {
                $rows = [];
                foreach ($students as $s) {
                    $valid = Phone::isValid($s->no_wa);
                    $rows[] = [
                        'blast_id' => $blast->id,
                        'student_id' => $s->id,
                        'nama' => $s->nama,
                        'no_wa' => $s->no_wa,
                        'pesan' => TemplateRenderer::render($data['isi_pesan'], array_merge($custom, TemplateRenderer::studentValues($s))),
                        'status' => $valid ? 'pending' : 'failed',
                        'error' => $valid ? null : 'Nomor tidak valid',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                BlastMessage::insert($rows);
            });

            $blast->refreshCounts();

            return $blast;
        });

        if ($scheduled) {
            return redirect()->route('blasts.show', $blast)
                ->with('success', 'Blast dijadwalkan pada ' . $scheduled->format('d M Y H:i') . '. Pastikan penjadwal dan antrean menyala saat itu.');
        }

        SendBlastMessage::dispatch($blast->id);

        return redirect()->route('blasts.show', $blast)->with('success', 'Blast dimulai. Pastikan terminal "php artisan queue:work" sedang berjalan.');
    }

    public function show(Request $r, Blast $blast)
    {
        $c = $blast->messages()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $sent = (int) ($c['sent'] ?? 0);
        $failed = (int) ($c['failed'] ?? 0);
        $cancelled = (int) ($c['cancelled'] ?? 0);
        $pending = (int) ($c['pending'] ?? 0) + (int) ($c['sending'] ?? 0);
        $avg = ($blast->delay_min + $blast->delay_max) / 2;

        return view('blasts.show', [
            'blast' => $blast,
            'stats' => [
                'sent' => $sent, 'failed' => $failed, 'pending' => $pending, 'cancelled' => $cancelled,
                'percent' => $blast->total > 0 ? (int) floor(($sent + $failed + $cancelled) / $blast->total * 100) : 0,
                'eta_min' => (int) ceil($pending * $avg / 60),
            ],
            'messages' => $blast->messages()
                ->when($r->status, fn ($x, $s) => $x->where('status', $s))
                ->orderBy('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function cancel(Blast $blast)
    {
        if (! in_array($blast->status, ['scheduled', 'queued', 'running', 'paused'], true)) {
            return back();
        }
        $blast->update(['status' => 'cancelled', 'finished_at' => now()]);
        $blast->messages()->where('status', 'pending')->update(['status' => 'cancelled', 'updated_at' => now()]);
        $blast->refreshCounts();

        return back()->with('success', 'Blast dibatalkan. Pesan yang belum terkirim tidak akan dikirim.');
    }

    public function resume(Blast $blast)
    {
        if ($blast->status !== 'paused') {
            return back();
        }
        $blast->messages()->where('status', 'sending')
            ->update(['status' => 'failed', 'error' => 'Proses terhenti saat mengirim, cek manual di WhatsApp', 'updated_at' => now()]);
        $blast->update(['status' => 'running', 'started_at' => $blast->started_at ?? now()]);
        $blast->refreshCounts();
        SendBlastMessage::dispatch($blast->id);

        return back()->with('success', 'Pengiriman dilanjutkan.');
    }

    // Kirim ulang pesan gagal: nomor diperbarui dari Data Mahasiswa, lalu dikirim lagi
    public function retryFailed(Blast $blast)
    {
        if ($blast->status !== 'done') {
            return back()->withErrors(['retry' => 'Kirim ulang hanya bisa dilakukan setelah blast selesai.']);
        }

        $permanent = ['Nomor tidak valid', 'Nomor tidak terdaftar di WhatsApp'];
        $reset = 0;

        $blast->messages()->where('status', 'failed')->chunkById(200, function ($msgs) use (&$reset, $permanent) {
            $students = Student::whereIn('id', $msgs->pluck('student_id')->filter())->get()->keyBy('id');

            foreach ($msgs as $m) {
                $no = $students->get($m->student_id)?->no_wa ?? $m->no_wa;
                if (! Phone::isValid($no)) {
                    continue; // nomor masih salah
                }
                if ($no === $m->no_wa && in_array($m->error, $permanent, true)) {
                    continue; // nomor sama, hasilnya pasti sama
                }
                $m->update(['no_wa' => $no, 'status' => 'pending', 'error' => null, 'sent_at' => null]);
                $reset++;
            }
        });

        if ($reset === 0) {
            return back()->withErrors(['retry' => 'Tidak ada pesan yang bisa dikirim ulang. Perbaiki dulu nomor yang salah di Data Mahasiswa, lalu coba lagi.']);
        }

        $blast->update(['status' => 'queued', 'finished_at' => null]);
        $blast->refreshCounts();
        SendBlastMessage::dispatch($blast->id);

        return back()->with('success', $reset . ' pesan dikirim ulang.');
    }

    public function export(Blast $blast)
    {
        return Excel::download(new BlastReportExport($blast->id), 'laporan-blast-' . $blast->id . '.xlsx');
    }

    private function recipientQuery(?string $angkatan, ?string $jurusan)
    {
        return Student::query()
            ->where('is_active', true)
            ->when($angkatan, fn ($x, $v) => $x->where('angkatan', $v))
            ->when($jurusan, fn ($x, $v) => $x->where('jurusan', $v));
    }

    // Variabel selain data mahasiswa (mis. {tanggal_batas}) wajib diisi staf
    private function customValues(string $isi, array $vars): array
    {
        $out = [];
        foreach (array_diff(TemplateRenderer::variables($isi), TemplateRenderer::STUDENT_VARS) as $name) {
            $v = trim((string) ($vars[$name] ?? ''));
            if ($v === '') {
                throw ValidationException::withMessages(["vars.$name" => 'Isian {' . $name . '} wajib diisi.']);
            }
            $out[$name] = $v;
        }

        return $out;
    }
}
