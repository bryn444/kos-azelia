@extends('layouts.panel.main')

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
                <a href="{{ route('password.change') }}" class="btn btn-outline-secondary btn-lg rounded-pill py-3 fw-bold">Ganti Kata Sandi</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill py-3 fw-bold w-100">Keluar Sistem</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===== BAGIAN: KARTU LOG AKTIVITAS (TAHAP 9B) ===== --}}
<div class="col-md-6">
    <a href="{{ route('pemilik.log') }}" class="card border-0 shadow-sm rounded-4 p-4 text-decoration-none h-100 bg-white">
        <h3 class="fw-bold text-primary mb-2">Log Aktivitas</h3>
        <p class="text-muted fs-5 mb-0">Lihat catatan aktivitas masuk, pembayaran, penghuni, kamar, dan pengaturan.</p>
    </a>
</div>
@endsection