<?php
/**
 * NAMA FILE: 2026_01_01_000010_create_kamar_foto_table.php
 * FUNGSI: Membuat tabel relasi 'kamar_foto' untuk menyimpan banyak foto per kamar.
 * HALAMAN: Pengelola Kelola Kamar.
 * CARA MENGUBAH: Ubah struktur kolom lalu jalankan 'php artisan migrate:fresh' atau rollback.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamar')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_sampul')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar_foto');
    }
};