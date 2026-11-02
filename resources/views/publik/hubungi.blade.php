@extends('layouts.publik')

@section('title', 'Hubungi Pengelola - Kos Azelia')

@section('content')
<div class="container my-5 py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-kustom bg-white shadow-sm p-5">
                <span class="badge bg-primary fs-5 px-4 py-2 rounded-pill mb-3 align-self-center">LAYANAN INFORMASI</span>
                <h1 class="fs-judul fw-bold mb-3">Hubungi Pengelola Kos Azelia</h1>
                <p class="text-muted fs-5 mb-4">Ingin survei kamar secara langsung atau menanyakan ketersediaan slot kosong? Silakan hubungi pengelola melalui WhatsApp atau telepon.</p>

                <div class="display-6 fw-bold text-dark mb-4">+62 812-3456-7890</div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="https://wa.me/{{ config('kos.whatsapp') }}" target="_blank" class="btn btn-pil btn-success px-5 py-3 fs-5 fw-bold" style="background-color: #25D366; border: none;">
                        Hubungi via WhatsApp
                    </a>
                    <a href="tel:{{ config('kos.whatsapp') }}" class="btn btn-pil btn-outline-dark px-5 py-3 fs-5 fw-bold">
                        Telepon Langsung
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection