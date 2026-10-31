@extends('layouts.panel.main')

@section('title', 'Konfirmasi Penghuni Keluar - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white border-top border-danger border-5">
            <h2 class="fw-bold text-danger mb-3">Konfirmasi Tandai Keluar</h2>
            <p class="fs-5 text-dark mb-4">Apakah Anda yakin ingin menandai penghuni <strong>{{ $penghuni->nama_lengkap }}</strong> (Kamar {{ $penghuni->kamar->nomor_kamar }}) telah keluar dari kos?</p>
            <p class="text-muted fs-6 mb-4">Kamar akan otomatis menjadi **Kosong** dan dapat diisi oleh penghuni baru.</p>

            <form action="{{ route('pengelola.penghuni.keluar.process', $penghuni->id) }}" method="POST">
                @csrf
                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill py-3 fw-bold fs-4">Ya, Tandai Keluar</button>
                    <a href="{{ route('pengelola.penghuni') }}" class="btn btn-outline-secondary btn-lg rounded-pill py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection