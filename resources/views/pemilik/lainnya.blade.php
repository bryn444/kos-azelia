@extends('layouts.pemilik.main')

@section('title', 'Lainnya - Kos Azelia')

@section('content')
<h2 class="fw-bold mb-4">Pengaturan & Fitur Tambahan Pemilik</h2>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light h-100">
            <h3 class="fw-bold mb-3">Lihat Data Khusus</h3>
            <div class="d-grid gap-3">
                <a href="{{ route('pemilik.riwayat_pembayaran') }}" class="btn btn-outline-primary btn-lg rounded-pill py-3 fw-bold text-start px-4">
                    Riwayat Seluruh Pembayaran
                </a>
                <a href="{{ route('pemilik.riwayat_tambahan_waktu') }}" class="btn btn-outline-primary btn-lg rounded-pill py-3 fw-bold text-start px-4">
                    Riwayat Tambahan Waktu Bayar
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light h-100">
            <h3 class="fw-bold mb-3">Personalisasi & Akun</h3>
            <div class="d-grid gap-3">
                <!-- Kartu Tautan Tampilan Baru -->
                <a href="{{ route('pemilik.tampilan') }}" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold text-start px-4">
                    🎨 Atur Tampilan Latar Belakang
                </a>
                <a href="{{ route('password.change') }}" class="btn btn-outline-secondary btn-lg rounded-pill py-3 fw-bold">Ganti Kata Sandi</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill py-3 fw-bold w-100">Keluar Sistem</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection