<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kamar extends Model
{
    protected $table = 'kamar';

    protected $fillable = [
        'nomor_kamar',
        'tarif',
        'status',
    ];

    public function penghuniAktif(): HasOne
    {
        return $this->hasOne(Penghuni::class, 'kamar_id')->where('status', 'aktif');
    }
}