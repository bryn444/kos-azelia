<?php
/**
 * NAMA FILE: PembayaranController.php (DIPERBARUI TAHAP 9A)
 * FUNGSI: Mengelola pencatatan pembayaran tunai dengan konfirmasi detail, validasi ketat, dan anti-ganda.
 * HALAMAN: Panel Pengelola - Modul Pembayaran.
 * CARA MENGUBAH: Sesuaikan batas hari pembayaran lama pada config/kos.php bagian 'aturan'.
 */

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar penghuni aktif untuk dipilih saat mencatat pembayaran tunai.
     * Alasan: Pengelola memilih penghuni yang menyerahkan pembayaran tunai.
     */
    public function index()
    {
        $penghuniAktif = Penghuni::with('kamar')
            ->where('status', 'aktif')
            ->orderBy('kamar_id')
            ->get();

        return view('pengelola.pembayaran.index', compact('penghuniAktif'));
    }

    /**
     * Menyiapkan data ringkasan dan token idempotensi di halaman konfirmasi pembayaran.
     * Alasan: Menampilkan rincian periode, riwayat bayar terakhir, dan mencegah submit ulang ganda.
     */
    public function konfirmasi(Request $request, JatuhTempoService $service)
    {
        $request->validate([
            'penghuni_id' => 'required|exists:penghuni,id',
        ]);

        $penghuni = Penghuni::with('kamar')->findOrFail($request->penghuni_id);

        $tanggalMasuk = Carbon::parse($penghuni->tanggal_masuk)->startOfDay();
        $today = Carbon::today();
        $periodeKe = max(1, (int) ceil($tanggalMasuk->diffInDays($today) / 30));

        $jatuhTempo = Carbon::parse($service->hitungJatuhTempo($penghuni->tanggal_masuk, $periodeKe))->startOfDay();
        $awalPeriode = $jatuhTempo->copy()->subDays(30);
        $rentangPeriode = 'Bulan ke-' . $periodeKe . ': ' . $awalPeriode->format('d M') . ' sampai ' . $jatuhTempo->format('d M');

        // DITAMBAHKAN TAHAP 9A: Cek apakah pembayaran untuk periode ini sudah tercatat aktif
        $pembayaranSudahAda = Pembayaran::where('penghuni_id', $penghuni->id)
            ->whereNull('dibatalkan_pada')
            ->where('tanggal_jatuh_tempo', $jatuhTempo->toDateString())
            ->first();

        if ($pembayaranSudahAda) {
            return redirect()->route('pengelola.pembayaran.riwayat', $penghuni->id)
                ->with('error', 'Pembayaran periode ini sudah tercatat');
        }

        // DITAMBAHKAN TAHAP 9A: Ambil pembayaran terakhir (aktif) milik penghuni untuk ditampilkan di konfirmasi
        $pembayaranTerakhir = Pembayaran::where('penghuni_id', $penghuni->id)
            ->whereNull('dibatalkan_pada')
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // DITAMBAHKAN TAHAP 9A: Buat token idempotensi sekali pakai di sesi
        $tokenIdempoten = (string) Str::uuid();
        $tokens = $request->session()->get('pembayaran_idempotency_tokens', []);
        $tokens[$tokenIdempoten] = true;
        $request->session()->put('pembayaran_idempotency_tokens', $tokens);

        $tarifKamar = (int) ($penghuni->kamar->tarif ?? 550000);
        $bayarLamaHari = (int) config('kos.aturan.bayar_lama_hari', 30);

        return view('pengelola.pembayaran.konfirmasi', [
            'penghuni' => $penghuni,
            'periodeKe' => $periodeKe,
            'rentangPeriode' => $rentangPeriode,
            'jatuhTempo' => $jatuhTempo,
            'tarifKamar' => $tarifKamar,
            'jumlahDefault' => $tarifKamar,
            'tanggalBayarDefault' => $today->toDateString(),
            'pembayaranTerakhir' => $pembayaranTerakhir,
            'tokenIdempoten' => $tokenIdempoten,
            'bayarLamaHari' => $bayarLamaHari,
        ]);
    }

    /**
     * Memvalidasi dan menyimpan pembayaran tunai secara aman dengan lockForUpdate & idempotensi.
     * Alasan: Mencegah duplikasi data, salah nominal/tanggal, dan penyimpanan ulang saat refresh.
     */
    public function store(Request $request)
    {
        $bayarLamaHari = (int) config('kos.aturan.bayar_lama_hari', 30);
        $today = Carbon::today();

        $validated = $request->validate([
            'penghuni_id' => 'required|exists:penghuni,id',
            'tanggal_bayar' => 'required|date|before_or_equal:today',
            'jumlah' => 'required|integer|min:1',
            'tanggal_jatuh_tempo' => 'required|date',
            'periode_bulan' => 'required|string',
            'catatan' => 'nullable|string|max:200',
            'konfirmasi_nominal_beda' => 'nullable|accepted',
            'konfirmasi_tanggal_lama' => 'nullable|accepted',
            'idempotency_token' => 'required|string',
        ], [
            'penghuni_id.required' => 'Data penghuni wajib dipilih.',
            'tanggal_bayar.required' => 'Tanggal terima uang tunai wajib diisi.',
            'tanggal_bayar.before_or_equal' => 'Tanggal bayar tidak boleh di masa depan.',
            'jumlah.required' => 'Jumlah pembayaran wajib diisi.',
            'jumlah.integer' => 'Jumlah pembayaran harus berupa bilangan bulat.',
            'jumlah.min' => 'Jumlah pembayaran harus lebih dari 0.',
            'catatan.max' => 'Catatan maksimal 200 karakter.',
        ]);

        // DITAMBAHKAN TAHAP 9A: Validasi & konsumsi token sekali pakai (idempotensi)
        $tokens = $request->session()->get('pembayaran_idempotency_tokens', []);
        $token = $validated['idempotency_token'];
        if (!array_key_exists($token, $tokens)) {
            return redirect()->route('pengelola.pembayaran')
                ->with('error', 'Permintaan tidak valid atau sudah diproses sebelumnya. Silakan ulangi dari awal.');
        }
        unset($tokens[$token]);
        $request->session()->put('pembayaran_idempotency_tokens', $tokens);

        $penghuni = Penghuni::with('kamar')->findOrFail($validated['penghuni_id']);
        $tarifKamar = (int) ($penghuni->kamar->tarif ?? 0);
        $jumlahBayar = (int) $validated['jumlah'];

        // DITAMBAHKAN TAHAP 9A: Jika nominal berbeda dari tarif kamar, wajib centang & isi catatan
        if ($jumlahBayar !== $tarifKamar) {
            if (!$request->boolean('konfirmasi_nominal_beda')) {
                return back()
                    ->withInput()
                    ->withErrors(['konfirmasi_nominal_beda' => 'Nominal berbeda dari tarif (Rp' . number_format($tarifKamar, 0, ',', '.') . '). Centang persetujuan terlebih dahulu.']);
            }
            if (empty(trim((string) ($validated['catatan'] ?? '')))) {
                return back()
                    ->withInput()
                    ->withErrors(['catatan' => 'Catatan wajib diisi jika nominal pembayaran berbeda dari tarif kamar.']);
            }
        }

        // DITAMBAHKAN TAHAP 9A: Jika tanggal bayar lebih lama dari batas hari, wajib centang konfirmasi
        $tglBayar = Carbon::parse($validated['tanggal_bayar'])->startOfDay();
        $selisihHariLalu = $tglBayar->diffInDays($today);
        if ($selisihHariLalu > $bayarLamaHari && !$request->boolean('konfirmasi_tanggal_lama')) {
            return back()
                ->withInput()
                ->withErrors(['konfirmasi_tanggal_lama' => "Tanggal bayar lebih lama dari {$bayarLamaHari} hari. Centang konfirmasi tanggal lama terlebih dahulu."]);
        }

        $duplikat = false;
        $pembayaran = null;

        DB::transaction(function () use ($validated, $jumlahBayar, &$duplikat, &$pembayaran) {
            // DITAMBAHKAN TAHAP 9A: Kunci baris penghuni lalu periksa duplikat periode di dalam transaksi
            $penghuniTerkunci = Penghuni::where('id', $validated['penghuni_id'])->lockForUpdate()->firstOrFail();

            $sudahAda = Pembayaran::where('penghuni_id', $penghuniTerkunci->id)
                ->whereNull('dibatalkan_pada')
                ->where('tanggal_jatuh_tempo', $validated['tanggal_jatuh_tempo'])
                ->exists();

            if ($sudahAda) {
                $duplikat = true;
                return;
            }

            $pembayaran = Pembayaran::create([
                'penghuni_id' => $penghuniTerkunci->id,
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah' => $jumlahBayar,
                'periode_bulan' => $validated['periode_bulan'],
                'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                'catatan' => isset($validated['catatan']) && trim($validated['catatan']) !== '' ? trim($validated['catatan']) : null,
                'dicatat_oleh' => auth()->id(),
            ]);

            $penghuniTerkunci->tambahanWaktu()->where('is_aktif', true)->update(['is_aktif' => false]);
        });

        if ($duplikat) {
            return redirect()->route('pengelola.pembayaran.riwayat', $penghuni->id)
                ->with('error', 'Pembayaran periode ini sudah tercatat');
        }

        // DITAMBAHKAN TAHAP 9A: Redirect setelah POST agar refresh browser tidak menyimpan ulang
        return redirect()->route('pengelola.pembayaran.riwayat', $penghuni->id)
            ->with('sukses', 'Pembayaran tunai sebesar Rp' . number_format($pembayaran->jumlah, 0, ',', '.') . ' berhasil dicatat.');
    }

    /**
     * Menampilkan riwayat pembayaran seorang penghuni.
     * Alasan: Pengelola dapat memeriksa catatan pembayaran aktif maupun yang dibatalkan.
     */
    public function riwayat(Penghuni $penghuni)
    {
        $penghuni->load('kamar');
        $riwayats = Pembayaran::with('pencatat')
            ->where('penghuni_id', $penghuni->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('pengelola.pembayaran.riwayat', compact('penghuni', 'riwayats'));
    }

    /**
     * Membatalkan catatan pembayaran dengan alasan wajib.
     * Alasan: Menjaga jejak audit bila terjadi kesalahan input pembayaran tunai.
     */
    public function batalkan(Request $request, Pembayaran $pembayaran)
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:500',
        ], [
            'alasan_batal.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        $pembayaran->update([
            'dibatalkan_pada' => Carbon::now(),
            'alasan_batal' => $request->alasan_batal,
        ]);

        return back()->with('sukses', 'Pembayaran berhasil dibatalkan.');
    }
}