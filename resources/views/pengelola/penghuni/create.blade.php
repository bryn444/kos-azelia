@extends('layouts.panel.main')

@section('title', 'Tambah Penghuni Baru - Kos Azelia')

@section('content')
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
                    <input type="text" name="nomor_hp" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('nomor_hp') }}">
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Pilih Kamar (Hanya Kamar Kosong)</label>
                    <select name="kamar_id" class="form-select form-select-lg fs-5 rounded-pill px-4 @error('kamar_id') is-invalid @enderror" required>
                        <option value="" disabled selected>-- Pilih Kamar --</option>
                        @foreach($kamarKosong as $k)
                            <option value="{{ $k->id }}">Kamar {{ $k->nomor_kamar }} (Rp{{ number_format($k->tarif, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @error('kamar_id')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tanggal Masuk Kos</label>
                    <input type="date" name="tanggal_masuk" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tanggal_masuk') is-invalid @enderror" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required>
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