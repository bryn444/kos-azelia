@extends('layouts.panel.main')

@section('title', 'Laporan Bulanan - Kos Azelia')

@section('content')
@php
    $routeNamePrefix = auth()->user()->role === 'pengelola' ? 'pengelola.' : 'pemilik.';
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h2 class="fw-bold mb-0">Laporan Bulanan Kos</h2>

    <form action="{{ route($routeNamePrefix . 'laporan') }}" method="GET" class="d-flex align-items-center gap-2">
        <label for="bulan" class="fs-5 fw-semibold text-nowrap">Pilih Bulan:</label>
        <input type="month" id="bulan" name="bulan" class="form-control form-control-lg fs-5 rounded-pill px-3" value="{{ $bulanSelected }}" onchange="this.form.submit()">
    </form>
</div>

<!-- Tombol Cetak PDF & Excel -->
<div class="d-flex gap-3 mb-4">
    <a href="{{ route($routeNamePrefix . 'laporan.pdf', ['bulan' => $bulanSelected]) }}" class="btn btn-danger btn-lg rounded-pill px-4 py-3 fw-bold fs-5">
        Cetak PDF
    </a>
    <a href="{{ route($routeNamePrefix . 'laporan.excel', ['bulan' => $bulanSelected]) }}" class="btn btn-success btn-lg rounded-pill px-4 py-3 fw-bold fs-5">
        Ekspor Excel
    </a>
</div>

<!-- Ringkasan Pemasukan -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <h3 class="fw-bold mb-3">Ringkasan Bulan {{ \Carbon\Carbon::parse($bulanSelected . '-01')->translatedFormat('F Y') }}</h3>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="p-3 bg-light rounded-4">
                <span class="text-muted fs-5">Total Pemasukan Tunai:</span>
                <h2 class="fw-bold text-primary mb-0">Rp{{ number_format($totalPemasukan, 0, ',', '.') }}</h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 bg-light rounded-4">
                <span class="text-muted fs-5">Jumlah Pembayaran Tercatat:</span>
                <h2 class="fw-bold text-dark mb-0">{{ $pembayarans->count() }} Transaksi</h2>
            </div>
        </div>
    </div>
</div>

<!-- Pratinjau Tabel Pembayaran Tercatat -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <h3 class="fw-bold mb-3">Daftar Pembayaran Tercatat</h3>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 18px;">
            <thead class="table-light">
                <tr>
                    <th>Tgl Bayar</th>
                    <th>Kamar</th>
                    <th>Nama Penghuni</th>
                    <th>Jatuh Tempo</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $bayar)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->format('d-m-Y') }}</td>
                        <td>Kamar {{ $bayar->penghuni->kamar->nomor_kamar ?? '-' }}</td>
                        <td><strong>{{ $bayar->penghuni->nama_lengkap ?? '-' }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($bayar->tanggal_jatuh_tempo)->format('d-m-Y') }}</td>
                        <td>Rp{{ number_format($bayar->jumlah, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">Belum ada transaksi pembayaran lunas untuk bulan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pratinjau Tabel Belum Bayar -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h3 class="fw-bold mb-3 text-danger">Daftar Belum Bayar Bulan Ini</h3>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 18px;">
            <thead class="table-light">
                <tr>
                    <th>Kamar</th>
                    <th>Nama Penghuni</th>
                    <th>Status</th>
                    <th>Jatuh Tempo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarBelumBayar as $bb)
                    <tr>
                        <td>Kamar {{ $bb->kamar->nomor_kamar ?? '-' }}</td>
                        <td><strong>{{ $bb->nama_lengkap }}</strong></td>
                        <td><span class="badge bg-danger fs-6">{{ $bb->status_detail['label'] }}</span></td>
                        <td>{{ $bb->status_detail['jatuh_tempo'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-3 text-muted">Tidak ada penunggak pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection