<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Penghuni;
use App\Models\TambahanWaktu;
use App\Services\JatuhTempoService;
use Illuminate\Http\Request;

class TambahanWaktuController extends Controller
{
    public function create(Penghuni $penghuni, JatuhTempoService $service)
    {
        $st = $service->hitungStatusPenghuni($penghuni);
        return view('pengelola.tambahan_waktu.create', compact('penghuni', 'st'));
    }

    public function konfirmasi(Request $request, Penghuni $penghuni)
    {
        $validated = $request->validate([
            'tanggal_jatuh_tempo_baru' => 'required|date|after:today',
            'alasan' => 'required|string|max:500',
        ], [
            'tanggal_jatuh_tempo_baru.required' => 'Tanggal tenggat baru wajib diisi.',
            'tanggal_jatuh_tempo_baru.after' => 'Tenggat baru harus tanggal setelah hari ini.',
            'alasan.required' => 'Alasan pemberian tambahan waktu wajib diisi.',
        ]);

        return view('pengelola.tambahan_waktu.konfirmasi', [
            'penghuni' => $penghuni,
            'tenggatBaru' => $validated['tanggal_jatuh_tempo_baru'],
            'alasan' => $validated['alasan'],
        ]);
    }

    public function store(Request $request, Penghuni $penghuni)
    {
        $validated = $request->validate([
            'tanggal_jatuh_tempo_baru' => 'required|date',
            'alasan' => 'required|string',
        ]);

        // Nonaktifkan tambahan waktu aktif sebelumnya jika ada
        TambahanWaktu::where('penghuni_id', $penghuni->id)->update(['is_aktif' => false]);

        TambahanWaktu::create([
            'penghuni_id' => $penghuni->id,
            'tanggal_jatuh_tempo_baru' => $validated['tanggal_jatuh_tempo_baru'],
            'alasan' => $validated['alasan'],
            'is_aktif' => true,
            'dibuat_oleh' => auth()->id(),
        ]);

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Tambahan waktu bayar berhasil diberikan.');
    }

    public function index()
    {
        $riwayats = TambahanWaktu::with(['penghuni.kamar', 'pembuat'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pengelola.tambahan_waktu.index', compact('riwayats'));
    }
}