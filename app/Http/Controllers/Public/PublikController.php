<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\InformasiKos;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    public function index()
    {
        $informasis = InformasiKos::all();
        return view('publik.index', compact('informasis'));
    }

    public function tanyaAsistenForm()
    {
        return view('publik.tanya_asisten');
    }

    public function tanyaAsistenProcess(Request $request)
    {
        $request->validate([
            'pertanyaan' => 'required|string|max:200',
        ]);

        $keyword = strtolower(trim($request->input('pertanyaan')));
        $informasis = InformasiKos::all();
        $jawaban = null;

        // Pencocokan kata kunci sederhana terhadap database informasi_kos
        foreach ($informasis as $info) {
            $judulLower = strtolower($info->judul);
            $isiLower = strtolower($info->deskripsi);
            $katLower = strtolower($info->kategori);

            if (
                str_contains($keyword, $katLower) || 
                str_contains($keyword, $judulLower) || 
                str_contains($judulLower, $keyword) ||
                (str_contains($keyword, 'harga') && str_contains($katLower, 'harga')) ||
                (str_contains($keyword, 'fasilitas') && str_contains($katLower, 'fasilitas')) ||
                (str_contains($keyword, 'aturan') && str_contains($katLower, 'aturan')) ||
                (str_contains($keyword, 'hewan') && str_contains($isiLower, 'hewan'))
            ) {
                $jawaban = "<strong>{$info->judul}</strong>: {$info->deskripsi}";
                break;
            }
        }

        if (!$jawaban) {
            $waNum = config('kos.whatsapp');
            $jawaban = "Maaf, informasi spesifik tersebut tidak ditemukan dalam sistem kami. Silakan hubungi pengelola secara langsung melalui WhatsApp di nomor <a href='https://wa.me/{$waNum}' target='_blank' class='fw-bold text-primary'>0812-3456-7890</a> untuk bantuan lebih lanjut.";
        }

        if ($request->expectsJson()) {
            return response()->json(['jawaban' => $jawaban]);
        }

        return back()->with('jawaban_hasil', $jawaban)->withInput();
    }

    public function hubungi()
    {
        return view('publik.hubungi');
    }
}