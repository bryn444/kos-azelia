@extends('layouts.panel.main')

@section('title', 'Konfirmasi Tambahan Waktu - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4 text-warning">Konfirmasi Tambahan Waktu</h2>

            <form action="{{ route('pengelola.tambahan_waktu.store', $penghuni->id) }}" method="POST">
                @csrf
                <input type="hidden" name="tanggal_jatuh_tempo_baru" value="{{ $tenggatBaru }}">
                <input type="hidden" name="alasan" value="{{ $alasan }}">

                <div class="bg-light p-4 rounded-4 mb-4 fs-5">
                    <div class="mb-2">Nama Penghuni: <strong>{{ $penghuni->nama_lengkap }}</strong></div>
                    <div class="mb-2">Kamar: <strong>Kamar {{ $penghuni->kamar->nomor_kamar }}</strong></div>
                    <div class="mb-2">Tenggat Pembayaran Baru: <strong class="text-primary">{{ \Carbon\Carbon::parse($tenggatBaru)->format('d-m-Y') }}</strong></div>
                    <div>Alasan: <em>"{{ $alasan }}"</em></div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan Keringanan</button>
                    <a href="{{ route('pengelola.penghuni') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection