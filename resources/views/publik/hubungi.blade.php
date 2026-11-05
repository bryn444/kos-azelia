{{-- 
  NAMA FILE: resources/views/publik/hubungi.blade.php
  FUNGSI: Menampilkan informasi kontak resmi pengelola dan tautan langsung WhatsApp.
  HALAMAN: Kontak (/hubungi-pengelola).
  CARA MENGUBAH: Sesuaikan teks atau tombol kontak di bawah ini.
--}}
@extends('layouts.publik')

@section('title', 'Hubungi Pengelola - Kos Azelia')

@section('content')
<div class="container my-5 py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white border rounded-4 shadow-sm p-5">
                <span class="badge bg-primary px-4 py-2 rounded-pill mb-3">LAYANAN INFORMASI</span>
                <h1 class="fw-bold mb-3">Hubungi Pengelola Kos Azelia</h1>
                <p class="text-muted mb-4">Ingin survei kamar secara langsung atau menanyakan ketersediaan slot kosong? Silakan hubungi pengelola melalui WhatsApp atau telepon.</p>

                <div class="fs-3 fw-bold text-dark mb-4">{{ config('kos.telepon') }}</div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="https://wa.me/{{ config('kos.whatsapp') }}?text={{ urlencode('Halo, saya ingin menanyakan informasi kamar Kos Azelia.') }}" target="_blank" class="btn-pil-biru px-5 py-3">
                        Hubungi via WhatsApp
                    </a>
                    <a href="tel:{{ config('kos.whatsapp') }}" class="btn-pil-hitam px-5 py-3">
                        Telepon Langsung
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection