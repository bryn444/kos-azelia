@extends('layouts.panel.main')

@section('title', 'Kelola Kamar - Kos Azelia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Data 15 Kamar Kos</h2>
    <a href="{{ route('pengelola.kamar.create') }}" class="btn btn-primary rounded-pill px-4 py-3 fw-bold fs-5">Tambah Kamar</a>
</div>

<div class="row g-4">
    @foreach($kamars as $k)
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 {{ $k->status === 'terisi' ? 'bg-white' : 'bg-light border border-2 border-primary' }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold text-primary mb-0">{{ $k->nomor_kamar }}</h3>
                    @if($k->status === 'terisi')
                        <span class="badge bg-danger fs-6 px-3 py-2">Terisi</span>
                    @else
                        <span class="badge bg-success fs-6 px-3 py-2">Kosong</span>
                    @endif
                </div>

                <p class="fs-4 fw-bold text-dark mb-2">Rp{{ number_format($k->tarif, 0, ',', '.') }} /bln</p>

                @if($k->status === 'terisi' && $k->penghuniAktif)
                    <p class="text-muted fs-5 mb-3">Penghuni: <strong>{{ $k->penghuniAktif->nama_lengkap }}</strong></p>
                @else
                    <p class="text-muted fs-5 mb-3">Kamar Siap Ditempati</p>
                @endif

                <div class="mt-auto">
                    <a href="{{ route('pengelola.kamar.edit', $k->id) }}" class="btn btn-outline-primary btn-lg w-100 rounded-pill fs-5">Ubah Data Kamar</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection