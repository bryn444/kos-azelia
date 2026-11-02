@extends('layouts.publik')

@section('title', 'Kos Azelia - Beranda Informasi Kos Putri')

@section('content')
<!-- 3. Hero Dua Kolom -->
<header class="container my-5 py-5">
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <span class="badge bg-light text-primary fs-6 px-3 py-2 rounded-pill mb-3 fw-bold border">KOS PUTRI EKSKLUSIF</span>
            <h1 class="fs-judul fw-extrabold mb-4 text-dark">Hunian Aman & Nyaman Khusus Mahasiswi</h1>
            <p class="text-muted fs-5 mb-4">Nikmati fasilitas lengkap dengan lingkungan yang tenang, strategis, dan ramah studi di Kos Azelia.</p>
            <div class="p-4 bg-light rounded-4 mb-4 d-inline-block border">
                <span class="text-muted d-block fs-6">Tarif Sewa Kamar</span>
                <span class="display-6 fw-bold text-primary">Rp550.000 <span class="fs-5 text-muted fw-normal">/ bulan</span></span>
            </div>
            <div>
                <a href="{{ route('publik.tanya') }}" class="btn btn-pil btn-biru px-5 py-3 shadow-sm">Tanya Ketersediaan Kamar</a>
            </div>
        </div>
        <div class="col-lg-6 text-center">
            <div class="blob-bg p-3">
                <img src="{{ asset('img/kamar-placeholder.svg') }}" alt="Foto Kamar Kos Azelia" class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</header>

<!-- 4. Baris Dua Kartu Fitur -->
<section class="container my-5 py-4">
    <div class="row g-4 justify-content-center">
        <div class="col-md-6">
            <div class="card-kustom d-flex align-items-center gap-4 h-100">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-2 fw-bold" style="min-width: 70px; height: 70px; background-color: var(--biru-baja) !important;">✓</div>
                <div>
                    <h3 class="fs-4 fw-bold mb-1">Sudah Termasuk Fasilitas</h3>
                    <p class="text-muted mb-0">Bebas biaya listrik, air, dan koneksi Wi-Fi cepat 24 jam tanpa tambahan biaya.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-kustom d-flex align-items-center gap-4 h-100">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-2 fw-bold" style="min-width: 70px; height: 70px; background-color: var(--biru-baja) !important;">♀</div>
                <div>
                    <h3 class="fs-4 fw-bold mb-1">Khusus Putri & Tenang</h3>
                    <p class="text-muted mb-0">Lingkungan eksklusif khusus mahasiswi dan tidak memperbolehkan membawa hewan peliharaan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. Bagian Informasi Kos dari Database -->
<section id="informasi" class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fs-judul fw-bold mb-3">Informasi Lengkap Kos</h2>
        <p class="text-muted fs-5">Semua detail tarif, fasilitas, dan peraturan kos yang transparan.</p>
    </div>

    <div class="row g-4" id="fasilitas">
        @forelse($informasis as $info)
            @php
                $isAturan = str_contains(strtolower($info->kategori), 'aturan');
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card-kustom h-100 d-flex flex-column {{ $isAturan ? 'card-aksen' : '' }}">
                    <span class="badge {{ $isAturan ? 'bg-light text-dark' : 'bg-primary text-white' }} fs-6 mb-3 align-self-start px-3 py-2 rounded-pill">{{ strtoupper($info->kategori) }}</span>
                    <h3 class="fs-4 fw-bold mb-3">{{ $info->judul }}</h3>
                    <p class="{{ $isAturan ? 'text-white-50' : 'text-muted' }} fs-5 mb-0 mt-auto">{{ $info->deskripsi }}</p>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center fs-5 p-4 rounded-4">
                    Belum ada informasi kos yang ditambahkan. Silakan hubungi pengelola langsung.
                </div>
            </div>
        @endforelse
    </div>
</section>
@endsection