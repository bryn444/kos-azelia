@extends('layouts.panel.main')

@section('title', 'Tambah Kamar Baru - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Tambah Kamar Baru</h2>

            <form action="{{ route('pengelola.kamar.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nomor Kamar</label>
                    <input type="text" name="nomor_kamar" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('nomor_kamar') is-invalid @enderror" value="{{ old('nomor_kamar') }}" placeholder="Contoh: K16" required>
                    @error('nomor_kamar')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tarif Sewa Per Bulan (Rp)</label>
                    <input type="number" name="tarif" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tarif') is-invalid @enderror" value="{{ old('tarif', 550000) }}" required>
                    @error('tarif')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Status Kamar</label>
                    <select name="status" class="form-select form-select-lg fs-5 rounded-pill px-4">
                        <option value="kosong" selected>Kosong</option>
                        <option value="terisi">Terisi</option>
                    </select>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan</button>
                    <a href="{{ route('pengelola.kamar') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection