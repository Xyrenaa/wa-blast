<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlastMessage extends Model
{
    // status: pending | sending | sent | failed | cancelled
    protected $fillable = ['blast_id', 'student_id', 'nama', 'no_wa', 'pesan', 'status', 'error', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function blast(): BelongsTo
    {
        return $this->belongsTo(Blast::class);
    }
}
