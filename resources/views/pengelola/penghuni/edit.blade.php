@extends('layouts.panel.main')

@section('title', 'Ubah Penghuni - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Ubah Data Penghuni {{ $penghuni->nama_lengkap }}</h2>

            <form action="{{ route('pengelola.penghuni.update', $penghuni->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('nama_lengkap', $penghuni->nama_lengkap) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nomor HP / WhatsApp</label>
                    <input type="text" name="nomor_hp" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('nomor_hp', $penghuni->nomor_hp) }}">
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Kamar Ditempati</label>
                    <select name="kamar_id" class="form-select form-select-lg fs-5 rounded-pill px-4" required>
                        @foreach($kamarKosong as $k)
                            <option value="{{ $k->id }}" {{ $penghuni->kamar_id == $k->id ? 'selected' : '' }}>Kamar {{ $k->nomor_kamar }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tanggal Masuk Kos</label>
                    <input type="date" name="tanggal_masuk" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('tanggal_masuk', $penghuni->tanggal_masuk) }}" required>
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