<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsImport implements WithMultipleSheets
{
    public StudentsSheetImport $sheet;

    public function __construct()
    {
        $this->sheet = new StudentsSheetImport();
    }

    // Hanya sheet pertama yang dibaca
    public function sheets(): array
    {
        return [0 => $this->sheet];
    }
}
