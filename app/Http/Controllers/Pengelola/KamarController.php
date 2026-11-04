<?php
/**
 * NAMA FILE: KamarController.php (DIPERBARUI TAHAP 5A)
 * FUNGSI: Mengelola logika CRUD kamar beserta manajemen foto multiple dan batasan kamar unggulan.
 * HALAMAN: Panel Pengelola - Modul Kamar.
 * CARA MENGUBAH: Sesuaikan aturan validasi di method store/update atau logika transaksi database.
 */

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\KamarFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KamarController extends Controller
{
    /**
     * Menampilkan daftar grid 15 kamar
     */
    public function index()
    {
        // DITAMBAHKAN TAHAP 5A: Eager load relasi fotos dan sampul untuk efisiensi thumbnail
        $kamars = Kamar::with(['penghuniAktif', 'fotos', 'sampul'])->orderBy('nomor_kamar')->get();
        return view('pengelola.kamar.index', compact('kamars'));
    }

    /**
     * Menampilkan form tambah kamar baru
     */
    public function create()
    {
        return view('pengelola.kamar.create');
    }

    /**
     * Menyimpan data kamar baru beserta unggahan foto dan validasi aturan unggulan
     */
    public function store(Request $request)
    {
        // DITAMBAHKAN TAHAP 5A: Validasi input teks dan file foto multiple (maksimal 6 file)
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|unique:kamar,nomor_kamar|max:10',
            'tarif' => 'required|integer|min:0',
            'status' => 'required|in:kosong,terisi',
            'deskripsi' => 'nullable|string|max:300',
            'unggulan' => 'nullable|boolean',
            'fotos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'nomor_kamar.unique' => 'Nomor kamar sudah terdaftar.',
            'tarif.required' => 'Tarif sewa wajib diisi.',
            'deskripsi.max' => 'Deskripsi maksimal 300 karakter.',
            'fotos.*.image' => 'File yang diunggah harus berupa gambar.',
            'fotos.*.max' => 'Setiap foto maksimal berukuran 2 MB.',
        ]);

        $isUnggulan = $request->has('unggulan') && $request->input('unggulan') == '1';
        $uploadedFiles = $request->file('fotos', []);

        // DITAMBAHKAN TAHAP 5A: Cek batas maksimal 6 foto
        if (count($uploadedFiles) > 6) {
            return back()->withInput()->with('error', 'Maksimal Anda hanya dapat mengunggah 6 foto dalam satu waktu.');
        }

        // DITAMBAHKAN TAHAP 5A: Aturan bisnis - Kamar unggulan wajib punya minimal 1 foto
        if ($isUnggulan && count($uploadedFiles) === 0) {
            return back()->withInput()->with('error', 'Kamar unggulan harus memiliki minimal 1 foto.');
        }

        // DITAMBAHKAN TAHAP 5A: Aturan bisnis - Maksimal 5 kamar unggulan aktif sekaligus
        if ($isUnggulan) {
            $totalUnggulan = Kamar::where('unggulan', true)->count();
            if ($totalUnggulan >= 5) {
                return back()->withInput()->with('error', 'Batas maksimal kamar unggulan adalah 5 kamar. Nonaktifkan salah satu kamar unggulan terlebih dahulu.');
            }
        }

        DB::transaction(function () use ($validated, $isUnggulan, $uploadedFiles) {
            $kamar = Kamar::create([
                'nomor_kamar' => $validated['nomor_kamar'],
                'tarif' => $validated['tarif'],
                'status' => $validated['status'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'unggulan' => $isUnggulan,
            ]);

            // Proses penyimpanan file foto
            foreach ($uploadedFiles as $index => $file) {
                $path = $file->store('kamar', 'public');
                $kamar->fotos()->create([
                    'path' => $path,
                    'urutan' => $index,
                    'is_sampul' => ($index === 0), // Foto pertama otomatis jadi sampul
                ]);
            }
        });

        return redirect()->route('pengelola.kamar')->with('sukses', 'Data kamar baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form ubah kamar beserta galeri foto
     */
    public function edit(Kamar $kamar)
    {
        $kamar->load('fotos');
        return view('pengelola.kamar.edit', compact('kamar'));
    }

    /**
     * Memperbarui data kamar dan mengelola foto
     */
    public function update(Request $request, Kamar $kamar)
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:10|unique:kamar,nomor_kamar,' . $kamar->id,
            'tarif' => 'required|integer|min:0',
            'status' => 'required|in:kosong,terisi',
            'deskripsi' => 'nullable|string|max:300',
            'unggulan' => 'nullable|boolean',
            'fotos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'deskripsi.max' => 'Deskripsi maksimal 300 karakter.',
            'fotos.*.max' => 'Setiap foto maksimal berukuran 2 MB.',
        ]);

        $isUnggulan = $request->has('unggulan') && $request->input('unggulan') == '1';
        $newFiles = $request->file('fotos', []);

        $jumlahFotoAktif = $kamar->fotos()->count();
        $totalSetelahTambah = $jumlahFotoAktif + count($newFiles);

        if ($totalSetelahTambah > 6) {
            return back()->withInput()->with('error', 'Total foto kamar tidak boleh melebihi 6 buah.');
        }

        // Cek syarat kamar unggulan (harus punya minimal 1 foto total)
        if ($isUnggulan && $totalSetelahTambah === 0) {
            return back()->withInput()->with('error', 'Kamar unggulan harus memiliki minimal 1 foto.');
        }

        if ($isUnggulan && !$kamar->unggulan) {
            $totalUnggulan = Kamar::where('unggulan', true)->count();
            if ($totalUnggulan >= 5) {
                return back()->withInput()->with('error', 'Batas maksimal kamar unggulan adalah 5 kamar.');
            }
        }

        DB::transaction(function () use ($kamar, $validated, $isUnggulan, $newFiles) {
            $kamar->update([
                'nomor_kamar' => $validated['nomor_kamar'],
                'tarif' => $validated['tarif'],
                'status' => $validated['status'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'unggulan' => $isUnggulan,
            ]);

            // Tambahkan foto baru jika ada
            $lastUrutan = $kamar->fotos()->max('urutan') ?? -1;
            $adaSampul = $kamar->fotos()->where('is_sampul', true)->exists();

            foreach ($newFiles as $index => $file) {
                $path = $file->store('kamar', 'public');
                $isSampulBaru = (!$adaSampul && $index === 0);
                if ($isSampulBaru) {
                    $adaSampul = true;
                }

                $kamar->fotos()->create([
                    'path' => $path,
                    'urutan' => ++$lastUrutan,
                    'is_sampul' => $isSampulBaru,
                ]);
            }
        });

        return redirect()->route('pengelola.kamar')->with('sukses', 'Data kamar berhasil diperbarui.');
    }

    /**
     * Menghapus foto spesifik dari suatu kamar
     */
    public function hapusFoto(Kamar $kamar, KamarFoto $foto)
    {
        if ($foto->kamar_id !== $kamar->id) {
            abort(403, 'Aksi tidak sah.');
        }

        DB::transaction(function () use ($kamar, $foto) {
            // Hapus file fisik dari storage
            if (Storage::disk('public')->exists($foto->path)) {
                Storage::disk('public')->delete($foto->path);
            }

            $wasSampul = $foto->is_sampul;
            $foto->delete();

            // Jika yang dihapus adalah foto sampul, jadikan foto berikutnya sebagai sampul baru
            if ($wasSampul) {
                $fotoPengganti = $kamar->fotos()->first();
                if ($fotoPengganti) {
                    $fotoPengganti->update(['is_sampul' => true]);
                }
            }

            // Jika setelah dihapus kamar tidak punya foto lagi dan berstatus unggulan, matikan status unggulannya
            if ($kamar->fotos()->count() === 0 && $kamar->unggulan) {
                $kamar->update(['unggulan' => false]);
                session()->flash('sukses', 'Foto dihapus. Kamar tidak lagi unggulan karena tidak punya foto.');
            }
        });

        return back()->with('sukses', 'Foto kamar berhasil dihapus.');
    }

    /**
     * Menetapkan foto tertentu sebagai sampul utama kamar
     */
    public function jadikanSampul(Kamar $kamar, KamarFoto $foto)
    {
        if ($foto->kamar_id !== $kamar->id) {
            abort(403, 'Aksi tidak sah.');
        }

        DB::transaction(function () use ($kamar, $foto) {
            // Set semua foto kamar ini menjadi bukan sampul
            $kamar->fotos()->update(['is_sampul' => false]);
            // Set foto yang dipilih menjadi sampul
            $foto->update(['is_sampul' => true]);
        });

        return back()->with('sukses', 'Foto sampul kamar berhasil diperbarui.');
    }
}