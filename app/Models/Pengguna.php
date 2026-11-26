<?php
/**
 * NAMA FILE: Pengguna.php (DIPERBARUI TAHAP 9B)
 * FUNGSI: Model autentikasi tabel 'pengguna' dengan dukungan status wajib_ganti_password.
 * HALAMAN: Autentikasi, Panel Pengelola & Panel Pemilik.
 * CARA MENGUBAH: Tambahkan atribut baru ke $fillable atau $casts bila diperlukan.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'peran',
        'wajib_ganti_password', // DITAMBAHKAN TAHAP 9B
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            // DITAMBAHKAN TAHAP 9B: Casting wajib_ganti_password menjadi boolean
            'wajib_ganti_password' => 'boolean',
        ];
    }

    /**
     * Mengecek apakah pengguna memiliki peran pengelola.
     * Alasan: Memudahkan pengecekan hak akses panel pengelola.
     */
    public function isPengelola(): bool
    {
        return $this->peran === 'pengelola';
    }

    /**
     * Mengecek apakah pengguna memiliki peran pemilik.
     * Alasan: Memudahkan pengecekan hak akses panel pemilik.
     */
    public function isPemilik(): bool
    {
        return $this->peran === 'pemilik';
    }
}