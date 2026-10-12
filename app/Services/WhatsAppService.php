<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.wa.url'))
            ->withHeaders(['X-API-KEY' => config('services.wa.key')])
            ->acceptJson()
            ->timeout(20);
    }

    /** @return array{state:string, qr:?string, phone:?string} */
    public function status(): array
    {
        try {
            $res = $this->http()->get('/status');
            if ($res->successful()) {
                return $res->json();
            }
        } catch (ConnectionException $e) {
            // WA service mati / tidak terjangkau
        }

        return ['state' => 'offline', 'qr' => null, 'phone' => null];
    }

    public function logout(): bool
    {
        try {
            return (bool) $this->http()->post('/logout')->json('ok');
        } catch (ConnectionException $e) {
            return false;
        }
    }

    /** Dipakai di tahap blast. @return array{ok:bool, error?:string} */
    public function send(string $number, string $message): array
    {
        try {
            $res = $this->http()->timeout(45)->post('/send', ['number' => $number, 'message' => $message]);

            return $res->json() ?? ['ok' => false, 'error' => 'Respons WA service tidak valid'];
        } catch (ConnectionException $e) {
            // Timeout berbeda dari service mati: permintaan sudah sampai dan pesan mungkin sudah terkirim.
            // Jangan dianggap "WA mati" (nanti dikirim ulang saat Lanjutkan, jadi dobel); tandai gagal tidak pasti.
            if (str_contains($e->getMessage(), 'cURL error 28')) {
                return ['ok' => false, 'error' => 'WA service tidak merespons tepat waktu, pesan mungkin sudah terkirim. Cek manual di WhatsApp'];
            }

            return ['ok' => false, 'error' => 'WA service tidak bisa dihubungi'];
        }
    }
}