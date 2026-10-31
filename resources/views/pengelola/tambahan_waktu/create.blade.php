@extends('layouts.panel.main')

@section('title', 'Beri Tambahan Waktu - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Beri Tambahan Waktu Pembayaran</h2>

            <form action="{{ route('pengelola.tambahan_waktu.konfirmasi', $penghuni->id) }}" method="POST">
                @csrf
                <div class="bg-light p-4 rounded-4 mb-4 fs-5">
                    <div>Penghuni: <strong>{{ $penghuni->nama_lengkap }}</strong></div>
                    <div>Kamar: <strong>Kamar {{ $penghuni->kamar->nomor_kamar }}</strong></div>
                    <div>Status Saat Ini: <strong class="text-danger">{{ $st['label'] }} ({{ $st['keterangan'] }})</strong></div>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tanggal Tenggat Jatuh Tempo Baru</label>
                    <input type="date" name="tanggal_jatuh_tempo_baru" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tanggal_jatuh_tempo_baru') is-invalid @enderror" value="{{ old('tanggal_jatuh_tempo_baru') }}" required>
                    @error('tanggal_jatuh_tempo_baru')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Alasan Pemberian Keringanan (Wajib)</label>
                    <textarea name="alasan" class="form-control fs-5 rounded-4 p-3 @error('alasan') is-invalid @enderror" rows="3" placeholder="Contoh: Kiriman uang orang tua agak terlambat" required>{{ old('alasan') }}</textarea>
                    @error('alasan')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Lanjut Konfirmasi</button>
                    <a href="{{ route('pengelola.penghuni') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection