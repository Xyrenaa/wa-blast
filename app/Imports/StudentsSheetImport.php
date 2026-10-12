<?php

namespace App\Imports;

use App\Models\Student;
use App\Support\Phone;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsSheetImport implements ToCollection, WithHeadingRow
{
    // Nama kolom yang dikenali (sudah dalam bentuk slug: huruf kecil, spasi/titik jadi _)
    private const ALIASES = [
        'nim' => ['nim', 'npm', 'nomor_induk', 'nomor_induk_mahasiswa', 'no_induk'],
        'nama' => ['nama', 'nama_lengkap', 'nama_mahasiswa', 'name'],
        'angkatan' => ['angkatan', 'tahun_masuk', 'tahun_angkatan', 'th_masuk'],
        'jurusan' => ['jurusan', 'prodi', 'program_studi', 'studi'],
        'no_wa' => ['no_wa', 'nomor_wa', 'no_whatsapp', 'nomor_whatsapp', 'whatsapp', 'wa', 'telepon',
            'no_telepon', 'nomor_telepon', 'telp', 'no_telp', 'hp', 'no_hp', 'nomor_hp', 'phone'],
    ];

    public int $done = 0;

    /** @var string[] */
    public array $skipped = [];

    /** @var string[] Ringkasan kolom mana yang terbaca */
    public array $mapping = [];

    public function collection(Collection $rows): void
    {
        $headers = $rows->isEmpty() ? [] : array_keys($rows->first()->toArray());

        $col = [];
        foreach (self::ALIASES as $field => $names) {
            $col[$field] = collect($names)->first(fn ($n) => in_array($n, $headers, true));
        }

        $this->mapping = [
            'NIM: ' . ($col['nim'] ? "kolom {$col['nim']}" : 'tidak ditemukan'),
            'Nama: ' . ($col['nama'] ? "kolom {$col['nama']}" : 'tidak ditemukan'),
            'Jurusan: ' . ($col['jurusan'] ? "kolom {$col['jurusan']}" : 'tidak ditemukan (diisi "-")'),
            'Angkatan: ' . ($col['angkatan'] ? "kolom {$col['angkatan']}" : '2 digit awal NIM'),
            'No. WhatsApp: ' . ($col['no_wa'] ? "kolom {$col['no_wa']}" : 'tidak ditemukan'),
        ];

        if (! $col['nim'] || ! $col['nama']) {
            $this->skipped[] = 'Kolom NIM dan Nama wajib ada. Beri nama kolom: nim (atau npm) dan nama.';

            return;
        }

        $now = now();
        $maxYear = (int) date('Y') + 1;
        $batch = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2; // baris 1 = judul kolom
            $get = fn (string $f) => $col[$f] ? $row->get($col[$f]) : null;

            $nim = $this->str($get('nim'));
            $nama = $this->str($get('nama'));
            if ($nim === '' && $nama === '') {
                continue; // baris kosong
            }
            if ($nim === '' || $nama === '') {
                $this->skipped[] = "Baris $line: NIM atau nama kosong";
                continue;
            }

            $angkatan = (int) $this->str($get('angkatan'));
            if ($angkatan > 0 && $angkatan < 100) {
                $angkatan += 2000; // "22" -> 2022
            }
            if ($angkatan < 2000 || $angkatan > $maxYear) {
                $angkatan = preg_match('/^(\d{2})/', $nim, $m) ? 2000 + (int) $m[1] : 0; // dari NIM
            }
            if ($angkatan < 2000 || $angkatan > $maxYear) {
                $this->skipped[] = "Baris $line ($nama): angkatan tidak bisa ditentukan";
                continue;
            }

            $jurusan = $this->str($get('jurusan'));

            $batch[$nim] = [ // dikunci per NIM: kalau ganda di file, baris terakhir dipakai
                'nim' => $nim,
                'nama' => $nama,
                'angkatan' => $angkatan,
                'jurusan' => $jurusan !== '' ? $jurusan : '-',
                'no_wa' => Phone::clean($this->str($get('no_wa'))),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // NIM yang sudah ada diperbarui, yang belum ada ditambahkan
        foreach (array_chunk(array_values($batch), 500) as $chunk) {
            Student::upsert($chunk, ['nim'], ['nama', 'angkatan', 'jurusan', 'no_wa', 'is_active', 'updated_at']);
        }

        $this->done = count($batch);
    }

    // Sel angka (mis. NIM) bisa terbaca sebagai float; ubah jadi teks tanpa notasi ilmiah
    private function str(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_int($v) || is_float($v)) {
            return number_format($v, 0, '', '');
        }

        return trim((string) $v);
    }
}
