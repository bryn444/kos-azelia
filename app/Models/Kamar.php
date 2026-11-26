<?php
/**
 * NAMA FILE: Kamar.php (DITAMBAHKAN TAHAP 5A, TAHAP 8 & TAHAP 9A)
 * FUNGSI: Model Eloquent tabel 'kamar' dengan relasi, status seharusnya, dan sinkronisasi status.
 * HALAMAN: Seluruh modul Kamar & Beranda Pengelola.
 * CARA MENGUBAH: Sesuaikan logika penentuan status seharusnya pada statusSeharusnya().
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kamar extends Model
{
    protected $table = 'kamar';

    protected $fillable = [
        'nomor_kamar',
        'tarif',
        'status',
        'unggulan',
        'deskripsi',
    ];

    protected $casts = [
        'unggulan' => 'boolean',
    ];

    /**
     * Relasi ke penghuni aktif.
     * Alasan: Mengetahui penghuni yang sedang menempati kamar saat ini.
     */
    public function penghuniAktif(): HasOne
    {
        return $this->hasOne(Penghuni::class, 'kamar_id')->where('status', 'aktif');
    }

    /**
     * Relasi ke seluruh penghuni (aktif maupun keluar) yang pernah memakai kamar ini.
     * Alasan: Riwayat penghuni dan pembayaran harus tetap tersimpan.
     */
    public function penghuni(): HasMany
    {
        return $this->hasMany(Penghuni::class, 'kamar_id');
    }

    /**
     * Relasi ke seluruh foto kamar diurutkan dari urutan terkecil.
     * Alasan: Menampilkan galeri foto kamar secara konsisten.
     */
    public function fotos(): HasMany
    {
        return $this->hasMany(KamarFoto::class, 'kamar_id')->orderBy('urutan')->orderBy('id');
    }

    /**
     * Relasi untuk mengambil foto sampul utama.
     * Alasan: Mengambil foto utama kamar bila tersedia.
     */
    public function sampul(): HasOne
    {
        return $this->hasOne(KamarFoto::class, 'kamar_id')->where('is_sampul', true);
    }

    /**
     * Accessor untuk mendapatkan URL sampul atau placeholder.
     * Alasan: Menghindari gambar rusak bila kamar belum memiliki foto.
     */
    public function getSampulUrlAttribute(): string
    {
        $fotoSampul = $this->sampul()->first() ?? $this->fotos()->first();
        return $fotoSampul ? $fotoSampul->url : asset('img/kamar-placeholder.svg');
    }

    /**
     * Scope query untuk menyaring kamar yang berstatus unggulan.
     * Alasan: Memudahkan pengambilan daftar kamar unggulan.
     */
    public function scopeUnggulan($query)
    {
        return $query->where('unggulan', true);
    }

    /**
     * DITAMBAHKAN TAHAP 9A: Menentukan status kamar ('terisi' atau 'kosong') berdasarkan penghuni aktif.
     * Alasan: Sumber kebenaran status kamar adalah ada tidaknya penghuni berstatus aktif.
     */
    public function statusSeharusnya(): string
    {
        $adaPenghuniAktif = $this->relationLoaded('penghuniAktif')
            ? $this->penghuniAktif !== null
            : $this->penghuniAktif()->exists();

        return $adaPenghuniAktif ? 'terisi' : 'kosong';
    }

    /**
     * DITAMBAHKAN TAHAP 9A: Menyamakan kolom status kamar dengan statusSeharusnya() bila berbeda.
     * Alasan: Memperbaiki ketidaksinkronan status kamar secara aman.
     */
    public function sinkronStatus(): bool
    {
        $seharusnya = $this->statusSeharusnya();
        if ($this->status !== $seharusnya) {
            return $this->update(['status' => $seharusnya]);
        }

        return false;
    }
}