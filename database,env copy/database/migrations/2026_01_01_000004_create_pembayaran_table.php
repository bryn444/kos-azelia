<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghuni')->onDelete('cascade');
            $table->date('tanggal_bayar');
            $table->unsignedInteger('jumlah')->default(550000);
            $table->string('periode_bulan', 20); // format: YYYY-MM
            $table->date('tanggal_jatuh_tempo');
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->text('alasan_batal')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};