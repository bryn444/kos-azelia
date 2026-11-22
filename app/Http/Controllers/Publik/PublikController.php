<?php
/**
 * NAMA FILE: app/Http/Controllers/Publik/PublikController.php
 * FUNGSI: Mengelola data beranda, detail kamar, dan logika pencocokan Tanya Asisten.
 * HALAMAN: Beranda, Detail Kamar, Tanya Asisten, Kontak.
 * CARA MENGUBAH: Sesuaikan logika pencocokan kata kunci pada method tanyaAsistenProcess.
 */

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\InformasiKos;
use App\Models\Kamar;
use App\Services\LatarBerandaService;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    /**
     * Menampilkan beranda publik dengan satu kamar unggulan (beserta daftar fotonya)
     * dan informasi kos.
     * Alasan: Slider hero kini menggeser foto dari satu kamar unggulan terpilih, bukan berpindah antar kamar.
     */
    public function index(LatarBerandaService $latarBerandaService)
    {
        // DITAMBAHKAN TAHAP 7: Batas maksimal foto pada slider hero kamar unggulan
        $maksFotoHero = 6;

        // DITAMBAHKAN TAHAP 7: Mengambil kamar unggulan pertama (urut nomor_kamar naik) yang punya minimal 1 foto
        $kamarHero = Kamar::with(['fotos', 'sampul'])
            ->unggulan()
            ->has('fotos')
            ->orderBy('nomor_kamar', 'asc')
            ->first();

        // DITAMBAHKAN TAHAP 7: Menyiapkan maksimal 6 foto (url & teks alt) untuk slider hero kanan
        $fotoHero = [];
        if ($kamarHero) {
            foreach ($kamarHero->fotos->take($maksFotoHero) as $idx => $foto) {
                $keterangan = $foto->keterangan ?: ('Kamar ' . $kamarHero->nomor_kamar);
                $fotoHero[] = [
                    'url' => $foto->url,
                    'alt' => $keterangan . ' - Foto ' . ($idx + 1),
                ];
            }
        }

        // Mengambil kamar lainnya (non-unggulan, atau semua jika unggulan kosong)
        $kamarLainnyas = Kamar::with(['fotos', 'sampul'])
            ->where('unggulan', false)
            ->get();

        if ($kamarLainnyas->isEmpty()) {
            $kamarLainnyas = Kamar::with(['fotos', 'sampul'])->get();
        }

        $informasis = InformasiKos::all();
        $tarifTermurah = Kamar::min('tarif') ?? 550000;

        // DITAMBAHKAN TAHAP 6A: Mengambil susunan foto latar aktif untuk halaman calon penghuni
        $latarUtama = $latarBerandaService->untukPublik('utama');

        return view('publik.index', compact(
            'kamarHero',
            'fotoHero',
            'kamarLainnyas',
            'informasis',
            'tarifTermurah',
            'latarUtama'
        ));
    }

    /**
     * Menampilkan halaman detail spesifik kamar (Privasi terjaga tanpa data penghuni).
     * Alasan: Calon penghuni dapat melihat rincian kamar yang dipilih dari beranda.
     */
    public function showKamar(Kamar $kamar)
    {
        $kamar->load('fotos');
        return view('publik.kamar_detail', compact('kamar'));
    }

    /**
     * Menampilkan halaman Tanya Asisten.
     * Alasan: Merender form interaktif agar pengguna dapat mengajukan pertanyaan seputar kos.
     */
    public function tanyaAsistenForm()
    {
        return view('publik.tanya_asisten');
    }

    /**
     * Memproses pertanyaan pengguna dan mencocokkannya dengan tabel informasi_kos.
     * Alasan: Memberikan jawaban cepat dari data informasi kos yang tersedia di database.
     */
    public function tanyaAsistenProcess(Request $request)
    {
        $request->validate([
            'pertanyaan' => 'required|string|max:200',
        ], [
            'pertanyaan.required' => 'Pertanyaan tidak boleh kosong.',
            'pertanyaan.max' => 'Pertanyaan maksimal 200 karakter.',
        ]);

        $keyword = strtolower(trim($request->input('pertanyaan')));
        $informasis = InformasiKos::all();
        $jawabanTeks = null;

        foreach ($informasis as $info) {
            $judulLower = strtolower($info->judul);
            $deskripsiLower = strtolower($info->deskripsi);
            $kategoriLower = strtolower($info->kategori);

            if (
                str_contains($keyword, $kategoriLower) || 
                str_contains($keyword, $judulLower) || 
                str_contains($judulLower, $keyword) ||
                (str_contains($keyword, 'harga') && str_contains($kategoriLower, 'harga')) ||
                (str_contains($keyword, 'fasilitas') && str_contains($kategoriLower, 'fasilitas')) ||
                (str_contains($keyword, 'aturan') && str_contains($kategoriLower, 'aturan')) ||
                (str_contains($keyword, 'hewan') && str_contains($deskripsiLower, 'hewan'))
            ) {
                $jawabanTeks = $info->judul . ": " . $info->deskripsi;
                break;
            }
        }

        if (!$jawabanTeks) {
            $jawabanTeks = "Maaf, informasi tersebut tidak ditemukan dalam sistem. Silakan hubungi pengelola langsung untuk bantuan lebih lanjut.";
        }

        if ($request->expectsJson()) {
            return response()->json(['jawaban' => $jawabanTeks]);
        }

        return back()->with('jawaban_hasil', $jawabanTeks)->withInput();
    }

    /**
     * Menampilkan halaman Hubungi Pengelola.
     * Alasan: Menyediakan akses kontak langsung ke pengelola kos.
     */
    public function hubungi()
    {
        return view('publik.hubungi');
    }
}