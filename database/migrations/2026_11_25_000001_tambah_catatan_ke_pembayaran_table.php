<?php
/**
 * NAMA FILE: 2026_11_25_000001_tambah_catatan_ke_pembayaran_table.php
 * FUNGSI: Menambahkan kolom catatan opsional pada tabel pembayaran.
 * CARA MENGUBAH: Ubah panjang atau posisi kolom catatan bila diperlukan sebelum menjalankan migrate.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DITAMBAHKAN TAHAP 9A: Kolom catatan untuk mencatat alasan jika nominal bayar berbeda dari tarif kamar
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('catatan', 200)->nullable()->after('alasan_batal');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }
};