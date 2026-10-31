@extends('layouts.panel.main')

@section('title', 'Ubah Kamar - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Ubah Data Kamar {{ $kamar->nomor_kamar }}</h2>

            <form action="{{ route('pengelola.kamar.update', $kamar->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nomor Kamar</label>
                    <input type="text" name="nomor_kamar" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('nomor_kamar', $kamar->nomor_kamar) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tarif Sewa Per Bulan (Rp)</label>
                    <input type="number" name="tarif" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('tarif', $kamar->tarif) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Status Kamar</label>
                    <select name="status" class="form-select form-select-lg fs-5 rounded-pill px-4">
                        <option value="kosong" {{ $kamar->status === 'kosong' ? 'selected' : '' }}>Kosong</option>
                        <option value="terisi" {{ $kamar->status === 'terisi' ? 'selected' : '' }}>Terisi</option>
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