@extends('layouts.pemilik.main')

@section('title', 'Atur Tampilan Latar - Kos Azelia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Pengaturan Latar Belakang Tampilan</h2>
    <a href="{{ route('pemilik.lainnya') }}" class="btn btn-secondary rounded-pill px-4 py-2 fs-5">Kembali</a>
</div>

<p class="text-muted fs-5 mb-4">Pilih preset warna gradien kaca yang nyaman di mata atau unggah gambar latar belakang pilihan Anda sendiri.</p>

<!-- 1. Pilihan Preset -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
    <h3 class="fw-bold mb-3 fs-4">Pilih Preset Warna</h3>
    <form action="{{ route('pemilik.tampilan.simpan') }}" method="POST">
        @csrf
        <div class="row g-3 mb-4">
            @php
                $presets = [
                    'biru' => ['nama' => 'Biru Laut (Bawaan)', 'css' => 'linear-gradient(135deg, #3B5F8A 0%, #1F2430 100%)'],
                    'teal' => ['nama' => 'Hijau Teal', 'css' => 'linear-gradient(135deg, #146E6E 0%, #0F232D 100%)'],
                    'senja' => ['nama' => 'Ungu Senja', 'css' => 'linear-gradient(135deg, #783C6E 0%, #23142D 100%)'],
                    'abu' => ['nama' => 'Abu Gelap', 'css' => 'linear-gradient(135deg, #46505F 0%, #14191E 100%)'],
                    'jingga' => ['nama' => 'Jingga Pagi', 'css' => 'linear-gradient(135deg, #B46432 0%, #2D190F 100%)'],
                ];
            @endphp

            @foreach($presets as $key => $p)
                <div class="col-md-4 col-sm-6">
                    <button type="submit" name="preset" value="{{ $key }}" class="btn w-100 p-3 rounded-4 border-2 text-start d-flex align-items-center gap-3 bg-white shadow-sm">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $p['css'] }}; border: 2px solid #ccc;"></div>
                        <span class="fw-bold fs-5 text-dark">{{ $p['nama'] }}</span>
                    </button>
                </div>
            @endforeach
        </div>
    </form>
</div>

<!-- 2. Unggah Gambar Sendiri -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
    <h3 class="fw-bold mb-3 fs-4">Unggah Gambar Latar Sendiri</h3>
    <form action="{{ route('pemilik.tampilan.simpan') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <input type="file" name="gambar_latar" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('gambar_latar') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp" required>
            <small class="text-muted d-block mt-2">Format: JPG, JPEG, PNG, atau WEBP. Batas maksimal ukuran file adalah <strong>10 MB</strong>.</small>
            
            @error('gambar_latar')
                <div class="invalid-feedback fs-6 fw-semibold px-3 mt-2">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-5">Unggah & Terapkan</button>
    </form>
</div>

<!-- 3. Tombol Reset -->
<div class="text-center">
    <form action="{{ route('pemilik.tampilan.reset') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-danger btn-lg rounded-pill px-5 py-3 fw-bold fs-5">Kembalikan ke Bawaan</button>
    </form>
</div>
@endsection