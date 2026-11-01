<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;

class BelumBayarController extends Controller
{
    public function index(JatuhTempoService $service)
    {
        $penghuniAktif = Penghuni::with(['kamar', 'pembayaran', 'tambahanWaktu'])
            ->where('status', 'aktif')
            ->get();

        $daftarBelumBayar = [];
        foreach ($penghuniAktif as $p) {
            $st = $service->hitungStatusPenghuni($p);
            if ($st['kode'] !== 'lunas') {
                $p->status_detail = $st;
                $p->tambahan_waktu_aktif = $p->tambahanWaktu->where('is_aktif', true)->last();
                $daftarBelumBayar[] = $p;
            }
        }

        return view('pemilik.belum_bayar', compact('daftarBelumBayar'));
    }
}