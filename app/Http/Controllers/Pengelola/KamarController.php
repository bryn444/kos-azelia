<?php
/**
 * NAMA FILE: KamarController.php (DIPERBARUI TAHAP 5A, TAHAP 8, TAHAP 9A & TAHAP 9B)
 * FUNGSI: Mengelola CRUD kamar, foto, sinkronisasi status, validasi tarif, hapus kamar, dan log aktivitas.
 * HALAMAN: Panel Pengelola - Modul Kamar.
 * CARA MENGUBAH: Sesuaikan batas aturan pada config/kos.php bagian 'aturan'.
 */

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\KamarFoto;
use App\Services\LogAktivitasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KamarController extends Controller
{
    /**
     * Menampilkan daftar 15 kamar beserta informasi ketidaksesuaian status bila ada.
     * Alasan: Pengelola dapat melihat status kamar dan segera memperbaiki bila tidak sesuai penghuni aktif.
     */
    public function index()
    {
        $kamars = Kamar::with(['penghuniAktif', 'fotos', 'sampul'])->orderBy('nomor_kamar')->get();

        $jumlahTidakSesuai = $kamars->filter(function (Kamar $k) {
            return $k->status !== $k->statusSeharusnya();
        })->count();

        return view('pengelola.kamar.index', compact('kamars', 'jumlahTidakSesuai'));
    }

    /**
     * Menampilkan form tambah kamar baru.
     * Alasan: Menyediakan halaman tambah kamar dengan batas tarif dari konfigurasi.
     */
    public function create()
    {
        $tarifMin = (int) config('kos.aturan.tarif_min', 100000);
        $tarifMaks = (int) config('kos.aturan.tarif_maks', 5000000);

        return view('pengelola.kamar.create', compact('tarifMin', 'tarifMaks'));
    }

    /**
     * Menyimpan data kamar baru; status awal selalu 'kosong' dan tarif divalidasi sesuai rentang.
     * Alasan: Mencegah input tarif tidak wajar dan mencegah pengubahan status kamar secara manual.
     */
    public function store(Request $request)
    {
        $tarifMin = (int) config('kos.aturan.tarif_min', 100000);
        $tarifMaks = (int) config('kos.aturan.tarif_maks', 5000000);

        $validated = $request->validate([
            'nomor_kamar' => 'required|string|unique:kamar,nomor_kamar|max:10',
            'tarif' => "required|integer|min:{$tarifMin}|max:{$tarifMaks}",
            'deskripsi' => 'nullable|string|max:300',
            'unggulan' => 'nullable|boolean',
            'fotos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'nomor_kamar.unique' => 'Nomor kamar sudah terdaftar.',
            'tarif.required' => 'Tarif sewa wajib diisi.',
            'tarif.integer' => 'Tarif sewa harus berupa bilangan bulat.',
            'tarif.min' => 'Tarif sewa minimal Rp' . number_format($tarifMin, 0, ',', '.') . '.',
            'tarif.max' => 'Tarif sewa maksimal Rp' . number_format($tarifMaks, 0, ',', '.') . '.',
            'deskripsi.max' => 'Deskripsi maksimal 300 karakter.',
            'fotos.*.image' => 'File yang diunggah harus berupa gambar.',
            'fotos.*.max' => 'Setiap foto maksimal berukuran 2 MB.',
        ]);

        $isUnggulan = $request->has('unggulan') && $request->input('unggulan') == '1';
        $uploadedFiles = $request->file('fotos', []);

        if (count($uploadedFiles) > 6) {
            return back()->withInput()->with('error', 'Maksimal Anda hanya dapat mengunggah 6 foto dalam satu waktu.');
        }

        if ($isUnggulan && count($uploadedFiles) === 0) {
            return back()->withInput()->with('error', 'Kamar unggulan harus memiliki minimal 1 foto.');
        }

        if ($isUnggulan) {
            $totalUnggulan = Kamar::where('unggulan', true)->count();
            if ($totalUnggulan >= 5) {
                return back()->withInput()->with('error', 'Batas maksimal kamar unggulan adalah 5 kamar. Nonaktifkan salah satu kamar unggulan terlebih dahulu.');
            }
        }

        $kamarBaru = null;

        DB::transaction(function () use ($validated, $isUnggulan, $uploadedFiles, &$kamarBaru) {
            $kamar = Kamar::create([
                'nomor_kamar' => $validated['nomor_kamar'],
                'tarif' => (int) $validated['tarif'],
                'status' => 'kosong',
                'deskripsi' => $validated['deskripsi'] ?? null,
                'unggulan' => $isUnggulan,
            ]);

            foreach ($uploadedFiles as $index => $file) {
                $path = $file->store('kamar', 'public');
                $kamar->fotos()->create([
                    'path' => $path,
                    'urutan' => $index,
                    'is_sampul' => ($index === 0),
                ]);
            }

            $kamarBaru = $kamar;
        });

        // DITAMBAHKAN TAHAP 9B: Catat penambahan kamar baru
        if ($kamarBaru) {
            $pelaku = auth()->user()->nama_lengkap ?? 'Pengelola';
            LogAktivitasService::catat(
                'kamar_tambah',
                "{$pelaku} menambah kamar {$kamarBaru->nomor_kamar} dengan tarif Rp" . number_format($kamarBaru->tarif, 0, ',', '.'),
                $kamarBaru,
                null,
                $kamarBaru->toArray()
            );
        }

        return redirect()->route('pengelola.kamar')->with('sukses', 'Data kamar baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form ubah kamar beserta galeri foto dan batas tarif.
     * Alasan: Pengelola dapat mengubah detail kamar dan foto dengan panduan batas tarif.
     */
    public function edit(Kamar $kamar)
    {
        $kamar->load(['fotos', 'penghuniAktif']);
        $tarifMin = (int) config('kos.aturan.tarif_min', 100000);
        $tarifMaks = (int) config('kos.aturan.tarif_maks', 5000000);
        $tarifUbahPersen = (float) config('kos.aturan.tarif_ubah_persen', 30);

        return view('pengelola.kamar.edit', compact('kamar', 'tarifMin', 'tarifMaks', 'tarifUbahPersen'));
    }

    /**
     * Memperbarui data kamar (tanpa mengubah status manual) dan memvalidasi lonjakan tarif.
     * Alasan: Mencegah salah ketik tarif besar dan menjaga konsistensi status kamar.
     */
    public function update(Request $request, Kamar $kamar)
    {
        $tarifMin = (int) config('kos.aturan.tarif_min', 100000);
        $tarifMaks = (int) config('kos.aturan.tarif_maks', 5000000);
        $tarifUbahPersen = (float) config('kos.aturan.tarif_ubah_persen', 30);

        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:10|unique:kamar,nomor_kamar,' . $kamar->id,
            'tarif' => "required|integer|min:{$tarifMin}|max:{$tarifMaks}",
            'deskripsi' => 'nullable|string|max:300',
            'unggulan' => 'nullable|boolean',
            'yakin_tarif_berubah' => 'nullable|accepted',
            'fotos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'tarif.required' => 'Tarif sewa wajib diisi.',
            'tarif.integer' => 'Tarif sewa harus berupa bilangan bulat.',
            'tarif.min' => 'Tarif sewa minimal Rp' . number_format($tarifMin, 0, ',', '.') . '.',
            'tarif.max' => 'Tarif sewa maksimal Rp' . number_format($tarifMaks, 0, ',', '.') . '.',
            'deskripsi.max' => 'Deskripsi maksimal 300 karakter.',
            'fotos.*.max' => 'Setiap foto maksimal berukuran 2 MB.',
        ]);

        $tarifLama = (int) $kamar->tarif;
        $tarifBaru = (int) $validated['tarif'];
        if ($tarifLama > 0 && $tarifBaru !== $tarifLama) {
            $persenPerubahan = (abs($tarifBaru - $tarifLama) / $tarifLama) * 100;
            if ($persenPerubahan > $tarifUbahPersen && !$request->boolean('yakin_tarif_berubah')) {
                $pesanTarif = 'Tarif berubah dari Rp' . number_format($tarifLama, 0, ',', '.')
                    . ' menjadi Rp' . number_format($tarifBaru, 0, ',', '.')
                    . '. Periksa kembali.';

                return back()
                    ->withInput()
                    ->with('perlu_konfirmasi_tarif', $pesanTarif)
                    ->withErrors(['yakin_tarif_berubah' => $pesanTarif]);
            }
        }

        $isUnggulan = $request->has('unggulan') && $request->input('unggulan') == '1';
        $newFiles = $request->file('fotos', []);

        $jumlahFotoAktif = $kamar->fotos()->count();
        $totalSetelahTambah = $jumlahFotoAktif + count($newFiles);

        if ($totalSetelahTambah > 6) {
            return back()->withInput()->with('error', 'Total foto kamar tidak boleh melebihi 6 buah.');
        }

        if ($isUnggulan && $totalSetelahTambah === 0) {
            return back()->withInput()->with('error', 'Kamar unggulan harus memiliki minimal 1 foto.');
        }

        if ($isUnggulan && !$kamar->unggulan) {
            $totalUnggulan = Kamar::where('unggulan', true)->count();
            if ($totalUnggulan >= 5) {
                return back()->withInput()->with('error', 'Batas maksimal kamar unggulan adalah 5 kamar.');
            }
        }

        $dataLama = $kamar->toArray();
        $jumlahFotoDitambah = count($newFiles);

        DB::transaction(function () use ($kamar, $validated, $tarifBaru, $isUnggulan, $newFiles) {
            $kamar->update([
                'nomor_kamar' => $validated['nomor_kamar'],
                'tarif' => $tarifBaru,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'unggulan' => $isUnggulan,
            ]);

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

        $kamar->refresh();
        $pelaku = auth()->user()->nama_lengkap ?? 'Pengelola';

        // DITAMBAHKAN TAHAP 9B: Catat penambahan foto kamar jika ada foto baru diunggah
        if ($jumlahFotoDitambah > 0) {
            LogAktivitasService::catat(
                'kamar_foto_tambah',
                "{$pelaku} menambah {$jumlahFotoDitambah} foto untuk kamar {$kamar->nomor_kamar}",
                $kamar,
                ['jumlah_foto' => $jumlahFotoAktif],
                ['jumlah_foto' => $jumlahFotoAktif + $jumlahFotoDitambah]
            );
        }

        // DITAMBAHKAN TAHAP 9B: Catat perubahan data kamar
        LogAktivitasService::catat(
            'kamar_ubah',
            "{$pelaku} mengubah data kamar {$kamar->nomor_kamar}",
            $kamar,
            $dataLama,
            $kamar->toArray()
        );

        return redirect()->route('pengelola.kamar')->with('sukses', 'Data kamar berhasil diperbarui.');
    }

    /**
     * Menampilkan halaman konfirmasi sinkronisasi status kamar.
     * Alasan: Pengelola dapat memeriksa daftar kamar yang statusnya berbeda dengan penghuni aktif sebelum diperbaiki.
     */
    public function konfirmasiSinkron()
    {
        $kamarBermasalah = Kamar::with('penghuniAktif')
            ->orderBy('nomor_kamar')
            ->get()
            ->filter(function (Kamar $k) {
                return $k->status !== $k->statusSeharusnya();
            })
            ->values();

        return view('pengelola.kamar.sinkron_status', compact('kamarBermasalah'));
    }

    /**
     * Memperbaiki status kamar yang tidak sesuai dengan penghuni aktif.
     * Alasan: Menyamakan status kamar di database dengan kondisi penghuni aktif secara atomik.
     */
    public function prosesSinkron()
    {
        $diperbaiki = 0;
        $detailPerubahan = [];

        DB::transaction(function () use (&$diperbaiki, &$detailPerubahan) {
            $kamars = Kamar::with('penghuniAktif')->lockForUpdate()->get();
            foreach ($kamars as $kamar) {
                $statusLama = $kamar->status;
                if ($kamar->sinkronStatus()) {
                    $diperbaiki++;
                    $detailPerubahan[] = [
                        'kamar' => $kamar->nomor_kamar,
                        'dari' => $statusLama,
                        'menjadi' => $kamar->status,
                    ];
                }
            }
        });

        if ($diperbaiki > 0) {
            $pelaku = auth()->user()->nama_lengkap ?? 'Pengelola';
            // DITAMBAHKAN TAHAP 9B: Catat sinkronisasi status kamar
            LogAktivitasService::catat(
                'kamar_sinkron_status',
                "{$pelaku} menyinkronkan status {$diperbaiki} kamar dengan penghuni aktif",
                null,
                null,
                ['diperbaiki' => $diperbaiki, 'rincian' => $detailPerubahan]
            );
        }

        $pesan = $diperbaiki > 0
            ? "Status {$diperbaiki} kamar berhasil disinkronkan dengan penghuni aktif."
            : 'Tidak ada status kamar yang perlu diperbaiki.';

        return redirect()->route('pengelola.kamar')->with('sukses', $pesan);
    }

    /**
     * Menghapus foto spesifik dari suatu kamar.
     * Alasan: Membersihkan file fisik dan baris foto kamar yang tidak lagi diperlukan.
     */
    public function hapusFoto(Kamar $kamar, KamarFoto $foto)
    {
        if ($foto->kamar_id !== $kamar->id) {
            abort(403, 'Aksi tidak sah.');
        }

        $dataFotoLama = $foto->toArray();

        DB::transaction(function () use ($kamar, $foto) {
            if (Storage::disk('public')->exists($foto->path)) {
                Storage::disk('public')->delete($foto->path);
            }

            $wasSampul = $foto->is_sampul;
            $foto->delete();

            if ($wasSampul) {
                $fotoPengganti = $kamar->fotos()->first();
                if ($fotoPengganti) {
                    $fotoPengganti->update(['is_sampul' => true]);
                }
            }

            if ($kamar->fotos()->count() === 0 && $kamar->unggulan) {
                $kamar->update(['unggulan' => false]);
                session()->flash('sukses', 'Foto dihapus. Kamar tidak lagi unggulan karena tidak punya foto.');
            }
        });

        // DITAMBAHKAN TAHAP 9B: Catat penghapusan foto kamar
        $pelaku = auth()->user()->nama_lengkap ?? 'Pengelola';
        LogAktivitasService::catat(
            'kamar_foto_hapus',
            "{$pelaku} menghapus foto kamar {$kamar->nomor_kamar}",
            $kamar,
            $dataFotoLama,
            null
        );

        return back()->with('sukses', 'Foto kamar berhasil dihapus.');
    }

    /**
     * Menetapkan foto tertentu sebagai sampul utama kamar.
     * Alasan: Menentukan gambar utama kamar yang tampil di daftar maupun halaman publik.
     */
    public function jadikanSampul(Kamar $kamar, KamarFoto $foto)
    {
        if ($foto->kamar_id !== $kamar->id) {
            abort(403, 'Aksi tidak sah.');
        }

        DB::transaction(function () use ($kamar, $foto) {
            $kamar->fotos()->update(['is_sampul' => false]);
            $foto->update(['is_sampul' => true]);
        });

        return back()->with('sukses', 'Foto sampul kamar berhasil diperbarui.');
    }

    /**
     * Menampilkan halaman konfirmasi hapus kamar setelah memeriksa syarat kelayakan hapus.
     * Alasan: Mencegah penghapusan kamar yang sedang terisi atau pernah dihuni.
     */
    public function konfirmasiHapus(Kamar $kamar)
    {
        $alasanTolak = $this->alasanTidakBisaDihapus($kamar);
        if ($alasanTolak !== null) {
            return redirect()->route('pengelola.kamar.edit', $kamar->id)->with('error', $alasanTolak);
        }

        $kamar->loadCount('fotos');
        return view('pengelola.kamar.konfirmasi_hapus', compact('kamar'));
    }

    /**
     * Menghapus data kamar dalam transaksi DB lalu menghapus file fotonya dari disk public.
     * Alasan: Menjaga konsistensi database dan membersihkan file fisik setelah transaksi sukses.
     */
    public function destroy(Kamar $kamar)
    {
        $alasanTolak = $this->alasanTidakBisaDihapus($kamar);
        if ($alasanTolak !== null) {
            return redirect()->route('pengelola.kamar.edit', $kamar->id)->with('error', $alasanTolak);
        }

        $nomorKamar = $kamar->nomor_kamar;
        $sedangUnggulan = (bool) $kamar->unggulan;
        $dataKamarLama = $kamar->toArray();

        $pathFotoList = $kamar->fotos()->pluck('path')->filter()->values()->all();

        DB::transaction(function () use ($kamar) {
            $kamar->delete();
        });

        foreach ($pathFotoList as $pathFoto) {
            if (Storage::disk('public')->exists($pathFoto)) {
                Storage::disk('public')->delete($pathFoto);
            }
        }

        // DITAMBAHKAN TAHAP 9B: Catat penghapusan kamar
        $pelaku = auth()->user()->nama_lengkap ?? 'Pengelola';
        LogAktivitasService::catat(
            'kamar_hapus',
            "{$pelaku} menghapus kamar {$nomorKamar}",
            ['tipe' => Kamar::class, 'id' => $kamar->id],
            $dataKamarLama,
            null
        );

        $pesanSukses = "Kamar {$nomorKamar} berhasil dihapus.";
        if ($sedangUnggulan) {
            $pesanSukses .= ' (Catatan: kamar ini sebelumnya berstatus unggulan.)';
        }

        return redirect()->route('pengelola.kamar')->with('sukses', $pesanSukses);
    }

    /**
     * Memeriksa apakah kamar boleh dihapus berdasarkan status dan riwayat penghuni.
     * Alasan: Aturan bisnis di server bahwa kamar terisi atau pernah/sedang dihuni tidak boleh dihapus.
     */
    private function alasanTidakBisaDihapus(Kamar $kamar): ?string
    {
        $adaPenghuni = $kamar->penghuni()->exists();
        if ($adaPenghuni) {
            return "Kamar {$kamar->nomor_kamar} tidak bisa dihapus karena pernah atau sedang dihuni (riwayat pembayaran harus tetap tersimpan).";
        }

        if ($kamar->status === 'terisi') {
            return "Kamar {$kamar->nomor_kamar} tidak bisa dihapus karena statusnya sedang terisi.";
        }

        return null;
    }
}