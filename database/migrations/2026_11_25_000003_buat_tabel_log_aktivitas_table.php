<?php
/**
 * NAMA FILE: 2026_11_25_000003_buat_tabel_log_aktivitas_table.php
 * FUNGSI: Membuat tabel log_aktivitas (hanya created_at, tanpa updated_at) untuk jejak aktivitas sistem.
 * CARA MENGUBAH: Jalankan via `php artisan migrate`.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DITAMBAHKAN TAHAP 9B: Tabel log_aktivitas hanya baca (tanpa updated_at)
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengguna_id')
                ->nullable()
                ->constrained('pengguna')
                ->nullOnDelete();
            $table->string('aksi', 40);
            $table->string('deskripsi', 255);
            $table->string('subjek_tipe', 120)->nullable();
            $table->unsignedBigInteger('subjek_id')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('aksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};