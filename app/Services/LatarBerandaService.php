<?php

/**
 * NAMA FILE: app/Services/LatarBerandaService.php
 * FUNGSI: Service penyedia data foto latar belakang aktif beserta susunan Laptop & HP
 *         yang siap dirender oleh Blade publik maupun editor Tahap 6B.
 * HALAMAN: Beranda publik (`PublikController@index`) dan editor Tahap 6B.
 * CARA MENGUBAH:
 * - Parameter `$area` default bernilai `'utama'`. Nanti saat fitur `'header'` dibuat,
 *   service ini bisa dipanggil dengan `'header'` tanpa mengubah struktur kodenya.
 */

namespace App\Services;

use App\Models\LatarFoto;
use Illuminate\Support\Facades\Schema;

class LatarBerandaService
{
    /**
     * Mengambil daftar foto aktif untuk area tertentu beserta nilai siap pakai Laptop dan HP.
     *
     * @return array<int, array{
     *     id: int,
     *     url: string,
     *     nama_asli: string|null,
     *     rasio: float,
     *     urutan: int,
     *     aktif: bool,
     *     laptop: array{jangkar: string, x: float, y: float, lebar: float, rot: float},
     *     hp: array{jangkar: string, x: float, y: float, lebar: float, rot: float}
     * }>
     */
    public function untukPublik(string $area = 'utama'): array
    {
        // Mencegah error apabila tabel belum dimigrasi saat instalasi awal
        if (!Schema::hasTable('latar_foto')) {
            return [];
        }

        return LatarFoto::query()
            ->area($area)
            ->aktif()
            ->terurut()
            ->get()
            ->map(function (LatarFoto $foto): array {
                return [
                    'id' => (int) $foto->id,
                    'url' => $foto->url,
                    'nama_asli' => $foto->nama_asli,
                    'rasio' => (float) $foto->rasio,
                    'urutan' => (int) $foto->urutan,
                    'aktif' => (bool) $foto->aktif,
                    'laptop' => $foto->nilaiPerangkat('laptop'),
                    'hp' => $foto->nilaiPerangkat('hp'),
                ];
            })
            ->values()
            ->all();
    }
}