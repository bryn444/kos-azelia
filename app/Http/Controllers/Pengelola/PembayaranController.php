<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        $penghuniAktif = Penghuni::with('kamar')
            ->where('status', 'aktif')
            ->orderBy('kamar_id')
            ->get();

        return view('pengelola.pembayaran.index', compact('penghuniAktif'));
    }

    public function konfirmasi(Request $request, JatuhTempoService $service)
    {
        $request->validate([
            'penghuni_id' => 'required|exists:penghuni,id',
        ]);

        $penghuni = Penghuni::with('kamar')->findOrFail($request->penghuni_id);
        $status = $service->hitungStatusPenghuni($penghuni);

        $tanggalMasuk = \Carbon\Carbon::parse($penghuni->tanggal_masuk);
        $today = \Carbon\Carbon::today();
        $periodeKe = max(1, (int) ceil($tanggalMasuk->diffInDays($today) / 30));

        $jatuhTempo = $service->hitungJatuhTempo($penghuni->tanggal_masuk, $periodeKe);

        // Cegah pembayaran ganda aktif untuk jatuh tempo yang sama
        $sudahLunas = Pembayaran::where('penghuni_id', $penghuni->id)
            ->whereNull('dibatalkan_pada')
            ->where('tanggal_jatuh_tempo', $jatuhTempo->toDateString())
            ->exists();

        if ($sudahLunas) {
            return redirect()->route('pengelola.pembayaran')
                ->with('error', 'Pembayaran untuk periode jatuh tempo ' . $jatuhTempo->format('d-m-Y') . ' sudah pernah dicatat dan masih aktif.');
        }

        return view('pengelola.pembayaran.konfirmasi', [
            'penghuni' => $penghuni,
            'periodeKe' => $periodeKe,
            'jatuhTempo' => $jatuhTempo,
            'jumlahDefault' => $penghuni->kamar->tarif ?? 550000,
            'tanggalBayarDefault' => date('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'penghuni_id' => 'required|exists:penghuni,id',
            'tanggal_bayar' => 'required|date',
            'jumlah' => 'required|integer|min:0',
            'tanggal_jatuh_tempo' => 'required|date',
            'periode_bulan' => 'required|string',
        ]);

        // Cek kembali untuk mencegah submit ganda
        $exists = Pembayaran::where('penghuni_id', $validated['penghuni_id'])
            ->whereNull('dibatalkan_pada')
            ->where('tanggal_jatuh_tempo', $validated['tanggal_jatuh_tempo'])
            ->exists();

        if ($exists) {
            return redirect()->route('pengelola.pembayaran')
                ->with('error', 'Pembayaran untuk periode ini sudah dicatat sebelumnya.');
        }

        $validated['dicatat_oleh'] = auth()->id();

        $pembayaran = Pembayaran::create($validated);
        $penghuni = Penghuni::with('kamar')->find($validated['penghuni_id']);

        // Matikan status tambahan waktu jika ada yang aktif
        $penghuni->tambahanWaktu()->where('is_aktif', true)->update(['is_aktif' => false]);

        return view('pengelola.pembayaran.sukses', compact('pembayaran', 'penghuni'));
    }

    public function riwayat(Penghuni $penghuni)
    {
        $riwayats = Pembayaran::with('pencatat')
            ->where('penghuni_id', $penghuni->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('pengelola.pembayaran.riwayat', compact('penghuni', 'riwayats'));
    }

    public function batalkan(Request $request, Pembayaran $pembayaran)
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:500',
        ], [
            'alasan_batal.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $pembayaran->update([
            'dibatalkan_pada' => \Carbon\Carbon::now(),
            'alasan_batal' => $request->alasan_batal,
        ]);

        return back()->with('sukses', 'Pembayaran berhasil dibatalkan.');
    }
}