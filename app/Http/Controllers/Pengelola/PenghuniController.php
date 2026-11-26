<?php
/**
 * NAMA FILE: PenghuniController.php (DIPERBARUI TAHAP 9A)
 * FUNGSI: Mengelola data penghuni, normalisasi nomor HP, validasi tanggal masuk, dan proses keluar aman.
 * HALAMAN: Panel Pengelola - Modul Penghuni.
 * CARA MENGUBAH: Sesuaikan aturan format nomor HP atau rentang tanggal masuk bila diperlukan.
 */

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenghuniController extends Controller
{
    /**
     * Menampilkan daftar penghuni dengan fitur pencarian.
     * Alasan: Memudahkan pengelola mencari penghuni aktif maupun yang sudah keluar.
     */
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

    /**
     * Menampilkan form tambah penghuni baru dengan daftar kamar berstatus kosong.
     * Alasan: Penghuni baru hanya dapat menempati kamar yang berstatus kosong.
     */
    public function create()
    {
        $kamarKosong = Kamar::where('status', 'kosong')->orderBy('nomor_kamar')->get();
        return view('pengelola.penghuni.create', compact('kamarKosong'));
    }

    /**
     * Menyimpan data penghuni baru dan menandai kamar terkait sebagai terisi.
     * Alasan: Memastikan format nomor HP, keunikan nomor pada penghuni aktif, dan rentang tanggal masuk valid.
     */
    public function store(Request $request)
    {
        $tglMaks = Carbon::today()->addDays(30)->toDateString();
        $tglMin = Carbon::today()->subYears(5)->toDateString();

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_hp' => ['nullable', 'string', 'max:20', 'regex:/^(\+62|62|0)8[1-9][0-9]{7,10}$/'],
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_masuk' => "required|date|after_or_equal:{$tglMin}|before_or_equal:{$tglMaks}",
        ], [
            'nama_lengkap.required' => 'Nama penghuni wajib diisi.',
            'nomor_hp.regex' => 'Nomor HP harus berformat Indonesia (contoh: 081234567890 atau +6281234567890).',
            'kamar_id.required' => 'Pilih kamar yang ditempati.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.after_or_equal' => 'Tanggal masuk tidak boleh lebih dari 5 tahun ke belakang.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk tidak boleh lebih dari 30 hari ke depan.',
        ]);

        // DITAMBAHKAN TAHAP 9A: Normalisasi nomor HP ke bentuk 08xxxxxxxxxx
        $nomorHpNormal = $this->normalisasiNomorHp($validated['nomor_hp'] ?? null);

        if ($nomorHpNormal !== null) {
            $duplikatAktif = Penghuni::where('status', 'aktif')
                ->where('nomor_hp', $nomorHpNormal)
                ->exists();

            if ($duplikatAktif) {
                return back()
                    ->withInput()
                    ->withErrors(['nomor_hp' => 'Nomor HP ini sudah digunakan oleh penghuni aktif lain.']);
            }
        }

        DB::transaction(function () use ($validated, $nomorHpNormal) {
            $kamar = Kamar::where('id', $validated['kamar_id'])->lockForUpdate()->firstOrFail();

            Penghuni::create([
                'nama_lengkap' => $validated['nama_lengkap'],
                'nomor_hp' => $nomorHpNormal,
                'kamar_id' => $kamar->id,
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'status' => 'aktif',
            ]);

            // DITAMBAHKAN TAHAP 9A: Sinkronkan status kamar menjadi terisi secara otomatis
            $kamar->sinkronStatus();
        });

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Penghuni baru berhasil dicatat.');
    }

    /**
     * Menampilkan form ubah data penghuni.
     * Alasan: Pengelola dapat memperbarui data penghuni aktif.
     */
    public function edit(Penghuni $penghuni)
    {
        $kamarKosong = Kamar::where('status', 'kosong')
            ->orWhere('id', $penghuni->kamar_id)
            ->orderBy('nomor_kamar')
            ->get();

        return view('pengelola.penghuni.edit', compact('penghuni', 'kamarKosong'));
    }

    /**
     * Memperbarui data penghuni dan menyinkronkan status kamar lama maupun baru.
     * Alasan: Menjaga kesesuaian status kamar saat perpindahan kamar terjadi.
     */
    public function update(Request $request, Penghuni $penghuni)
    {
        $tglMaks = Carbon::today()->addDays(30)->toDateString();
        $tglMin = Carbon::today()->subYears(5)->toDateString();

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_hp' => ['nullable', 'string', 'max:20', 'regex:/^(\+62|62|0)8[1-9][0-9]{7,10}$/'],
            'kamar_id' => 'required|exists:kamar,id',
            'tanggal_masuk' => "required|date|after_or_equal:{$tglMin}|before_or_equal:{$tglMaks}",
        ], [
            'nomor_hp.regex' => 'Nomor HP harus berformat Indonesia (contoh: 081234567890).',
            'tanggal_masuk.after_or_equal' => 'Tanggal masuk tidak boleh lebih dari 5 tahun ke belakang.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk tidak boleh lebih dari 30 hari ke depan.',
        ]);

        $nomorHpNormal = $this->normalisasiNomorHp($validated['nomor_hp'] ?? null);

        if ($nomorHpNormal !== null) {
            $duplikatAktif = Penghuni::where('status', 'aktif')
                ->where('nomor_hp', $nomorHpNormal)
                ->where('id', '!=', $penghuni->id)
                ->exists();

            if ($duplikatAktif) {
                return back()
                    ->withInput()
                    ->withErrors(['nomor_hp' => 'Nomor HP ini sudah digunakan oleh penghuni aktif lain.']);
            }
        }

        $kamarLamaId = $penghuni->kamar_id;

        DB::transaction(function () use ($penghuni, $validated, $nomorHpNormal, $kamarLamaId) {
            $penghuni->update([
                'nama_lengkap' => $validated['nama_lengkap'],
                'nomor_hp' => $nomorHpNormal,
                'kamar_id' => $validated['kamar_id'],
                'tanggal_masuk' => $validated['tanggal_masuk'],
            ]);

            if ((int) $kamarLamaId !== (int) $validated['kamar_id']) {
                $kamarLama = Kamar::find($kamarLamaId);
                if ($kamarLama) {
                    $kamarLama->sinkronStatus();
                }
            }

            $kamarBaru = Kamar::find($validated['kamar_id']);
            if ($kamarBaru) {
                $kamarBaru->sinkronStatus();
            }
        });

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Data penghuni berhasil diperbarui.');
    }

    /**
     * Menampilkan halaman konfirmasi penghuni keluar beserta informasi jumlah bulan menunggak.
     * Alasan: Mengingatkan pengelola bila penghuni masih memiliki tunggakan sebelum ditandai keluar.
     */
    public function confirmKeluar(Penghuni $penghuni, JatuhTempoService $service)
    {
        $penghuni->load(['kamar', 'pembayaran', 'tambahanWaktu']);
        $jumlahBulanMenunggak = $this->hitungBulanMenunggak($penghuni, $service);

        return view('pengelola.penghuni.confirm_keluar', compact('penghuni', 'jumlahBulanMenunggak'));
    }

    /**
     * Memproses penghuni keluar, memvalidasi tunggakan & tanggal keluar, dan membebaskan kamar.
     * Alasan: Mencegah penghuni keluar tercatat tanpa konfirmasi tunggakan atau tanggal keluar di masa depan.
     */
    public function processKeluar(Request $request, Penghuni $penghuni, JatuhTempoService $service)
    {
        $penghuni->load(['kamar', 'pembayaran', 'tambahanWaktu']);
        $jumlahBulanMenunggak = $this->hitungBulanMenunggak($penghuni, $service);

        $rules = [
            'tanggal_keluar' => 'required|date|before_or_equal:today',
        ];
        $messages = [
            'tanggal_keluar.required' => 'Tanggal keluar wajib diisi.',
            'tanggal_keluar.before_or_equal' => 'Tanggal keluar tidak boleh di masa depan.',
        ];

        if ($jumlahBulanMenunggak > 0) {
            $rules['konfirmasi_tunggakan'] = 'required|accepted';
            $messages['konfirmasi_tunggakan.required'] = "Penghuni masih menunggak {$jumlahBulanMenunggak} bulan. Tetap tandai keluar.";
            $messages['konfirmasi_tunggakan.accepted'] = "Penghuni masih menunggak {$jumlahBulanMenunggak} bulan. Tetap tandai keluar.";
        }

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($penghuni, $validated) {
            $penghuni->update([
                'status' => 'keluar',
                'tanggal_keluar' => $validated['tanggal_keluar'],
            ]);

            $kamar = Kamar::find($penghuni->kamar_id);
            if ($kamar) {
                $kamar->sinkronStatus();
            }
        });

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Penghuni ' . $penghuni->nama_lengkap . ' telah ditandai keluar.');
    }

    /**
     * DITAMBAHKAN TAHAP 9A: Menormalkan nomor HP Indonesia ke format 08xxxxxxxxxx.
     * Alasan: Menyeragamkan format nomor HP di database.
     */
    private function normalisasiNomorHp(?string $nomor): ?string
    {
        if ($nomor === null) {
            return null;
        }

        $bersih = preg_replace('/[\s\-\(\)]/', '', trim($nomor));
        if ($bersih === '') {
            return null;
        }

        if (str_starts_with($bersih, '+62')) {
            return '0' . substr($bersih, 3);
        }

        if (str_starts_with($bersih, '62')) {
            return '0' . substr($bersih, 2);
        }

        return $bersih;
    }

    /**
     * DITAMBAHKAN TAHAP 9A: Menghitung perkiraan jumlah bulan yang masih menunggak.
     * Alasan: Menampilkan peringatan jumlah bulan tunggakan saat konfirmasi penghuni keluar.
     */
    private function hitungBulanMenunggak(Penghuni $penghuni, JatuhTempoService $service): int
    {
        $st = $service->hitungStatusPenghuni($penghuni);
        if (($st['kode'] ?? '') !== 'menunggak') {
            return 0;
        }

        $hariTerlambat = max(1, (int) ($st['hari_terlambat'] ?? 1));
        return max(1, (int) ceil($hariTerlambat / 30));
    }
}