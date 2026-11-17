<?php

/**
 * NAMA FILE: app/Models/LatarFoto.php
 * FUNGSI: Model Eloquent untuk tabel `latar_foto` dengan scope area & aktif, pengurutan lapisan,
 *         accessor URL publik, dan pembantu nilai posisi untuk perangkat Laptop maupun HP.
 * HALAMAN: Halaman publik calon penghuni dan panel pengelola latar belakang (6A & 6B).
 * CARA MENGUBAH:
 * - Pertahankan nama accessor `url` dan method `nilaiPerangkat()` karena dipakai langsung oleh
 *   `LatarBerandaService` serta akan dipakai ulang oleh editor interaktif Tahap 6B.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LatarFoto extends Model
{
    protected $table = 'latar_foto';

    protected $fillable = [
        'area',
        'path',
        'nama_asli',
        'rasio',
        'urutan',
        'aktif',
        'jangkar',
        'lap_x',
        'lap_y',
        'lap_lebar',
        'lap_rot',
        'hp_jangkar',
        'hp_x',
        'hp_y',
        'hp_lebar',
        'hp_rot',
    ];

    protected $casts = [
        'rasio' => 'float',
        'urutan' => 'integer',
        'aktif' => 'boolean',
        'lap_x' => 'float',
        'lap_y' => 'float',
        'lap_lebar' => 'float',
        'lap_rot' => 'float',
        'hp_x' => 'float',
        'hp_y' => 'float',
        'hp_lebar' => 'float',
        'hp_rot' => 'float',
    ];

    /**
     * Memfilter data berdasarkan area latar (misalnya 'utama').
     */
    public function scopeArea(Builder $query, string $area): Builder
    {
        return $query->where('area', $area);
    }

    /**
     * Memfilter hanya foto yang aktif ditampilkan di halaman publik.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    /**
     * Mengurutkan foto dari lapisan paling belakang ke depan (urutan kecil ke besar, lalu id).
     */
    public function scopeTerurut(Builder $query): Builder
    {
        return $query->orderBy('urutan', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Menghasilkan URL publik file memakai asset('storage/...') agar tetap akurat
     * meskipun alamat/port akses berbeda dengan APP_URL di .env.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim((string) $this->path, '/'));
    }

    /**
     * Mengambil susunan posisi (jangkar, x, y, lebar, rot) untuk 'laptop' atau 'hp'.
     * Jika perangkat 'hp' memiliki nilai null, otomatis jatuh ke nilai susunan Laptop.
     *
     * @return array{jangkar: string, x: float, y: float, lebar: float, rot: float}
     */
    public function nilaiPerangkat(string $perangkat = 'laptop'): array
    {
        $lapJangkar = in_array($this->jangkar, ['atas', 'bawah'], true) ? $this->jangkar : 'atas';
        $lapX = (float) $this->lap_x;
        $lapY = (float) $this->lap_y;
        $lapLebar = (float) $this->lap_lebar;
        $lapRot = (float) $this->lap_rot;

        if ($perangkat === 'hp') {
            $hpJangkarValid = in_array($this->hp_jangkar, ['atas', 'bawah'], true) ? $this->hp_jangkar : null;

            return [
                'jangkar' => $hpJangkarValid ?? $lapJangkar,
                'x' => $this->hp_x !== null ? (float) $this->hp_x : $lapX,
                'y' => $this->hp_y !== null ? (float) $this->hp_y : $lapY,
                'lebar' => $this->hp_lebar !== null ? (float) $this->hp_lebar : $lapLebar,
                'rot' => $this->hp_rot !== null ? (float) $this->hp_rot : $lapRot,
            ];
        }

        return [
            'jangkar' => $lapJangkar,
            'x' => $lapX,
            'y' => $lapY,
            'lebar' => $lapLebar,
            'rot' => $lapRot,
        ];
    }
}