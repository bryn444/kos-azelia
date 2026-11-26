<?php
/**
 * NAMA FILE: 2026_11_25_000002_tambah_wajib_ganti_password_ke_pengguna_table.php
 * FUNGSI: Menambahkan kolom wajib_ganti_password pada tabel pengguna dan mengaktifkannya pada semua akun yang ada.
 * CARA MENGUBAH: Jalankan via `php artisan migrate`.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DITAMBAHKAN TAHAP 9B: Tambah kolom wajib_ganti_password pada tabel pengguna
        Schema::table('pengguna', function (Blueprint $table) {
            $table->boolean('wajib_ganti_password')->default(false);
        });

        // DITAMBAHKAN TAHAP 9B: Set true untuk seluruh akun yang sudah ada agar wajib ganti sandi bawaan
        DB::table('pengguna')->update(['wajib_ganti_password' => true]);
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('wajib_ganti_password');
        });
    }
};