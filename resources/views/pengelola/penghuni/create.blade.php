@extends('layouts.panel.main')

{{-- 
  NAMA FILE: create.blade.php (Modul Penghuni - DIPERBARUI TAHAP 9A)
  FUNGSI: Form tambah penghuni baru dengan validasi format nomor HP Indonesia dan tanggal masuk.
  CARA MENGUBAH: Sesuaikan teks bantuan format nomor HP atau batasan tanggal masuk bila diperlukan.
--}}

@section('title', 'Tambah Penghuni Baru - Kos Azelia')

@section('content')
@php
    $tglMaks = \Carbon\Carbon::today()->addDays(30)->toDateString();
    $tglMin = \Carbon\Carbon::today()->subYears(5)->toDateString();
@endphp

{{-- ===== BAGIAN: FORM TAMBAH PENGHUNI BARU ===== --}}
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Tambah Data Penghuni Baru</h2>

            <form action="{{ route('pengelola.penghuni.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nama Lengkap Penghuni</label>
                    <input type="text" name="nama_lengkap" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap') }}" required>
                    @error('nama_lengkap')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nomor HP / WhatsApp (Opsional)</label>
                    <input type="text" name="nomor_hp" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('nomor_hp') is-invalid @enderror" value="{{ old('nomor_hp') }}" placeholder="Contoh: 081234567890">
                    <small class="text-muted d-block mt-2 fs-6">Format Indonesia (contoh: 081234567890 atau +6281234567890). Disimpan otomatis sebagai 08xxxxxxxxxx.</small>
                    @error('nomor_hp')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Pilih Kamar (Hanya Kamar Kosong)</label>
                    <select name="kamar_id" class="form-select form-select-lg fs-5 rounded-pill px-4 @error('kamar_id') is-invalid @enderror" required>
                        <option value="" disabled {{ old('kamar_id') ? '' : 'selected' }}>-- Pilih Kamar --</option>
                        @foreach($kamarKosong as $k)
                            <option value="{{ $k->id }}" {{ old('kamar_id') == $k->id ? 'selected' : '' }}>Kamar {{ $k->nomor_kamar }} (Rp{{ number_format($k->tarif, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @error('kamar_id')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tanggal Masuk Kos</label>
                    <input type="date" name="tanggal_masuk" min="{{ $tglMin }}" max="{{ $tglMaks }}" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tanggal_masuk') is-invalid @enderror" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required>
                    @error('tanggal_masuk')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan</button>
                    <a href="{{ route('pengelola.penghuni') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection