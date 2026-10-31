<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\JatuhTempoService;

class Penghuni extends Model
{
    protected $table = 'penghuni';

    protected $fillable = [
        'kamar_id',
        'nama_lengkap',
        'nomor_hp',
        'tanggal_masuk',
        'status',
        'tanggal_keluar',
    ];

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'penghuni_id');
    }

    public function tambahanWaktu(): HasMany
    {
        return $this->hasMany(TambahanWaktu::class, 'penghuni_id');
    }

    public function getStatusPembayaranAttribute(): array
    {
        return app(JatuhTempoService::class)->hitungStatusPenghuni($this);
    }
}