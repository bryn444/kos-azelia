<?php
/**
 * NAMA FILE: LogAktivitasController.php (DITAMBAHKAN TAHAP 9B)
 * FUNGSI: Menampilkan halaman Log Aktivitas hanya baca dengan filter tanggal dan jenis aksi.
 * HALAMAN: Log Aktivitas Pengelola (`pengelola.log`) & Pemilik (`pemilik.log`).
 * CARA MENGUBAH: Ubah jumlah baris per halaman pada config/kos.php bagian 'log_aktivitas'.
 */

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas terbaru dengan filter tanggal dan jenis aksi.
     * Alasan: Memberikan visibilitas aktivitas sistem kepada pengelola dan pemilik secara hanya baca.
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $aksi = $request->input('aksi');
        $perHalaman = (int) config('kos.log_aktivitas.per_halaman', 25);

        $query = LogAktivitas::with('pengguna')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($tanggal)) {
            $query->whereDate('created_at', $tanggal);
        }

        if (!empty($aksi)) {
            $query->where('aksi', $aksi);
        }

        $logs = $query->paginate(max(1, $perHalaman))->withQueryString();

        $daftarAksi = LogAktivitas::query()
            ->select('aksi')
            ->distinct()
            ->orderBy('aksi')
            ->pluck('aksi');

        $ruteLog = $request->routeIs('pemilik.*') ? 'pemilik.log' : 'pengelola.log';

        return view('panel.log_aktivitas.index', compact('logs', 'tanggal', 'aksi', 'daftarAksi', 'ruteLog'));
    }
}