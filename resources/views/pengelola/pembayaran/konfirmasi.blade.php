@extends('layouts.panel.main')

@section('title', 'Konfirmasi Pembayaran - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4 text-primary">Konfirmasi Pembayaran Sewa</h2>

            <form action="{{ route('pengelola.pembayaran.store') }}" method="POST">
                @csrf
                <input type="hidden" name="penghuni_id" value="{{ $penghuni->id }}">
                <input type="hidden" name="tanggal_jatuh_tempo" value="{{ $jatuhTempo->toDateString() }}">
                <input type="hidden" name="periode_bulan" value="{{ $jatuhTempo->format('Y-m') }}">

                <div class="bg-light p-4 rounded-4 mb-4 fs-5">
                    <div class="row mb-2"><div class="col-5 text-muted">Nama Penghuni:</div><div class="col-7 fw-bold">{{ $penghuni->nama_lengkap }}</div></div>
                    <div class="row mb-2"><div class="col-5 text-muted">Kamar:</div><div class="col-7 fw-bold">Kamar {{ $penghuni->kamar->nomor_kamar }}</div></div>
                    <div class="row mb-2"><div class="col-5 text-muted">Periode Sewa Ke:</div><div class="col-7 fw-bold">Bulan Ke-{{ $periodeKe }}</div></div>
                    <div class="row"><div class="col-5 text-muted">Tanggal Jatuh Tempo:</div><div class="col-7 fw-bold text-danger">{{ $jatuhTempo->format('d-m-Y') }}</div></div>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tanggal Terima Uang Tunai</label>
                    <input type="date" name="tanggal_bayar" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ $tanggalBayarDefault }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Jumlah Pembayaran (Rp)</label>
                    <input type="number" name="jumlah" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ $jumlahDefault }}" required>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan</button>
                    <a href="{{ route('pengelola.pembayaran') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection