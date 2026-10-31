@extends('layouts.panel.main')

@section('title', 'Catat Pembayaran Tunai - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Catat Pembayaran Sewa Tunai</h2>

            <form action="{{ route('pengelola.pembayaran.konfirmasi') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Pilih Penghuni yang Bayar</label>
                    <select name="penghuni_id" class="form-select form-select-lg fs-5 rounded-pill px-4" required>
                        <option value="" disabled selected>-- Pilih Penghuni --</option>
                        @foreach($penghuniAktif as $p)
                            <option value="{{ $p->id }}">Kamar {{ $p->kamar->nomor_kamar }} - {{ $p->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold fs-4">Lanjut Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection