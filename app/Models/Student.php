<?php

namespace App\Models;

use App\Support\Phone;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['nim', 'nama', 'angkatan', 'jurusan', 'no_wa', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    // Nomor selalu disimpan dalam format 62xxxxxxxxxx
    public function setNoWaAttribute($value): void
    {
        $this->attributes['no_wa'] = Phone::clean($value);
    }

    public function getWaValidAttribute(): bool
    {
        return Phone::isValid($this->no_wa);
    }

    public function scopeWaInvalid($query)
    {
        return $query->where(function ($w) {
            $w->whereNull('no_wa')->orWhereRaw("no_wa NOT REGEXP '^628[0-9]{7,11}$'");
        });
    }
}
