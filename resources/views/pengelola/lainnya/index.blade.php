{{--
  NAMA FILE: resources/views/pengelola/lainnya/index.blade.php
  FUNGSI: Halaman menu Lainnya pengelola berisi pengaturan akun, navigasi laporan/riwayat,
          pengaturan tampilan tambahan (latar belakang), dan pengelolaan informasi kos publik.
  HALAMAN: Panel Pengelola -> Lainnya (`/pengelola/lainnya`).
  CARA MENGUBAH:
  - Ubah label tombol "Atur latar belakang" pada bagian `DITAMBAHKAN TAHAP 6A` di kolom kanan.
--}}
@extends('layouts.panel.main')

@section('title', 'Lainnya - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: PENGATURAN AKUN & TAMPILAN TAMBAHAN ===== --}}
<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
            <h3 class="fw-bold mb-3">Pengaturan Akun & Menu Pengelola</h3>
            <a href="{{ route('password.change') }}" class="btn btn-outline-primary btn-lg rounded-pill py-3 fw-bold mb-3 w-100">Ganti Kata Sandi</a>
            
            <!-- Tautan tambahan untuk pengelola -->
            <a href="{{ route('pengelola.laporan') }}" class="btn btn-outline-primary btn-lg rounded-pill py-3 fw-bold mb-3 w-100">Laporan Bulanan</a>
            <a href="{{ route('pengelola.tambahan_waktu.index') }}" class="btn btn-outline-primary btn-lg rounded-pill py-3 fw-bold mb-3 w-100">Riwayat Tambahan Waktu</a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-lg rounded-pill py-3 fw-bold w-100">Keluar dari Sistem</button>
            </form>
        </div>
    </div>

    {{-- DITAMBAHKAN TAHAP 6A: Kartu kanan "Pengaturan tampilan tambahan" dengan tombol bergaya sama seperti kartu pertama --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
            <h3 class="fw-bold mb-3">Pengaturan tampilan tambahan</h3>
            <a href="{{ route('pengelola.latar') }}" class="btn btn-outline-primary btn-lg rounded-pill py-3 fw-bold mb-3 w-100">Atur latar belakang</a>
        </div>
    </div>
</div>

{{-- ===== BAGIAN: KELOLA INFORMASI KOS PUBLIK ===== --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Kelola Informasi Kos Publik</h2>
    <a href="{{ route('pengelola.lainnya.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fs-5">Tambah Informasi</a>
</div>

<div class="row g-4">
    @foreach($informasis as $info)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <span class="badge bg-info text-dark fs-6 mb-2 align-self-start">{{ strtoupper($info->kategori) }}</span>
                <h4 class="fw-bold text-dark">{{ $info->judul }}</h4>
                <p class="text-muted fs-5 mb-4">{{ $info->deskripsi }}</p>
                <div class="d-flex gap-2 mt-auto">
                    <a href="{{ route('pengelola.lainnya.edit', $info->id) }}" class="btn btn-outline-secondary rounded-pill px-4">Ubah</a>
                    <form action="{{ route('pengelola.lainnya.destroy', $info->id) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Hapus informasi ini?')" class="btn btn-outline-danger rounded-pill px-4">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection