<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Penghuni;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PenghuniController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = Penghuni::with(['kamar', 'pembayaran', 'tambahanWaktu']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhereHas('kamar', function ($k) use ($search) {
                      $k->where('nomor_kamar', 'like', "%{$search}%");
                  });
            });
        }

        $penghunis = $query->orderBy('status', 'asc')
            ->orderBy('kamar_id', 'asc')
            ->get();

        return view('pengelola.penghuni.index', compact('penghunis', 'search'));
    }

    public function create()
    {
        $kamarKosong = Kamar::where('status', 'kosong')->orderBy('nomor_kamar')->get();
        return view('pengelola.penghuni.create', compact('kamarKosong'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_hp' => 'nullable|string|max:20',
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_masuk' => 'required|date',
        ], [
            'nama_lengkap.required' => 'Nama penghuni wajib diisi.',
            'kamar_id.required' => 'Pilih kamar yang ditempati.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
        ]);

        $validated['status'] = 'aktif';

        $penghuni = Penghuni::create($validated);

        // Update status kamar menjadi terisi
        Kamar::where('id', $request->kamar_id)->update(['status' => 'terisi']);

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Penghuni baru berhasil dicatat.');
    }

    public function edit(Penghuni $penghuni)
    {
        $kamarKosong = Kamar::where('status', 'kosong')
            ->orWhere('id', $penghuni->kamar_id)
            ->orderBy('nomor_kamar')
            ->get();

        return view('pengelola.penghuni.edit', compact('penghuni', 'kamarKosong'));
    }

    public function update(Request $request, Penghuni $penghuni)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_hp' => 'nullable|string|max:20',
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_masuk' => 'required|date',
        ]);

        $kamarLamaId = $penghuni->kamar_id;

        $penghuni->update($validated);

        if ($kamarLamaId != $request->kamar_id) {
            Kamar::where('id', $kamarLamaId)->update(['status' => 'kosong']);
            Kamar::where('id', $request->kamar_id)->update(['status' => 'terisi']);
        }

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Data penghuni berhasil diperbarui.');
    }

    public function confirmKeluar(Penghuni $penghuni)
    {
        return view('pengelola.penghuni.confirm_keluar', compact('penghuni'));
    }

    public function processKeluar(Request $request, Penghuni $penghuni)
    {
        $penghuni->update([
            'status' => 'keluar',
            'tanggal_keluar' => Carbon::now()->toDateString(),
        ]);

        // Bebaskan kamar
        Kamar::where('id', $penghuni->kamar_id)->update(['status' => 'kosong']);

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Penghuni ' . $penghuni->nama_lengkap . ' telah ditandai keluar.');
    }
}