<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;

class BerandaController extends Controller
{
    public function index(JatuhTempoService $service)
    {
        $totalKamarTerisi = Kamar::where('status', 'terisi')->count();
        $penghuniAktif = Penghuni::with(['kamar', 'pembayaran', 'tambahanWaktu'])
            ->where('status', 'aktif')
            ->get();

        $daftarMenunggak = [];
        $daftarJatuhTempo7Hari = [];

        foreach ($penghuniAktif as $p) {
            $st = $service->hitungStatusPenghuni($p);
            $p->status_detail = $st;

            if ($st['kode'] === 'menunggak') {
                $daftarMenunggak[] = $p;
            } elseif ($st['kode'] === 'jatuh_tempo' || $st['kode'] === 'tambahan_waktu') {
                $daftarJatuhTempo7Hari[] = $p;
            }
        }

        // Urutkan menunggak dari yang hari keterlambatannya paling lama
        usort($daftarMenunggak, function ($a, $b) {
            return ($b->status_detail['hari_terlambat'] ?? 0) <=> ($a->status_detail['hari_terlambat'] ?? 0);
        });

        $totalMenunggak = count($daftarMenunggak);

        return view('pengelola.beranda', compact(
            'totalKamarTerisi',
            'totalMenunggak',
            'daftarMenunggak',
            'daftarJatuhTempo7Hari'
        ));
    }
}