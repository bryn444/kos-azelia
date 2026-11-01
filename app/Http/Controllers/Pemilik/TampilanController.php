<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TampilanController extends Controller
{
    public function index()
    {
        return view('pemilik.tampilan');
    }

    public function simpan(Request $request)
    {
        $user = auth()->user();

        // 1. Jika memilih preset warna
        if ($request->has('preset')) {
            $request->validate([
                'preset' => 'required|in:biru,teal,senja,abu,jingga',
            ]);

            if ($user->latar_tampilan && str_starts_with($user->latar_tampilan, 'unggah:')) {
                $oldPath = str_replace('unggah:', '', $user->latar_tampilan);
                Storage::disk('public')->delete($oldPath);
            }

            $user->latar_tampilan = 'preset:' . $request->preset;
            $user->save();

            return back()->with('sukses', 'Tema latar belakang berhasil diperbarui.');
        }

        // 2. Jika mengunggah gambar latar (Maksimal 10 MB / 10240 KB)
        if ($request->hasFile('gambar_latar')) {
            $request->validate([
                'gambar_latar' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            ], [
                'gambar_latar.image' => 'File yang diunggah harus berupa gambar.',
                'gambar_latar.mimes' => 'Format gambar harus berjenis: JPG, JPEG, PNG, atau WEBP.',
                'gambar_latar.max' => 'Ukuran file gambar terlalu besar! Maksimal ukuran yang diperbolehkan adalah 10 MB.',
            ]);

            // Hapus file gambar latar lama jika ada
            if ($user->latar_tampilan && str_starts_with($user->latar_tampilan, 'unggah:')) {
                $oldPath = str_replace('unggah:', '', $user->latar_tampilan);
                Storage::disk('public')->delete($oldPath);
            }

            // Simpan gambar baru dengan nama unik
            $file = $request->file('gambar_latar');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('latar_pemilik', $filename, 'public');

            $user->latar_tampilan = 'unggah:' . $path;
            $user->save();

            return back()->with('sukses', 'Gambar latar belakang berhasil diunggah dan diterapkan.');
        }

        return back()->with('error', 'Tidak ada data latar belakang yang dikirimkan.');
    }

    public function reset()
    {
        $user = auth()->user();

        if ($user->latar_tampilan && str_starts_with($user->latar_tampilan, 'unggah:')) {
            $oldPath = str_replace('unggah:', '', $user->latar_tampilan);
            Storage::disk('public')->delete($oldPath);
        }

        $user->latar_tampilan = null;
        $user->save();

        return back()->with('sukses', 'Latar belakang berhasil dikembalikan ke bawaan.');
    }
}