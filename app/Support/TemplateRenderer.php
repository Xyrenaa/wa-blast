<?php

namespace App\Support;

use App\Models\Student;

final class TemplateRenderer
{
    /** Variabel yang otomatis diisi dari data mahasiswa. */
    public const STUDENT_VARS = ['nama_mahasiswa', 'nim', 'prodi', 'angkatan'];

    /** Daftar variabel {nama_variabel} yang dipakai di sebuah pesan. */
    public static function variables(string $isi): array
    {
        preg_match_all('/\{([a-z0-9_]+)\}/', $isi, $m);

        return array_values(array_unique($m[1]));
    }

    public static function studentValues(Student $s): array
    {
        return [
            'nama_mahasiswa' => $s->nama,
            'nim' => $s->nim,
            'prodi' => $s->jurusan,
            'angkatan' => (string) $s->angkatan,
        ];
    }

    /** Ganti {variabel} dengan nilainya. Variabel yang tidak ada nilainya dibiarkan apa adanya. */
    public static function render(string $isi, array $values): string
    {
        return preg_replace_callback(
            '/\{([a-z0-9_]+)\}/',
            fn ($m) => (string) ($values[$m[1]] ?? $m[0]),
            $isi
        );
    }

    /** Data contoh untuk pratinjau: mahasiswa pertama di database, atau data bawaan. */
    public static function sample(): array
    {
        $s = Student::orderBy('id')->first();

        return $s ? self::studentValues($s) : [
            'nama_mahasiswa' => 'Ahmad Rizky',
            'nim' => '22082010500',
            'prodi' => 'Sistem Informasi',
            'angkatan' => '2022',
        ];
    }
}
