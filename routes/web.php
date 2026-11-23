<?php
/**
 * NAMA FILE: routes/web.php
 * FUNGSI: Mendefinisikan seluruh rute aplikasi (Publik, Auth, Pengelola, Pemilik).
 * CARA MENGUBAH: Tambahkan rute baru di dalam grup middleware yang sesuai.
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Pengelola\BerandaController;
use App\Http\Controllers\Pengelola\KamarController;
use App\Http\Controllers\Pengelola\LatarBerandaController;
use App\Http\Controllers\Pengelola\PenghuniController;
use App\Http\Controllers\Pengelola\PembayaranController;
use App\Http\Controllers\Pengelola\TambahanWaktuController;
use App\Http\Controllers\Pengelola\InformasiKosController;
use App\Http\Controllers\Pemilik\RekapController;
use App\Http\Controllers\Pemilik\BelumBayarController;
use App\Http\Controllers\Pemilik\RiwayatController;
use App\Http\Controllers\Pemilik\LainnyaController;
use App\Http\Controllers\Pemilik\TampilanController;
use App\Http\Controllers\Publik\PublikController;

// 1. Rute Publik (Calon Penghuni)
Route::get('/', [PublikController::class, 'index'])->name('publik.index');
Route::get('/kamar/{kamar}', [PublikController::class, 'showKamar'])->name('publik.kamar.show');
Route::get('/tanya-asisten', [PublikController::class, 'tanyaAsistenForm'])->name('publik.tanya');
Route::post('/tanya-asisten', [PublikController::class, 'tanyaAsistenProcess'])->name('publik.tanya.process')->middleware('throttle:20,1');
Route::get('/hubungi-pengelola', [PublikController::class, 'hubungi'])->name('publik.hubungi');

// 2. Rute Auth (Login & Logout)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Ganti Password (untuk yang sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/ganti-password', [AuthController::class, 'showGantiPassword'])->name('password.change');
    Route::post('/ganti-password', [AuthController::class, 'updatePassword']);
});

// 3. Rute Panel Pengelola (Kakek)
Route::middleware(['auth', 'isPengelola'])->prefix('pengelola')->name('pengelola.')->group(function () {
    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');

    // Route pengelolaan latar belakang halaman calon penghuni
    Route::get('/latar-beranda', [LatarBerandaController::class, 'index'])->name('latar');
    Route::post('/latar-beranda/unggah', [LatarBerandaController::class, 'unggah'])->name('latar.unggah');
    Route::post('/latar-beranda/simpan', [LatarBerandaController::class, 'simpan'])->name('latar.simpan');
    Route::delete('/latar-beranda/{foto}/hapus', [LatarBerandaController::class, 'hapus'])->name('latar.hapus');
    Route::post('/latar-beranda/reset', [LatarBerandaController::class, 'reset'])->name('latar.reset');

    // DITAMBAHKAN TAHAP 8: Rute konfirmasi dan eksekusi hapus kamar
    Route::get('/kamar/{kamar}/hapus', [KamarController::class, 'konfirmasiHapus'])->name('kamar.hapus');
    Route::post('/kamar/{kamar}/hapus', [KamarController::class, 'destroy'])->name('kamar.destroy');
    
    // Halaman pratinjau publik khusus di dalam iframe editor pengelola
    Route::get('/latar-beranda/pratinjau', [LatarBerandaController::class, 'pratinjau'])->name('latar.pratinjau');

    // Modul Kamar
    Route::get('/kamar', [KamarController::class, 'index'])->name('kamar');
    Route::get('/kamar/tambah', [KamarController::class, 'create'])->name('kamar.create');
    Route::post('/kamar/tambah', [KamarController::class, 'store'])->name('kamar.store');
    Route::get('/kamar/{kamar}/ubah', [KamarController::class, 'edit'])->name('kamar.edit');
    Route::post('/kamar/{kamar}/ubah', [KamarController::class, 'update'])->name('kamar.update');

    // Rute manajemen foto kamar
    Route::post('/kamar/{kamar}/foto/{foto}/hapus', [KamarController::class, 'hapusFoto'])->name('kamar.foto.hapus');
    Route::post('/kamar/{kamar}/foto/{foto}/sampul', [KamarController::class, 'jadikanSampul'])->name('kamar.foto.sampul');

    // Modul Penghuni
    Route::get('/penghuni', [PenghuniController::class, 'index'])->name('penghuni');
    Route::get('/penghuni/tambah', [PenghuniController::class, 'create'])->name('penghuni.create');
    Route::post('/penghuni/tambah', [PenghuniController::class, 'store'])->name('penghuni.store');
    Route::get('/penghuni/{penghuni}/ubah', [PenghuniController::class, 'edit'])->name('penghuni.edit');
    Route::post('/penghuni/{penghuni}/ubah', [PenghuniController::class, 'update'])->name('penghuni.update');
    Route::get('/penghuni/{penghuni}/keluar', [PenghuniController::class, 'confirmKeluar'])->name('penghuni.keluar');
    Route::post('/penghuni/{penghuni}/keluar', [PenghuniController::class, 'processKeluar'])->name('penghuni.keluar.process');

    // Modul Pembayaran
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran');
    Route::post('/pembayaran/konfirmasi', [PembayaranController::class, 'konfirmasi'])->name('pembayaran.konfirmasi');
    Route::post('/pembayaran/simpan', [PembayaranController::class, 'store'])->name('pembayaran.store');
    Route::get('/pembayaran/riwayat/{penghuni}', [PembayaranController::class, 'riwayat'])->name('pembayaran.riwayat');
    Route::post('/pembayaran/{pembayaran}/batalkan', [PembayaranController::class, 'batalkan'])->name('pembayaran.batalkan');

    // Modul Tambahan Waktu
    Route::get('/tambahan-waktu', [TambahanWaktuController::class, 'index'])->name('tambahan_waktu.index');
    Route::get('/tambahan-waktu/tambah/{penghuni}', [TambahanWaktuController::class, 'create'])->name('tambahan_waktu.create');
    Route::post('/tambahan-waktu/konfirmasi/{penghuni}', [TambahanWaktuController::class, 'konfirmasi'])->name('tambahan_waktu.konfirmasi');
    Route::post('/tambahan-waktu/simpan/{penghuni}', [TambahanWaktuController::class, 'store'])->name('tambahan_waktu.store');

    // Modul Lainnya (Kelola Informasi Kos)
    Route::get('/lainnya', [InformasiKosController::class, 'index'])->name('lainnya');
    Route::get('/lainnya/tambah', [InformasiKosController::class, 'create'])->name('lainnya.create');
    Route::post('/lainnya/tambah', [InformasiKosController::class, 'store'])->name('lainnya.store');
    Route::get('/lainnya/{informasiKos}/ubah', [InformasiKosController::class, 'edit'])->name('lainnya.edit');
    Route::post('/lainnya/{informasiKos}/ubah', [InformasiKosController::class, 'update'])->name('lainnya.update');
    Route::post('/lainnya/{informasiKos}/hapus', [InformasiKosController::class, 'destroy'])->name('lainnya.destroy');

    // Modul Laporan Pengelola
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/pdf', [LaporanController::class, 'downloadPdf'])->name('laporan.pdf');
    Route::get('/laporan/excel', [LaporanController::class, 'downloadExcel'])->name('laporan.excel');
});

// 4. Rute Panel Pemilik (Ibu Endang Supriyatin)
Route::middleware(['auth', 'isPemilik'])->prefix('pemilik')->name('pemilik.')->group(function () {
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap');
    Route::get('/belum-bayar', [BelumBayarController::class, 'index'])->name('belum_bayar');
    Route::get('/riwayat-pembayaran', [RiwayatController::class, 'pembayaran'])->name('riwayat_pembayaran');
    Route::get('/riwayat-tambahan-waktu', [RiwayatController::class, 'riwayatTambahanWaktu'])->name('riwayat_tambahan_waktu');
    Route::get('/lainnya', [LainnyaController::class, 'index'])->name('lainnya');

    // Modul Tampilan Latar Pemilik
    Route::get('/tampilan', [TampilanController::class, 'index'])->name('tampilan');
    Route::post('/tampilan/simpan', [TampilanController::class, 'simpan'])->name('tampilan.simpan');
    Route::post('/tampilan/reset', [TampilanController::class, 'reset'])->name('tampilan.reset');

    // Modul Laporan Pemilik
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/pdf', [LaporanController::class, 'downloadPdf'])->name('laporan.pdf');
    Route::get('/laporan/excel', [LaporanController::class, 'downloadExcel'])->name('laporan.excel');
});