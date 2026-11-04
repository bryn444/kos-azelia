<?php
/**
 * NAMA FILE: KamarFoto.php
 * FUNGSI: Model Eloquent untuk tabel 'kamar_foto' (relasi foto kamar).
 * HALAMAN: Modul Kamar Pengelola & Publik.
 * CARA MENGUBAH: Tambahkan atribut fillable atau custom accessor jika diperlukan.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class KamarFoto extends Model
{
    protected $table = 'kamar_foto';

    protected $fillable = [
        'kamar_id',
        'path',
        'urutan',
        'is_sampul',
    ];

    protected $casts = [
        'is_sampul' => 'boolean',
    ];

    /**
     * Relasi ke model Kamar (Many to One)
     */
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    /**
     * Accessor untuk mendapatkan URL lengkap foto dari storage
     */
    public function getUrlAttribute(): string
    {
        if ($this->path && Storage::disk('public')->exists($this->path)) {
            return asset('storage/' . $this->path);
        }
        return asset('img/kamar-placeholder.svg');
    }
}