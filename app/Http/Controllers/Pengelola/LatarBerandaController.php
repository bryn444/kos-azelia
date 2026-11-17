<?php

/**
 * NAMA FILE: app/Http/Controllers/Pengelola/LatarBerandaController.php
 * FUNGSI: Controller panel pengelola untuk mengelola foto latar belakang halaman calon penghuni
 *         (tampil editor interaktif, pratinjau halaman publik untuk iframe, unggah file asli,
 *         simpan posisi/ukuran/rotasi, hapus permanen satuan, dan reset ke tampilan tanpa latar).
 * HALAMAN: Pengelola -> Latar Beranda (`/pengelola/latar-beranda` dan `/pengelola/latar-beranda/pratinjau`).
 */

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\InformasiKos;
use App\Models\Kamar;
use App\Models\LatarFoto;
use App\Services\LatarBerandaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LatarBerandaController extends Controller
{
    private const AREA = 'utama';
    private const MAKS_FOTO = 15;
    private const MAKS_UKURAN_KB = 3072; // 3 MB

    private const MIN_X = -50;
    private const MAKS_X = 150;
    private const MIN_Y = -50;
    private const MAKS_Y = 1500;
    private const MIN_LEBAR = 3;
    private const MAKS_LEBAR = 200;
    private const MIN_ROT = -180;
    private const MAKS_ROT = 180;

    public function __construct(
        private readonly LatarBerandaService $latarService
    ) {
    }

    /**
     * Menampilkan halaman editor interaktif ala Canva untuk mengatur foto latar belakang utama.
     */
    public function index(): View
    {
        $daftarFoto = LatarFoto::query()
            ->area(self::AREA)
            ->terurut()
            ->get()
            ->map(function (LatarFoto $foto): array {
                return [
                    'id' => (int) $foto->id,
                    'url' => $foto->url,
                    'nama_asli' => $foto->nama_asli ?: ('Foto #' . $foto->id),
                    'rasio' => (float) $foto->rasio,
                    'urutan' => (int) $foto->urutan,
                    'aktif' => (bool) $foto->aktif,
                    'laptop' => $foto->nilaiPerangkat('laptop'),
                    'hp' => [
                        'jangkar' => $foto->hp_jangkar,
                        'x' => $foto->hp_x !== null ? (float) $foto->hp_x : null,
                        'y' => $foto->hp_y !== null ? (float) $foto->hp_y : null,
                        'lebar' => $foto->hp_lebar !== null ? (float) $foto->hp_lebar : null,
                        'rot' => $foto->hp_rot !== null ? (float) $foto->hp_rot : null,
                    ],
                ];
            })
            ->values()
            ->all();

        return view('pengelola.latar.index', [
            'dataAwalEditor' => $daftarFoto,
            'maksFoto' => self::MAKS_FOTO,
            'maksUkuranMb' => (int) (self::MAKS_UKURAN_KB / 1024),
        ]);
    }

    /**
     * Merender halaman beranda publik asli di dalam <iframe> editor
     * dengan penanda `$modePratinjau = true`.
     */
    public function pratinjau(): View
    {
        $kamarUnggulans = Kamar::with(['fotos', 'sampul'])
            ->unggulan()
            ->has('fotos')
            ->take(5)
            ->get();

        $kamarLainnyas = Kamar::with(['fotos', 'sampul'])
            ->where('unggulan', false)
            ->get();

        if ($kamarLainnyas->isEmpty()) {
            $kamarLainnyas = Kamar::with(['fotos', 'sampul'])->get();
        }

        $informasis = InformasiKos::all();
        $tarifTermurah = Kamar::min('tarif') ?? 550000;
        $latarUtama = $this->latarService->untukPublik(self::AREA);

        return view('publik.index', [
            'kamarUnggulans' => $kamarUnggulans,
            'kamarLainnyas' => $kamarLainnyas,
            'informasis' => $informasis,
            'tarifTermurah' => $tarifTermurah,
            'latarUtama' => $latarUtama,
            'modePratinjau' => true,
        ]);
    }

    /**
     * Mengunggah satu atau beberapa foto latar apa adanya (PNG, JPG, JPEG, WEBP).
     */
    public function unggah(Request $request): RedirectResponse|JsonResponse
    {
        $inputFile = $request->file('foto');
        if (!$inputFile) {
            $pesan = 'Pilih minimal satu file gambar terlebih dahulu.';
            return $request->expectsJson()
                ? response()->json(['message' => $pesan], 422)
                : redirect()->route('pengelola.latar')->with('error', $pesan);
        }

        $files = is_array($inputFile) ? $inputFile : [$inputFile];

        $request->validate([
            'foto' => ['required'],
            'foto.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:' . self::MAKS_UKURAN_KB,
            ],
        ], [
            'foto.required' => 'Pilih minimal satu file gambar untuk diunggah.',
            'foto.*.file' => 'Item yang diunggah harus berupa file gambar yang valid.',
            'foto.*.mimes' => 'Format gambar hanya boleh JPG, JPEG, PNG, atau WEBP (SVG dan GIF tidak diperbolehkan).',
            'foto.*.mimetypes' => 'Tipe file gambar tidak valid. Gunakan JPG, PNG, atau WEBP.',
            'foto.*.max' => 'Ukuran setiap gambar maksimal 3 MB.',
        ]);

        $jumlahSaatIni = LatarFoto::query()->area(self::AREA)->count();
        if (($jumlahSaatIni + count($files)) > self::MAKS_FOTO) {
            $sisa = max(0, self::MAKS_FOTO - $jumlahSaatIni);
            $pesan = 'Maksimal ' . self::MAKS_FOTO . " foto untuk latar utama. Saat ini sudah ada {$jumlahSaatIni} foto (sisa kuota {$sisa} foto).";
            return $request->expectsJson()
                ? response()->json(['message' => $pesan], 422)
                : redirect()->route('pengelola.latar')->with('error', $pesan);
        }

        $urutanTertinggi = (int) LatarFoto::query()->area(self::AREA)->max('urutan');
        $hasilUnggah = [];

        DB::transaction(function () use ($files, &$urutanTertinggi, &$hasilUnggah): void {
            foreach ($files as $file) {
                $urutanTertinggi++;

                $rasio = 1.00000;
                $ukuranGambar = @getimagesize($file->getRealPath());
                if (is_array($ukuranGambar) && !empty($ukuranGambar[0]) && !empty($ukuranGambar[1])) {
                    $lebarAsli = (float) $ukuranGambar[0];
                    $tinggiAsli = (float) $ukuranGambar[1];
                    if ($lebarAsli > 0) {
                        $rasio = round($tinggiAsli / $lebarAsli, 5);
                    }
                }

                $ekstensi = strtolower($file->getClientOriginalExtension() ?: 'png');
                $namaAcak = Str::random(32) . '.' . $ekstensi;
                $path = $file->storeAs('latar', $namaAcak, 'public');

                $foto = LatarFoto::create([
                    'area' => self::AREA,
                    'path' => $path,
                    'nama_asli' => $file->getClientOriginalName(),
                    'rasio' => $rasio,
                    'urutan' => $urutanTertinggi,
                    'aktif' => true,
                    'jangkar' => 'atas',
                    'lap_x' => 50,
                    'lap_y' => 40,
                    'lap_lebar' => 30,
                    'lap_rot' => 0,
                    'hp_jangkar' => null,
                    'hp_x' => null,
                    'hp_y' => null,
                    'hp_lebar' => null,
                    'hp_rot' => null,
                ]);

                $hasilUnggah[] = [
                    'id' => (int) $foto->id,
                    'url' => $foto->url,
                    'nama_asli' => $foto->nama_asli ?: ('Foto #' . $foto->id),
                    'rasio' => (float) $foto->rasio,
                    'urutan' => (int) $foto->urutan,
                    'aktif' => (bool) $foto->aktif,
                    'laptop' => $foto->nilaiPerangkat('laptop'),
                    'hp' => [
                        'jangkar' => $foto->hp_jangkar,
                        'x' => $foto->hp_x,
                        'y' => $foto->hp_y,
                        'lebar' => $foto->hp_lebar,
                        'rot' => $foto->hp_rot,
                    ],
                ];
            }
        });

        $pesanSukses = count($hasilUnggah) . ' gambar latar berhasil diunggah.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $pesanSukses,
                'data' => $hasilUnggah,
            ], 201);
        }

        return redirect()->route('pengelola.latar')->with('success', $pesanSukses);
    }

    /**
     * Menyimpan perubahan semua foto latar sekaligus.
     */
    public function simpan(Request $request): RedirectResponse|JsonResponse
    {
        $payload = $request->isJson() ? $request->json()->all() : $request->all();
        $items = $payload['foto'] ?? [];

        if (!is_array($items)) {
            $pesan = 'Format data foto tidak valid.';
            return $request->expectsJson()
                ? response()->json(['message' => $pesan], 422)
                : redirect()->route('pengelola.latar')->with('error', $pesan);
        }

        $ids = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['id']) || !is_numeric($item['id'])) {
                $pesan = 'Setiap item foto harus memiliki ID yang valid.';
                return $request->expectsJson()
                    ? response()->json(['message' => $pesan], 422)
                    : redirect()->route('pengelola.latar')->with('error', $pesan);
            }
            $ids[] = (int) $item['id'];
        }

        $models = LatarFoto::query()
            ->area(self::AREA)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        if ($models->count() !== count(array_unique($ids))) {
            $pesan = 'Terdapat ID foto yang tidak ditemukan atau bukan milik area utama.';
            return $request->expectsJson()
                ? response()->json(['message' => $pesan], 422)
                : redirect()->route('pengelola.latar')->with('error', $pesan);
        }

        $dataTervalidasi = [];
        foreach ($items as $item) {
            $id = (int) $item['id'];
            $aktif = filter_var($item['aktif'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $urutan = isset($item['urutan']) && is_numeric($item['urutan']) ? max(0, (int) $item['urutan']) : 0;

            $jangkar = (string) ($item['jangkar'] ?? 'atas');
            if (!in_array($jangkar, ['atas', 'bawah'], true)) {
                $pesan = 'Jangkar Laptop harus bernilai "atas" atau "bawah".';
                return $request->expectsJson()
                    ? response()->json(['message' => $pesan], 422)
                    : redirect()->route('pengelola.latar')->with('error', $pesan);
            }

            $lapX = $item['lap_x'] ?? null;
            $lapY = $item['lap_y'] ?? null;
            $lapLebar = $item['lap_lebar'] ?? null;
            $lapRot = $item['lap_rot'] ?? null;

            if (!$this->angkaDalamRentang($lapX, $lapY, $lapLebar, $lapRot)) {
                $pesan = 'Nilai Laptop tidak valid: X (-50 s.d. 150), Y (-50 s.d. 1500), Lebar (3 s.d. 200), Rotasi (-180 s.d. 180).';
                return $request->expectsJson()
                    ? response()->json(['message' => $pesan], 422)
                    : redirect()->route('pengelola.latar')->with('error', $pesan);
            }

            $hpJangkarRaw = $item['hp_jangkar'] ?? null;
            $hpJangkar = ($hpJangkarRaw === '' || $hpJangkarRaw === null) ? null : (string) $hpJangkarRaw;
            $hpX = ($item['hp_x'] ?? '') === '' ? null : ($item['hp_x'] ?? null);
            $hpY = ($item['hp_y'] ?? '') === '' ? null : ($item['hp_y'] ?? null);
            $hpLebar = ($item['hp_lebar'] ?? '') === '' ? null : ($item['hp_lebar'] ?? null);
            $hpRot = ($item['hp_rot'] ?? '') === '' ? null : ($item['hp_rot'] ?? null);

            $daftarHp = [$hpJangkar, $hpX, $hpY, $hpLebar, $hpRot];
            $jumlahNull = count(array_filter($daftarHp, static fn ($v): bool => $v === null));

            if ($jumlahNull !== 0 && $jumlahNull !== 5) {
                $pesan = 'Susunan HP (Jangkar, X, Y, Lebar, Rotasi) harus dikosongkan semua atau diisi semua.';
                return $request->expectsJson()
                    ? response()->json(['message' => $pesan], 422)
                    : redirect()->route('pengelola.latar')->with('error', $pesan);
            }

            if ($jumlahNull === 0) {
                if (!in_array($hpJangkar, ['atas', 'bawah'], true)) {
                    $pesan = 'Jangkar HP harus bernilai "atas" atau "bawah".';
                    return $request->expectsJson()
                        ? response()->json(['message' => $pesan], 422)
                        : redirect()->route('pengelola.latar')->with('error', $pesan);
                }

                if (!$this->angkaDalamRentang($hpX, $hpY, $hpLebar, $hpRot)) {
                    $pesan = 'Nilai HP tidak valid: X (-50 s.d. 150), Y (-50 s.d. 1500), Lebar (3 s.d. 200), Rotasi (-180 s.d. 180).';
                    return $request->expectsJson()
                        ? response()->json(['message' => $pesan], 422)
                        : redirect()->route('pengelola.latar')->with('error', $pesan);
                }
            }

            $dataTervalidasi[$id] = [
                'aktif' => $aktif,
                'urutan' => $urutan,
                'jangkar' => $jangkar,
                'lap_x' => round((float) $lapX, 2),
                'lap_y' => round((float) $lapY, 2),
                'lap_lebar' => round((float) $lapLebar, 2),
                'lap_rot' => round((float) $lapRot, 2),
                'hp_jangkar' => $hpJangkar,
                'hp_x' => $hpX !== null ? round((float) $hpX, 2) : null,
                'hp_y' => $hpY !== null ? round((float) $hpY, 2) : null,
                'hp_lebar' => $hpLebar !== null ? round((float) $hpLebar, 2) : null,
                'hp_rot' => $hpRot !== null ? round((float) $hpRot, 2) : null,
            ];
        }

        DB::transaction(function () use ($models, $dataTervalidasi): void {
            foreach ($dataTervalidasi as $id => $atribut) {
                $models[$id]->update($atribut);
            }
        });

        $pesanSukses = 'Susunan latar belakang berhasil diterapkan ke halaman calon penghuni.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $pesanSukses,
                'data' => $this->latarService->untukPublik(self::AREA),
            ]);
        }

        return redirect()->route('pengelola.latar')->with('success', $pesanSukses);
    }

    /**
     * Menghapus permanen satu foto latar dari disk public dan database.
     */
    public function hapus(Request $request, LatarFoto $foto): RedirectResponse|JsonResponse
    {
        if ($foto->area !== self::AREA) {
            $pesan = 'Foto tidak ditemukan pada area utama.';
            return $request->expectsJson()
                ? response()->json(['message' => $pesan], 404)
                : redirect()->route('pengelola.latar')->with('error', $pesan);
        }

        $path = $foto->path;

        DB::transaction(function () use ($foto, $path): void {
            $foto->delete();
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        });

        $pesanSukses = 'Foto latar belakang berhasil dihapus permanen.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $pesanSukses]);
        }

        return redirect()->route('pengelola.latar')->with('success', $pesanSukses);
    }

    /**
     * Menonaktifkan semua foto pada area 'utama' tanpa menghapus file.
     */
    public function reset(Request $request): RedirectResponse|JsonResponse
    {
        LatarFoto::query()->area(self::AREA)->update(['aktif' => false]);

        $pesanSukses = 'Semua foto latar dinonaktifkan. Halaman calon penghuni kembali ke tampilan tanpa latar.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $pesanSukses]);
        }

        return redirect()->route('pengelola.latar')->with('success', $pesanSukses);
    }

    private function angkaDalamRentang(mixed $x, mixed $y, mixed $lebar, mixed $rot): bool
    {
        if (!is_numeric($x) || !is_numeric($y) || !is_numeric($lebar) || !is_numeric($rot)) {
            return false;
        }

        $fx = (float) $x;
        $fy = (float) $y;
        $flebar = (float) $lebar;
        $frot = (float) $rot;

        return $fx >= self::MIN_X && $fx <= self::MAKS_X
            && $fy >= self::MIN_Y && $fy <= self::MAKS_Y
            && $flebar >= self::MIN_LEBAR && $flebar <= self::MAKS_LEBAR
            && $frot >= self::MIN_ROT && $frot <= self::MAKS_ROT;
    }
}