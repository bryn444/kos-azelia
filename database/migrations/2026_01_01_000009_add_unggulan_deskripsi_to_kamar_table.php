<?php
/**
 * NAMA FILE: 2026_01_01_000009_add_unggulan_deskripsi_to_kamar_table.php
 * FUNGSI: Menambahkan kolom 'unggulan' dan 'deskripsi' ke tabel kamar yang sudah ada.
 * HALAMAN: Pengelola Kelola Kamar (Tambah & Ubah Kamar).
 * CARA MENGUBAH: Jalankan 'php artisan migrate' setelah mengubah file migrasi ini.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->boolean('unggulan')->default(false)->after('status');
            $table->text('deskripsi')->nullable()->after('unggulan');
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropColumn(['unggulan', 'deskripsi']);
        });
    }
};