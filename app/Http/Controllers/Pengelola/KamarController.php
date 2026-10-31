<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    public function index()
    {
        $kamars = Kamar::with('penghuniAktif')->orderBy('nomor_kamar')->get();
        return view('pengelola.kamar.index', compact('kamars'));
    }

    public function create()
    {
        return view('pengelola.kamar.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|unique:kamar,nomor_kamar|max:10',
            'tarif' => 'required|integer|min:0',
            'status' => 'required|in:kosong,terisi',
        ], [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'nomor_kamar.unique' => 'Nomor kamar sudah ada.',
            'tarif.required' => 'Tarif sewa wajib diisi.',
            'status.required' => 'Status kamar wajib dipilih.',
        ]);

        Kamar::create($validated);

        return redirect()->route('pengelola.kamar')->with('sukses', 'Data kamar berhasil ditambahkan.');
    }

    public function edit(Kamar $kamar)
    {
        return view('pengelola.kamar.edit', compact('kamar'));
    }

    public function update(Request $request, Kamar $kamar)
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:10|unique:kamar,nomor_kamar,' . $kamar->id,
            'tarif' => 'required|integer|min:0',
            'status' => 'required|in:kosong,terisi',
        ]);

        $kamar->update($validated);

        return redirect()->route('pengelola.kamar')->with('sukses', 'Data kamar berhasil diperbarui.');
    }
}