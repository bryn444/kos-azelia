@extends('layouts.panel.main')

@section('title', 'Tambah Informasi Kos - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Tambah Informasi Kos</h2>

            <form action="{{ route('pengelola.lainnya.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Kategori</label>
                    <input type="text" name="kategori" class="form-control form-control-lg fs-5 rounded-pill px-4" placeholder="Contoh: harga / fasilitas / aturan" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Judul Informasi</label>
                    <input type="text" name="judul" class="form-control form-control-lg fs-5 rounded-pill px-4" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Deskripsi / Isi</label>
                    <textarea name="deskripsi" class="form-control fs-5 rounded-4 p-3" rows="3" required></textarea>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan</button>
                    <a href="{{ route('pengelola.lainnya') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection