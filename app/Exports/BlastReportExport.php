<?php

namespace App\Exports;

use App\Models\BlastMessage;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class BlastReportExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithCustomValueBinder
{
    private const STATUS = [
        'sent' => 'Terkirim', 'failed' => 'Gagal', 'pending' => 'Menunggu', 'sending' => 'Mengirim', 'cancelled' => 'Dibatalkan',
    ];

    public function __construct(private int $blastId)
    {
    }

    public function query()
    {
        return BlastMessage::where('blast_id', $this->blastId)->orderBy('id');
    }

    public function headings(): array
    {
        return ['Nama', 'No. WhatsApp', 'Status', 'Keterangan', 'Waktu kirim'];
    }

    public function map($m): array
    {
        return [$m->nama, $m->no_wa, self::STATUS[$m->status] ?? $m->status, $m->error, $m->sent_at?->format('d/m/Y H:i:s')];
    }

    // Kolom B (nomor) selalu teks agar tidak berubah jadi 6,28E+12
    public function bindValue(Cell $cell, $value): bool
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
