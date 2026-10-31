<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformasiKos extends Model
{
    protected $table = 'informasi_kos';

    protected $fillable = [
        'kategori',
        'judul',
        'deskripsi',
    ];
}