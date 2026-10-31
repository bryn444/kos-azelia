<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TambahanWaktu extends Model
{
    protected $table = 'tambahan_waktu';

    protected $fillable = [
        'penghuni_id',
        'tanggal_jatuh_tempo_baru',
        'alasan',
        'is_aktif',
        'dibuat_oleh',
    ];

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(Penghuni::class, 'penghuni_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }
}