<?php
/**
 * NAMA FILE: SinkronStatusKamarCommand.php (DITAMBAHKAN TAHAP 9A)
 * FUNGSI: Perintah Artisan untuk menyinkronkan status kamar dengan penghuni aktif.
 * CARA MENGUBAH: Jalankan via `php artisan kos:sinkron-status`.
 */

namespace App\Console\Commands;

use App\Models\Kamar;
use Illuminate\Console\Command;

class SinkronStatusKamarCommand extends Command
{
    protected $signature = 'kos:sinkron-status';

    protected $description = 'Menyinkronkan status kamar (kosong/terisi) berdasarkan data penghuni aktif';

    /**
     * Menjalankan pemeriksaan dan perbaikan status seluruh kamar.
     * Alasan: Memungkinkan sinkronisasi data dari terminal/scheduler tanpa membuka halaman web.
     */
    public function handle(): int
    {
        $kamars = Kamar::with('penghuniAktif')->orderBy('nomor_kamar')->get();
        $diperbaiki = 0;

        foreach ($kamars as $kamar) {
            $seharusnya = $kamar->statusSeharusnya();
            if ($kamar->status !== $seharusnya) {
                $statusLama = $kamar->status;
                $kamar->sinkronStatus();
                $diperbaiki++;
                $this->line("Kamar {$kamar->nomor_kamar}: {$statusLama} -> {$seharusnya}");
            }
        }

        if ($diperbaiki === 0) {
            $this->info('Semua status kamar sudah sesuai dengan penghuni aktif.');
        } else {
            $this->info("Selesai. {$diperbaiki} kamar berhasil disinkronkan.");
        }

        return self::SUCCESS;
    }
}