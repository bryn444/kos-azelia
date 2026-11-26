@extends('layouts.panel.main')

{{-- 
  NAMA FILE: index.blade.php (Modul Kamar - DITAMBAHKAN TAHAP 5A & TAHAP 9A)
  FUNGSI: Menampilkan daftar 15 kamar dalam bentuk grid beserta banner sinkronisasi status bila diperlukan.
  CARA MENGUBAH: Ubah struktur HTML grid, banner peringatan, atau card di bawah ini.
--}}

@section('title', 'Kelola Kamar - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: HEADER DAN TOMBOL TAMBAH ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <h2 class="fw-bold mb-0">Data 15 Kamar Kos</h2>
    <a href="{{ route('pengelola.kamar.create') }}" class="btn btn-primary rounded-pill px-4 py-3 fw-bold fs-5">Tambah Kamar</a>
</div>

{{-- ===== BAGIAN: BANNER KETIDAKSESUAIAN STATUS KAMAR (TAHAP 9A) ===== --}}
@if(($jumlahTidakSesuai ?? 0) > 0)
    <div class="alert alert-warning border-warning rounded-4 p-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="fs-5 fw-semibold text-dark">
            Ada {{ $jumlahTidakSesuai }} kamar yang statusnya tidak sesuai dengan penghuni.
        </div>
        <a href="{{ route('pengelola.kamar.sinkron') }}" class="btn btn-warning text-dark fw-bold fs-5 rounded-pill px-4 py-3">
            Perbaiki Status Kamar
        </a>
    </div>
@endif

{{-- ===== BAGIAN: GRID 15 KAMAR ===== --}}
<div class="row g-4">
    @foreach($kamars as $k)
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 {{ $k->status === 'terisi' ? 'bg-white' : 'bg-light border border-2 border-primary' }}">
                
                {{-- DITAMBAHKAN TAHAP 5A: Thumbnail Foto Sampul & Badge Unggulan --}}
                <div class="position-relative" style="height: 180px;">
                    <img src="{{ $k->sampul_url }}" alt="Kamar {{ $k->nomor_kamar }}" class="w-100 h-100 object-fit-cover">
                    
                    @if($k->unggulan)
                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 fs-6 px-3 py-2 shadow-sm">⭐ Unggulan</span>
                    @endif

                    @if($k->status === 'terisi')
                        <span class="badge bg-danger position-absolute top-0 end-0 m-3 fs-6 px-3 py-2">Terisi</span>
                    @else
                        <span class="badge bg-success position-absolute top-0 end-0 m-3 fs-6 px-3 py-2">Kosong</span>
                    @endif
                </div>

                <div class="p-4 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="fw-bold text-primary mb-0">Kamar {{ $k->nomor_kamar }}</h3>
                    </div>

                    <p class="fs-4 fw-bold text-dark mb-2">Rp{{ number_format($k->tarif, 0, ',', '.') }} /bln</p>

                    @if($k->status === 'terisi' && $k->penghuniAktif)
                        <p class="text-muted fs-5 mb-3">Penghuni: <strong>{{ $k->penghuniAktif->nama_lengkap }}</strong></p>
                    @else
                        <p class="text-muted fs-5 mb-3">Kamar Siap Ditempati</p>
                    @endif

                    @if($k->deskripsi)
                        <p class="text-muted small mb-3 text-truncate">{{ $k->deskripsi }}</p>
                    @endif

                    <div class="mt-auto pt-2">
                        <a href="{{ route('pengelola.kamar.edit', $k->id) }}" class="btn btn-outline-primary btn-lg w-100 rounded-pill fs-5">Ubah Data Kamar</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection