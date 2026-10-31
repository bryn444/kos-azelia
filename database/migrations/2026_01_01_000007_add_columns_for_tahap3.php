<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penghuni', function (Blueprint $table) {
            $table->date('tanggal_keluar')->nullable()->after('status');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->foreignId('dicatat_oleh')->nullable()->after('alasan_batal')->constrained('pengguna')->onDelete('set null');
        });

        Schema::table('tambahan_waktu', function (Blueprint $table) {
            $table->foreignId('dibuat_oleh')->nullable()->after('is_aktif')->constrained('pengguna')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('penghuni', function (Blueprint $table) {
            $table->dropColumn('tanggal_keluar');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['dicatat_oleh']);
            $table->dropColumn('dicatat_oleh');
        });

        Schema::table('tambahan_waktu', function (Blueprint $table) {
            $table->dropForeign(['dibuat_oleh']);
            $table->dropColumn('dibuat_oleh');
        });
    }
};