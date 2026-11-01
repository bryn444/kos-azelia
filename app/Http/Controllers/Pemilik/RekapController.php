<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request, JatuhTempoService $service)
    {
        $bulanSelected = $request->input('bulan', date('Y-m'));

        // Pemasukan Bulan Dipilih (Hanya yang tidak dibatalkan)
        $totalPemasukanBulanIni = Pembayaran::where('periode_bulan', $bulanSelected)
            ->whereNull('dibatalkan_pada')
            ->sum('jumlah');

        // Jumlah Kamar Terisi
        $totalKamarTerisi = Kamar::where('status', 'terisi')->count();

        // Jumlah Penghuni Belum Bayar
        $penghuniAktif = Penghuni::with(['kamar', 'pembayaran', 'tambahanWaktu'])
            ->where('status', 'aktif')
            ->get();

        $jumlahBelumBayar = 0;
        foreach ($penghuniAktif as $p) {
            $st = $service->hitungStatusPenghuni($p);
            if ($st['kode'] !== 'lunas') {
                $jumlahBelumBayar++;
            }
        }

        // Data Grafik 6 Bulan Terakhir (Diperbaiki agar urut dan tidak duplikat)
        $labels6Bulan = [];
        $dataPemasukan6Bulan = [];

        for ($i = 5; $i >= 0; $i--) {
            // Gunakan startOfMonth agar perhitungan bulan mundur ke belakang akurat
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $keyBulan = $date->format('Y-m');
            
            // Format label nama bulan dan tahun (Contoh: Mei 2026, Juni 2026, dst)
            $labels6Bulan[] = $date->locale('id')->translatedFormat('F Y');

            $total = Pembayaran::where('periode_bulan', $keyBulan)
                ->whereNull('dibatalkan_pada')
                ->sum('jumlah');

            $dataPemasukan6Bulan[] = $total;
        }

        return view('pemilik.rekap', compact(
            'bulanSelected',
            'totalPemasukanBulanIni',
            'totalKamarTerisi',
            'jumlahBelumBayar',
            'labels6Bulan',
            'dataPemasukan6Bulan'
        ));
    }
}