<?php
/**
 * NAMA FILE: BerandaController.php (DIPERBARUI TAHAP 9A)
 * FUNGSI: Menyiapkan ringkasan beranda pengelola, daftar jatuh tempo, dan item pada kartu "Perlu Dicek".
 * HALAMAN: Beranda Pengelola.
 * CARA MENGUBAH: Sesuaikan ambang batas pada config/kos.php bagian 'aturan'.
 */

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Services\JatuhTempoService;
use Carbon\Carbon;

class BerandaController extends Controller
{
    /**
     * Menampilkan beranda pengelola beserta peringatan ketidaksesuaian data.
     * Alasan: Membantu pengelola mendeteksi dini tunggakan lama, status kamar tidak sesuai, dan kamar unggulan terisi.
     */
    public function index(JatuhTempoService $service)
    {
        // DITAMBAHKAN TAHAP 9A: Ambil ambang hari tunggakan dari config/kos.php
        $tunggakanPeriksaHari = (int) config('kos.aturan.tunggakan_periksa_hari', 45);

        $semuaKamar = Kamar::with('penghuniAktif')->orderBy('nomor_kamar')->get();
        $totalKamarTerisi = Kamar::where('status', 'terisi')->count();

        $penghuniAktif = Penghuni::with(['kamar', 'pembayaran', 'tambahanWaktu'])
            ->where('status', 'aktif')
            ->get();

        $daftarMenunggak = [];
        $daftarJatuhTempo7Hari = [];
        $itemPerluDicek = [];

        // DITAMBAHKAN TAHAP 9A: Cek penghuni yang menunggak melebihi batas hari pemeriksaan
        foreach ($penghuniAktif as $p) {
            $st = $service->hitungStatusPenghuni($p);
            $p->status_detail = $st;

            if ($st['kode'] === 'menunggak') {
                $daftarMenunggak[] = $p;

                $hariTerlambat = (int) ($st['hari_terlambat'] ?? 0);
                if ($hariTerlambat > $tunggakanPeriksaHari) {
                    $itemPerluDicek[] = [
                        'teks' => ($p->nama_lengkap . ' (Kamar ' . ($p->kamar->nomor_kamar ?? '-') . ') - Menunggak ' . $hariTerlambat . ' hari. Sudah pindah?'),
                        'aksi_label' => 'Tandai Keluar',
                        'aksi_url' => route('pengelola.penghuni.keluar', $p->id),
                        'aksi_style' => 'danger',
                    ];
                }
            } elseif ($st['kode'] === 'jatuh_tempo' || $st['kode'] === 'tambahan_waktu') {
                $daftarJatuhTempo7Hari[] = $p;
            }
        }

        usort($daftarMenunggak, function ($a, $b) {
            return ($b->status_detail['hari_terlambat'] ?? 0) <=> ($a->status_detail['hari_terlambat'] ?? 0);
        });

        // DITAMBAHKAN TAHAP 9A: Cek kamar unggulan yang berstatus terisi
        foreach ($semuaKamar as $kamar) {
            if ($kamar->unggulan && $kamar->status === 'terisi') {
                $itemPerluDicek[] = [
                    'teks' => "Kamar {$kamar->nomor_kamar} ditandai unggulan tetapi sedang terisi",
                    'aksi_label' => 'Ubah',
                    'aksi_url' => route('pengelola.kamar.edit', $kamar->id),
                    'aksi_style' => 'primary',
                ];
            }
        }

        // DITAMBAHKAN TAHAP 9A: Hitung kamar dengan status tidak sesuai penghuni aktif
        $kamarStatusTidakSesuai = $semuaKamar->filter(function (Kamar $k) {
            return $k->status !== $k->statusSeharusnya();
        })->values();
        $jumlahStatusTidakSesuai = $kamarStatusTidakSesuai->count();

        if ($jumlahStatusTidakSesuai > 0) {
            $itemPerluDicek[] = [
                'teks' => "Ada {$jumlahStatusTidakSesuai} kamar yang statusnya tidak sesuai dengan penghuni",
                'aksi_label' => 'Periksa Status',
                'aksi_url' => route('pengelola.kamar.sinkron'),
                'aksi_style' => 'warning',
            ];
        }

        $totalMenunggak = count($daftarMenunggak);
        $hariIni = Carbon::today()->format('d-m-Y');

        return view('pengelola.beranda', compact(
            'totalKamarTerisi',
            'totalMenunggak',
            'daftarMenunggak',
            'daftarJatuhTempo7Hari',
            'itemPerluDicek',
            'jumlahStatusTidakSesuai',
            'hariIni'
        ));
    }
}