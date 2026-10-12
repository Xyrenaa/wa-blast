<?php

namespace App\Support;

final class Phone
{
    /** Bersihkan dan ubah ke format 62xxxxxxxxxx. Kembalikan null jika kosong. */
    public static function clean(mixed $value): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $value);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '0')) {
            return '62' . substr($d, 1);
        }
        if (str_starts_with($d, '8')) {
            return '62' . $d;
        }

        return $d; // sudah berawalan 62
    }

    /** Nomor seluler Indonesia: 628 + 7 sampai 11 digit. */
    public static function isValid(?string $value): bool
    {
        return $value !== null && preg_match('/^628\d{7,11}$/', $value) === 1;
    }
}
