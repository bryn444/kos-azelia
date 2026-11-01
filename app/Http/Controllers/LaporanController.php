<?php

namespace App\Http\Controllers;

use App\Exports\LaporanBulananExport;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(Request $request, JatuhTempoService $service)
    {
        $bulanSelected = $request->input('bulan', date('Y-m'));

        // Transaksi Lunas / Tercatat
        $pembayarans = Pembayaran::with(['penghuni.kamar', 'pencatat'])
            ->where('periode_bulan', $bulanSelected)
            ->whereNull('dibatalkan_pada')
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        $totalPemasukan = $pembayarans->sum('jumlah');

        // Penghuni Menunggak / Belum Bayar
        $penghuniAktif = Penghuni::with(['kamar', 'pembayaran', 'tambahanWaktu'])
            ->where('status', 'aktif')
            ->get();

        $daftarBelumBayar = [];
        foreach ($penghuniAktif as $p) {
            $st = $service->hitungStatusPenghuni($p);
            if (in_array($st['kode'], ['menunggak', 'jatuh_tempo', 'belum_bayar', 'tambahan_waktu'])) {
                $p->status_detail = $st;
                $daftarBelumBayar[] = $p;
            }
        }

        return view('laporan.index', compact('bulanSelected', 'pembayarans', 'totalPemasukan', 'daftarBelumBayar'));
    }

    public function downloadPdf(Request $request, JatuhTempoService $service)
    {
        $bulanSelected = $request->input('bulan', date('Y-m'));

        $pembayarans = Pembayaran::with(['penghuni.kamar', 'pencatat'])
            ->where('periode_bulan', $bulanSelected)
            ->whereNull('dibatalkan_pada')
            ->orderBy('tanggal_bayar', 'asc')
            ->get();

        $totalPemasukan = $pembayarans->sum('jumlah');

        $penghuniAktif = Penghuni::with(['kamar', 'pembayaran', 'tambahanWaktu'])
            ->where('status', 'aktif')
            ->get();

        $daftarBelumBayar = [];
        foreach ($penghuniAktif as $p) {
            $st = $service->hitungStatusPenghuni($p);
            if (in_array($st['kode'], ['menunggak', 'jatuh_tempo', 'belum_bayar', 'tambahan_waktu'])) {
                $p->status_detail = $st;
                $daftarBelumBayar[] = $p;
            }
        }

        $pdf = Pdf::loadView('laporan.pdf', compact('bulanSelected', 'pembayarans', 'totalPemasukan', 'daftarBelumBayar'));
        return $pdf->download('Laporan_Kos_Azelia_' . $bulanSelected . '.pdf');
    }

    public function downloadExcel(Request $request): BinaryFileResponse
    {
        $bulanSelected = $request->input('bulan', date('Y-m'));
        return Excel::download(new LaporanBulananExport($bulanSelected), 'Laporan_Kos_Azelia_' . $bulanSelected . '.xlsx');
    }
}