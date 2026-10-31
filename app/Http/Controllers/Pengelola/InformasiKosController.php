<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\InformasiKos;
use Illuminate\Http\Request;

class InformasiKosController extends Controller
{
    public function index()
    {
        $informasis = InformasiKos::orderBy('kategori')->get();
        return view('pengelola.lainnya.index', compact('informasis'));
    }

    public function create()
    {
        return view('pengelola.lainnya.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:50',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        InformasiKos::create($validated);

        return redirect()->route('pengelola.lainnya')->with('sukses', 'Informasi kos berhasil ditambahkan.');
    }

    public function edit(InformasiKos $informasiKos)
    {
        return view('pengelola.lainnya.edit', compact('informasiKos'));
    }

    public function update(Request $request, InformasiKos $informasiKos)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:50',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        $informasiKos->update($validated);

        return redirect()->route('pengelola.lainnya')->with('sukses', 'Informasi kos berhasil diperbarui.');
    }

    public function destroy(InformasiKos $informasiKos)
    {
        $informasiKos->delete();
        return redirect()->route('pengelola.lainnya')->with('sukses', 'Informasi kos berhasil dihapus.');
    }
}