<?php
/**
 * NAMA FILE: Kamar.php (DITAMBAHKAN TAHAP 5A & TAHAP 8)
 * FUNGSI: Model Eloquent tabel 'kamar' dengan tambahan relasi foto, relasi penghuni, scope unggulan, dan accessor sampul.
 * HALAMAN: Seluruh modul Kamar.
 * CARA MENGUBAH: Tambahkan method relasi atau accessor di bawah kelas ini.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kamar extends Model
{
    protected $table = 'kamar';

    // DITAMBAHKAN TAHAP 5A: Menambahkan field baru ke fillable
    protected $fillable = [
        'nomor_kamar',
        'tarif',
        'status',
        'unggulan',
        'deskripsi',
    ];

    // DITAMBAHKAN TAHAP 5A: Casting tipe data boolean
    protected $casts = [
        'unggulan' => 'boolean',
    ];

    /**
     * Relasi ke penghuni aktif.
     * Alasan: Memudahkan pengecekan penghuni yang sedang menempati kamar saat ini.
     */
    public function penghuniAktif(): HasOne
    {
        return $this->hasOne(Penghuni::class, 'kamar_id')->where('status', 'aktif');
    }

    /**
     * DITAMBAHKAN TAHAP 8: Relasi ke seluruh penghuni (aktif maupun keluar) yang pernah memakai kamar ini.
     * Alasan: Kamar tidak boleh dihapus jika pernah dihuni agar riwayat pembayaran penghuni tetap tersimpan.
     */
    public function penghuni(): HasMany
    {
        return $this->hasMany(Penghuni::class, 'kamar_id');
    }

    // DITAMBAHKAN TAHAP 5A: Relasi ke seluruh foto kamar diurutkan dari urutan terkecil
    public function fotos(): HasMany
    {
        return $this->hasMany(KamarFoto::class, 'kamar_id')->orderBy('urutan')->orderBy('id');
    }

    // DITAMBAHKAN TAHAP 5A: Relasi untuk mengambil foto sampul utama
    public function sampul(): HasOne
    {
        return $this->hasOne(KamarFoto::class, 'kamar_id')->where('is_sampul', true);
    }

    // DITAMBAHKAN TAHAP 5A: Accessor untuk mendapatkan URL sampul atau placeholder
    public function getSampulUrlAttribute(): string
    {
        $fotoSampul = $this->sampul()->first() ?? $this->fotos()->first();
        return $fotoSampul ? $fotoSampul->url : asset('img/kamar-placeholder.svg');
    }

    // DITAMBAHKAN TAHAP 5A: Scope query untuk menyaring kamar yang berstatus unggulan
    public function scopeUnggulan($query)
    {
        return $query->where('unggulan', true);
    }
}