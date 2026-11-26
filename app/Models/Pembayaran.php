<?php
/**
 * NAMA FILE: Pembayaran.php (DITAMBAHKAN TAHAP 9A)
 * FUNGSI: Model Eloquent tabel 'pembayaran' beserta relasi penghuni dan pencatat.
 * HALAMAN: Modul Pembayaran & Laporan.
 * CARA MENGUBAH: Tambahkan field baru ke $fillable jika ada kolom tambahan di tabel pembayaran.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'penghuni_id',
        'tanggal_bayar',
        'jumlah',
        'periode_bulan',
        'tanggal_jatuh_tempo',
        'dibatalkan_pada',
        'alasan_batal',
        'catatan', // DITAMBAHKAN TAHAP 9A: Catatan saat nominal bayar berbeda dari tarif kamar
        'dicatat_oleh',
    ];

    /**
     * Relasi ke data penghuni yang membayar.
     * Alasan: Menghubungkan transaksi pembayaran dengan profil penghuni.
     */
    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(Penghuni::class, 'penghuni_id');
    }

    /**
     * Relasi ke pengguna yang mencatat pembayaran.
     * Alasan: Mengetahui siapa pengelola yang mencatat transaksi tunai.
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dicatat_oleh');
    }
}