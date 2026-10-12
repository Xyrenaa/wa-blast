<?php

namespace App\Jobs;

use App\Models\Blast;
use App\Models\BlastMessage;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mengirim SATU pesan, lalu menjadwalkan dirinya lagi dengan jeda acak untuk pesan berikutnya.
 * Satu blast = satu rantai job, jadi mudah dijeda atau dibatalkan.
 */
class SendBlastMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    // Error yang berarti WhatsApp bermasalah (bukan salah penerima)
    private const WA_DOWN = ['WhatsApp belum terhubung', 'WA service tidak bisa dihubungi', 'Unauthorized'];

    // Request ke WA service paling lama 45 detik, jadi pesan yang masih "sending" lebih dari ini
    // pasti terputus (worker dimatikan/restart saat mengirim)
    private const STALE_SECONDS = 60;

    public function __construct(public int $blastId)
    {
    }

    public function handle(WhatsAppService $wa): void
    {
        $blast = Blast::find($this->blastId);
        if (! $blast || ! in_array($blast->status, ['queued', 'running'], true)) {
            return;
        }
        if ($blast->status === 'queued') {
            $blast->update(['status' => 'running', 'started_at' => now()]);
        }

        // Pesan yang menggantung (worker sempat mati) dianggap gagal, tidak dikirim ulang
        // karena bisa jadi sudah terkirim
        $blast->messages()->where('status', 'sending')->where('updated_at', '<', now()->subSeconds(self::STALE_SECONDS))
            ->update(['status' => 'failed', 'error' => 'Proses terhenti saat mengirim, status tidak pasti. Cek manual di WhatsApp', 'updated_at' => now()]);

        $msg = $blast->messages()->where('status', 'pending')->orderBy('id')->first();
        if (! $msg) {
            // Masih ada pesan yang sedang dikirim (atau job sebelumnya terputus): jangan tandai selesai dulu.
            // Cek lagi sebentar; kalau ternyata terputus, ia akan ditandai gagal oleh pembersihan di atas.
            if ($blast->messages()->where('status', 'sending')->exists()) {
                self::dispatch($blast->id)->delay(now()->addSeconds(15));

                return;
            }
            $this->finish($blast);

            return;
        }

        if (($wa->status()['state'] ?? 'offline') !== 'connected') {
            $blast->update(['status' => 'paused']);

            return;
        }

        // Klaim pesan ini; kalau sudah diambil proses lain, berhenti
        $claimed = BlastMessage::where('id', $msg->id)->where('status', 'pending')
            ->update(['status' => 'sending', 'updated_at' => now()]);
        if (! $claimed) {
            return;
        }
        $msg->refresh();

        $res = $wa->send($msg->no_wa, $msg->pesan);
        $ok = (bool) ($res['ok'] ?? false);
        $error = $res['error'] ?? 'Gagal mengirim';

        if (! $ok && in_array($error, self::WA_DOWN, true)) {
            $msg->update(['status' => 'pending']); // kembalikan, jangan dihitung gagal
            $blast->update(['status' => 'paused']);

            return;
        }

        if ($ok) {
            $msg->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
        } else {
            $msg->update(['status' => 'failed', 'error' => Str::limit($error, 250, '')]);
        }

        $blast->refreshCounts();
        $blast->refresh();
        if ($blast->status !== 'running') {
            return; // dibatalkan saat sedang mengirim
        }

        if (! $blast->messages()->where('status', 'pending')->exists()) {
            $this->finish($blast);

            return;
        }

        $delay = random_int($blast->delay_min, max($blast->delay_min, $blast->delay_max));
        self::dispatch($blast->id)->delay(now()->addSeconds($delay));
    }

    // Kalau job gagal total (mis. error sistem), jeda blast agar staf bisa menekan Lanjutkan
    public function failed(Throwable $e): void
    {
        Blast::where('id', $this->blastId)->whereIn('status', ['queued', 'running'])->update(['status' => 'paused']);
    }

    private function finish(Blast $blast): void
    {
        $blast->update(['status' => 'done', 'finished_at' => now()]);
        $blast->refreshCounts();
    }
}