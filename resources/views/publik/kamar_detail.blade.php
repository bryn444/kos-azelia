{{-- 
  NAMA FILE: resources/views/publik/kamar_detail.blade.php
  FUNGSI: Menampilkan detail lengkap suatu kamar beserta galeri foto (tanpa data pribadi penghuni).
  HALAMAN: Halaman Detail Kamar (/kamar/{kamar}).
  CARA MENGUBAH: Sesuaikan tata letak galeri atau tombol WhatsApp.
--}}
@extends('layouts.publik')

@section('title', 'Kamar ' . $kamar->nomor_kamar . ' - Kos Azelia')

@section('content')
<div class="container my-5">
    <div class="mb-4">
        <a href="{{ route('publik.index') }}#kamar" class="text-decoration-none text-muted fw-semibold">← Kembali ke Beranda</a>
    </div>

    <div class="row g-5">
        <div class="col-lg-7">
            {{-- Foto Utama --}}
            <div class="mb-3" style="height: 400px; border-radius: 24px; overflow: hidden; background-color: #E5E7EB;">
                <img src="{{ $kamar->sampul_url }}" alt="Kamar {{ $kamar->nomor_kamar }}" id="fotoUtama" class="w-100 h-100 object-fit-cover shadow-sm">
            </div>

            {{-- Thumbnail Galeri Foto --}}
            @if($kamar->fotos->count() > 1)
                <div class="d-flex gap-2 overflow-auto pb-2">
                    @foreach($kamar->fotos as $f)
                        <button type="button" class="btn p-0 border-0 rounded-3 overflow-hidden thumb-btn" style="width: 80px; height: 80px; flex: 0 0 auto;" onclick="document.getElementById('fotoUtama').src='{{ $f->url }}'">
                            <img src="{{ $f->url }}" alt="Thumbnail" class="w-100 h-100 object-fit-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="p-4 bg-light rounded-4 border h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="fw-bold mb-0">Kamar {{ $kamar->nomor_kamar }}</h1>
                    @if($kamar->status === 'terisi')
                        <span class="badge bg-secondary px-3 py-2 fs-6">Terisi</span>
                    @else
                        <span class="badge bg-primary px-3 py-2 fs-6">Tersedia</span>
                    @endif
                </div>

                <div class="fs-3 fw-bold text-primary mb-4">Rp{{ number_format($kamar->tarif, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">/ bulan</span></div>

                <h3 class="fw-bold fs-5 mb-2">Deskripsi Kamar</h3>
                <p class="text-muted mb-4">{{ $kamar->deskripsi ?? 'Kamar bersih, nyaman, dan sudah termasuk fasilitas listrik, air, serta WiFi.' }}</p>

                <div class="mt-auto pt-3 border-top">
                    <a href="https://wa.me/{{ config('kos.whatsapp') }}?text={{ urlencode('Halo, saya ingin menanyakan ketersediaan Kamar ' . $kamar->nomor_kamar . '.') }}" target="_blank" class="btn-pil-biru w-100 text-center py-3">Tanya Ketersediaan via WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection