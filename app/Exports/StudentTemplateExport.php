<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StudentTemplateExport implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize
{
    public function headings(): array
    {
        return ['nim', 'nama', 'angkatan', 'jurusan', 'no_wa'];
    }

    public function array(): array
    {
        return [['24082010001', 'Contoh Nama', 2024, 'Sistem Informasi', '081234567890']];
    }

    // Kolom NIM dan no_wa berformat teks agar angka 0 di depan tidak hilang
    public function columnFormats(): array
    {
        return ['A' => NumberFormat::FORMAT_TEXT, 'E' => NumberFormat::FORMAT_TEXT];
    }
}
