<?php
/**
 * NAMA FILE: TambahanWaktuController.php (DIPERBARUI TAHAP 9A)
 * FUNGSI: Mengelola pemberian tambahan waktu bayar dengan validasi rentang hari dan konfirmasi duplikasi.
 * HALAMAN: Panel Pengelola - Modul Tambahan Waktu.
 * CARA MENGUBAH: Sesuaikan tambahan_waktu_maks_hari pada config/kos.php bagian 'aturan'.
 */

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Penghuni;
use App\Models\TambahanWaktu;
use App\Services\JatuhTempoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TambahanWaktuController extends Controller
{
    /**
     * Menampilkan form pemberian tambahan waktu pembayaran beserta jatuh tempo saat ini.
     * Alasan: Pengelola melihat jatuh tempo semula sebelum menentukan tenggat baru.
     */
    public function create(Penghuni $penghuni, JatuhTempoService $service)
    {
        $st = $service->hitungStatusPenghuni($penghuni);
        $maksHari = (int) config('kos.aturan.tambahan_waktu_maks_hari', 30);

        return view('pengelola.tambahan_waktu.create', compact('penghuni', 'st', 'maksHari'));
    }

    /**
     * Memvalidasi tenggat baru dan menampilkan halaman konfirmasi beserta selisih hari.
     * Alasan: Memastikan tenggat baru berada setelah jatuh tempo semula dan tidak melebihi batas maksimal.
     */
    public function konfirmasi(Request $request, Penghuni $penghuni, JatuhTempoService $service)
    {
        $maksHari = (int) config('kos.aturan.tambahan_waktu_maks_hari', 30);
        $st = $service->hitungStatusPenghuni($penghuni);

        $jatuhTempoSemula = Carbon::parse($st['jatuh_tempo'] ?? Carbon::today()->toDateString())->startOfDay();
        $tanggalMinimal = $jatuhTempoSemula->copy()->addDay()->toDateString();
        $tanggalMaksimal = $jatuhTempoSemula->copy()->addDays($maksHari)->toDateString();

        $validated = $request->validate([
            'tanggal_jatuh_tempo_baru' => "required|date|after_or_equal:{$tanggalMinimal}|before_or_equal:{$tanggalMaksimal}",
            'alasan' => 'required|string|max:500',
        ], [
            'tanggal_jatuh_tempo_baru.required' => 'Tanggal tenggat baru wajib diisi.',
            'tanggal_jatuh_tempo_baru.after_or_equal' => 'Tenggat baru harus setelah jatuh tempo semula (' . $jatuhTempoSemula->format('d-m-Y') . ').',
            'tanggal_jatuh_tempo_baru.before_or_equal' => "Tenggat baru maksimal {$maksHari} hari setelah jatuh tempo semula.",
            'alasan.required' => 'Alasan pemberian tambahan waktu wajib diisi.',
        ]);

        $tenggatBaruCarbon = Carbon::parse($validated['tanggal_jatuh_tempo_baru'])->startOfDay();
        $selisihHari = $jatuhTempoSemula->diffInDays($tenggatBaruCarbon);

        // DITAMBAHKAN TAHAP 9A: Cek apakah sudah ada tambahan waktu aktif
        $tambahanAktif = TambahanWaktu::where('penghuni_id', $penghuni->id)
            ->where('is_aktif', true)
            ->orderBy('id', 'desc')
            ->first();

        return view('pengelola.tambahan_waktu.konfirmasi', [
            'penghuni' => $penghuni,
            'jatuhTempoSemula' => $jatuhTempoSemula,
            'tenggatBaru' => $validated['tanggal_jatuh_tempo_baru'],
            'selisihHari' => $selisihHari,
            'alasan' => $validated['alasan'],
            'tambahanAktif' => $tambahanAktif,
            'maksHari' => $maksHari,
        ]);
    }

    /**
     * Menyimpan tambahan waktu pembayaran setelah lolos validasi dan konfirmasi duplikasi bila ada.
     * Alasan: Mencegah pemberian tambahan waktu ganda tanpa persetujuan sadar pengelola.
     */
    public function store(Request $request, Penghuni $penghuni, JatuhTempoService $service)
    {
        $maksHari = (int) config('kos.aturan.tambahan_waktu_maks_hari', 30);
        $st = $service->hitungStatusPenghuni($penghuni);

        $jatuhTempoSemula = Carbon::parse($st['jatuh_tempo'] ?? Carbon::today()->toDateString())->startOfDay();
        $tanggalMinimal = $jatuhTempoSemula->copy()->addDay()->toDateString();
        $tanggalMaksimal = $jatuhTempoSemula->copy()->addDays($maksHari)->toDateString();

        $validated = $request->validate([
            'tanggal_jatuh_tempo_baru' => "required|date|after_or_equal:{$tanggalMinimal}|before_or_equal:{$tanggalMaksimal}",
            'alasan' => 'required|string|max:500',
            'konfirmasi_duplikat_aktif' => 'nullable|accepted',
        ], [
            'tanggal_jatuh_tempo_baru.after_or_equal' => 'Tenggat baru harus setelah jatuh tempo semula.',
            'tanggal_jatuh_tempo_baru.before_or_equal' => "Tenggat baru maksimal {$maksHari} hari setelah jatuh tempo semula.",
        ]);

        $adaAktif = TambahanWaktu::where('penghuni_id', $penghuni->id)
            ->where('is_aktif', true)
            ->exists();

        if ($adaAktif && !$request->boolean('konfirmasi_duplikat_aktif')) {
            return back()
                ->withInput()
                ->withErrors(['konfirmasi_duplikat_aktif' => 'Penghuni sudah memiliki tambahan waktu aktif. Centang konfirmasi terlebih dahulu.']);
        }

        TambahanWaktu::where('penghuni_id', $penghuni->id)->update(['is_aktif' => false]);

        TambahanWaktu::create([
            'penghuni_id' => $penghuni->id,
            'tanggal_jatuh_tempo_baru' => $validated['tanggal_jatuh_tempo_baru'],
            'alasan' => $validated['alasan'],
            'is_aktif' => true,
            'dibuat_oleh' => auth()->id(),
        ]);

        return redirect()->route('pengelola.penghuni')->with('sukses', 'Tambahan waktu bayar berhasil diberikan.');
    }

    /**
     * Menampilkan riwayat seluruh tambahan waktu yang pernah diberikan.
     * Alasan: Memudahkan pemantauan keringanan jatuh tempo penghuni.
     */
    public function index()
    {
        $riwayats = TambahanWaktu::with(['penghuni.kamar', 'pembuat'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pengelola.tambahan_waktu.index', compact('riwayats'));
    }
}