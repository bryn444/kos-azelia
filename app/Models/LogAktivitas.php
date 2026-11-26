<?php
/**
 * NAMA FILE: LogAktivitas.php (DITAMBAHKAN TAHAP 9B)
 * FUNGSI: Model Eloquent untuk tabel log_aktivitas (bersifat append-only / hanya baca di antarmuka).
 * HALAMAN: Log Aktivitas Pengelola & Pemilik.
 * CARA MENGUBAH: Sesuaikan relasi atau format tampilan bila diperlukan.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'pengguna_id',
        'aksi',
        'deskripsi',
        'subjek_tipe',
        'subjek_id',
        'data_lama',
        'data_baru',
        'ip',
        'created_at',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke pengguna yang melakukan aktivitas.
     * Alasan: Menampilkan nama/peran pelaku aktivitas pada daftar log.
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
}