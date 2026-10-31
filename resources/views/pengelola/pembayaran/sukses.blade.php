@extends('layouts.panel.main')

@section('title', 'Pembayaran Tercatat - Kos Azelia')

@section('content')
<div class="row justify-content-center text-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
            <div class="mb-3">
                <span class="badge bg-success p-3 rounded-circle fs-1">✓</span>
            </div>
            <h2 class="fw-bold text-success mb-2">Pembayaran Berhasil Dicatat!</h2>
            <p class="fs-5 text-muted mb-4">Uang sewa dari <strong>{{ $penghuni->nama_lengkap }}</strong> (Kamar {{ $penghuni->kamar->nomor_kamar }}) sebesar <strong>Rp{{ number_format($pembayaran->jumlah, 0, ',', '.') }}</strong> telah diterima secara tunai.</p>

            @php
                $pesanWa = "Halo {$penghuni->nama_lengkap}, pembayaran sewa Kos Azelia Kamar {$penghuni->kamar->nomor_kamar} sebesar Rp" . number_format($pembayaran->jumlah, 0, ',', '.') . " untuk periode jatuh tempo " . \Carbon\Carbon::parse($pembayaran->tanggal_jatuh_tempo)->format('d-m-Y') . " telah diterima pada " . \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d-m-Y') . ". Terima kasih!";
            @endphp

            <div class="d-grid gap-3 mb-4">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $pesanWa }}'); alert('Teks WhatsApp berhasil disalin!');" class="btn btn-outline-success btn-lg rounded-pill py-3 fw-bold fs-4">
                    Salin Teks WhatsApp
                </button>
                <a href="{{ route('pengelola.beranda') }}" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold fs-4">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>
@endsection