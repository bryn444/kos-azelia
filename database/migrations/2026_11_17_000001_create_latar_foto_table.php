<?php

/**
 * NAMA FILE: database/migrations/2026_11_17_000001_create_latar_foto_table.php
 * FUNGSI: Membuat tabel `latar_foto` untuk menyimpan daftar foto latar belakang halaman
 *         calon penghuni beserta rasio asli, jangkar atas/bawah, dan koordinat Laptop & HP (satuan cqw).
 * HALAMAN: Halaman publik calon penghuni dan halaman pengelola latar belakang (Tahap 6A & 6B).
 * CARA MENGUBAH:
 * - Ubah nilai default posisi/ukuran di bawah jika ingin posisi awal foto yang baru diunggah berbeda.
 * - Setelah mengubah migrasi, jalankan `php artisan migrate:fresh` (khusus pengembangan lokal)
 *   atau buat migrasi baru agar perubahan struktur kolom diterapkan ke database.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('latar_foto', function (Blueprint $table) {
            $table->id();
            // Area penempatan latar ('utama' untuk seluruh halaman sekarang, 'header' disiapkan untuk nanti)
            $table->string('area', 20)->default('utama')->index();
            // Path file pada disk public (contoh: latar/abc123.png)
            $table->string('path');
            $table->string('nama_asli')->nullable();
            // Rasio tinggi dibagi lebar asli gambar (diisi otomatis lewat getimagesize saat unggah)
            $table->decimal('rasio', 8, 5)->default(1.00000);
            // Urutan lapisan DOM (semakin besar semakin di depan)
            $table->unsignedInteger('urutan')->default(0);
            // Status tampil di halaman publik
            $table->boolean('aktif')->default(false);

            // Susunan Laptop (jangkar: 'atas' atau 'bawah'; x, y, lebar dalam satuan cqw; rot dalam derajat)
            $table->string('jangkar', 10)->default('atas');
            $table->decimal('lap_x', 7, 2)->default(50);
            $table->decimal('lap_y', 7, 2)->default(40);
            $table->decimal('lap_lebar', 7, 2)->default(30);
            $table->decimal('lap_rot', 7, 2)->default(0);

            // Susunan HP (nullable; jika null otomatis mengikuti susunan Laptop)
            $table->string('hp_jangkar', 10)->nullable();
            $table->decimal('hp_x', 7, 2)->nullable();
            $table->decimal('hp_y', 7, 2)->nullable();
            $table->decimal('hp_lebar', 7, 2)->nullable();
            $table->decimal('hp_rot', 7, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('latar_foto');
    }
};