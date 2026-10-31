<?php

namespace App\Services;

use App\Models\Penghuni;
use Carbon\Carbon;

class JatuhTempoService
{
    /**
     * Hitung tanggal jatuh tempo berdasarkan tanggal masuk dan indeks bulan (periode ke-N).
     * Aturan: Tanggal Masuk + N bulan - 1 hari.
     */
    public function hitungJatuhTempo(string $tanggalMasuk, int $periodeKe = 1): Carbon
    {
        $masuk = Carbon::parse($tanggalMasuk);
        return $masuk->copy()->addMonths($periodeKe)->subDay();
    }

    /**
     * Menghitung status pembayaran penghuni saat ini.
     */
    public function hitungStatusPenghuni(Penghuni $penghuni): array
    {
        $today = Carbon::today();
        $tanggalMasuk = Carbon::parse($penghuni->tanggal_masuk);

        // Perbaikan: diffInMonths dipanggil dari instance $tanggalMasuk
        $periodeKe = max(1, (int) ceil($tanggalMasuk->diffInDays($today) / 30));

        // Cek apakah ada tambahan waktu yang aktif
        $tambahanWaktu = $penghuni->tambahanWaktu()
            ->where('is_aktif', true)
            ->latest()
            ->first();

        // Hitung jatuh tempo normal terdekat
        $jatuhTempo = $this->hitungJatuhTempo($penghuni->tanggal_masuk, $periodeKe);

        if ($tambahanWaktu && Carbon::parse($tambahanWaktu->tanggal_jatuh_tempo_baru)->gte($today)) {
            $effectiveJatuhTempo = Carbon::parse($tambahanWaktu->tanggal_jatuh_tempo_baru);
        } else {
            $effectiveJatuhTempo = $jatuhTempo;
        }

        // Cek apakah sudah ada pembayaran yang valid (tidak dibatalkan) untuk jatuh tempo ini
        $pembayaranLunas = $penghuni->pembayaran()
            ->whereNull('dibatalkan_pada')
            ->where('tanggal_jatuh_tempo', $jatuhTempo->toDateString())
            ->exists();

        if ($pembayaranLunas) {
            return [
                'kode' => 'lunas',
                'label' => 'Lunas',
                'warna' => 'hijau',
                'jatuh_tempo' => $effectiveJatuhTempo->format('d-m-Y'),
                'keterangan' => 'Pembayaran bulan ini telah lunas.'
            ];
        }

        if ($tambahanWaktu && $tambahanWaktu->is_aktif) {
            return [
                'kode' => 'tambahan_waktu',
                'label' => 'Diberi Tambahan Waktu',
                'warna' => 'kuning',
                'jatuh_tempo' => Carbon::parse($tambahanWaktu->tanggal_jatuh_tempo_baru)->format('d-m-Y'),
                'keterangan' => 'Tambahan waktu hingga ' . Carbon::parse($tambahanWaktu->tanggal_jatuh_tempo_baru)->format('d M Y') . ' (' . $tambahanWaktu->alasan . ')'
            ];
        }

        $selisihHari = $today->diffInDays($effectiveJatuhTempo, false);

        if ($selisihHari < 0) {
            return [
                'kode' => 'menunggak',
                'label' => 'Menunggak',
                'warna' => 'merah',
                'jatuh_tempo' => $effectiveJatuhTempo->format('d-m-Y'),
                'hari_terlambat' => abs((int) $selisihHari),
                'keterangan' => 'Terlambat ' . abs((int) $selisihHari) . ' hari'
            ];
        } elseif ($selisihHari <= 7) {
            return [
                'kode' => 'jatuh_tempo',
                'label' => 'Segera Jatuh Tempo',
                'warna' => 'kuning',
                'jatuh_tempo' => $effectiveJatuhTempo->format('d-m-Y'),
                'sisa_hari' => (int) $selisihHari,
                'keterangan' => 'Jatuh tempo dalam ' . (int) $selisihHari . ' hari'
            ];
        } else {
            return [
                'kode' => 'belum_bayar',
                'label' => 'Belum Dibayar',
                'warna' => 'kuning',
                'jatuh_tempo' => $effectiveJatuhTempo->format('d-m-Y'),
                'keterangan' => 'Jatuh tempo pada ' . $effectiveJatuhTempo->format('d M Y')
            ];
        }
    }
}