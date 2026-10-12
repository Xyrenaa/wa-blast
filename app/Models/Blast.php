<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blast extends Model
{
    protected $fillable = [
        'judul', 'message_template_id', 'isi_pesan', 'filter', 'status', 'scheduled_at', 'started_at',
        'finished_at', 'total', 'sent_count', 'failed_count', 'delay_min', 'delay_max', 'created_by',
    ];

    protected $casts = [
        'filter' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(BlastMessage::class);
    }

    // Hitung ulang terkirim/gagal dari tabel pesan (selalu akurat)
    public function refreshCounts(): void
    {
        $c = $this->messages()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $this->update([
            'sent_count' => (int) ($c['sent'] ?? 0),
            'failed_count' => (int) ($c['failed'] ?? 0),
        ]);
    }
}
