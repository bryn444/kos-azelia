<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\TambahanWaktu;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function pembayaran(Request $request)
    {
        $bulan = $request->input('bulan');
        $penghuniId = $request->input('penghuni_id');

        $query = Pembayaran::with(['penghuni.kamar', 'pencatat']);

        if ($bulan) {
            $query->where('periode_bulan', $bulan);
        }

        if ($penghuniId) {
            $query->where('penghuni_id', $penghuniId);
        }

        $pembayarans = $query->orderBy('tanggal_bayar', 'desc')->get();
        $semuaPenghuni = Penghuni::orderBy('nama_lengkap')->get();

        return view('pemilik.riwayat_pembayaran', compact('pembayarans', 'semuaPenghuni', 'bulan', 'penghuniId'));
    }

    public function tambahanWaktu()
    {
        $riwayats = TambahanWaktu::with(['penghuni.kamar', 'pembuat'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pemilik.riwayat_tambahan_waktu', compact('riwayats'));
    }
}