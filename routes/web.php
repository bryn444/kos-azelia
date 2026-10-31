<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pengelola\BerandaController;
use App\Http\Controllers\Pengelola\KamarController;
use App\Http\Controllers\Pengelola\PenghuniController;
use App\Http\Controllers\Pengelola\PembayaranController;
use App\Http\Controllers\Pengelola\TambahanWaktuController;
use App\Http\Controllers\Pengelola\InformasiKosController;

// 1. Rute Publik (Calon Penghuni)
Route::get('/', function () {
    return view('publik.index');
})->name('publik.index');

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
    
    // Modul Kamar
    Route::get('/kamar', [KamarController::class, 'index'])->name('kamar');
    Route::get('/kamar/tambah', [KamarController::class, 'create'])->name('kamar.create');
    Route::post('/kamar/tambah', [KamarController::class, 'store'])->name('kamar.store');
    Route::get('/kamar/{kamar}/ubah', [KamarController::class, 'edit'])->name('kamar.edit');
    Route::post('/kamar/{kamar}/ubah', [KamarController::class, 'update'])->name('kamar.update');

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
});