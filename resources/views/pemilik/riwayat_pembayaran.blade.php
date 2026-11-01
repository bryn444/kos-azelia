@extends('layouts.pemilik.main')

@section('title', 'Riwayat Pembayaran - Kos Azelia')

@section('content')
<h2 class="fw-bold mb-4">Riwayat Pembayaran Sewa</h2>

<!-- Filter Form -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <form action="{{ route('pemilik.riwayat_pembayaran') }}" method="GET" class="row g-3">
        <div class="col-md-5">
            <label class="form-label fs-5 fw-semibold">Filter Bulan Periode</label>
            <input type="month" name="bulan" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ $bulan }}">
        </div>
        <div class="col-md-5">
            <label class="form-label fs-5 fw-semibold">Filter Penghuni</label>
            <select name="penghuni_id" class="form-select form-select-lg fs-5 rounded-pill px-4">
                <option value="">-- Semua Penghuni --</option>
                @foreach($semuaPenghuni as $p)
                    <option value="{{ $p->id }}" {{ $penghuniId == $p->id ? 'selected' : '' }}>{{ $p->nama_lengkap }} (Kamar {{ $p->kamar->nomor_kamar ?? '-' }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fs-5">Terapkan</button>
        </div>
    </form>
</div>

<div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table table-hover align-middle mb-0" style="font-size: 18px;">
        <thead class="table-light">
            <tr>
                <th class="py-3">Tgl Bayar</th>
                <th class="py-3">Penghuni</th>
                <th class="py-3">Kamar</th>
                <th class="py-3">Jatuh Tempo</th>
                <th class="py-3">Jumlah</th>
                <th class="py-3">Status / Alasan Batal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $r)
                <tr class="{{ $r->dibatalkan_pada ? 'table-danger text-muted' : '' }}">
                    <td>{{ \Carbon\Carbon::parse($r->tanggal_bayar)->format('d-m-Y') }}</td>
                    <td><strong>{{ $r->penghuni->nama_lengkap ?? '-' }}</strong></td>
                    <td>{{ $r->penghuni->kamar->nomor_kamar ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->tanggal_jatuh_tempo)->format('d-m-Y') }}</td>
                    <td>
                        @if($r->dibatalkan_pada)
                            <del>Rp{{ number_format($r->jumlah, 0, ',', '.') }}</del>
                        @else
                            Rp{{ number_format($r->jumlah, 0, ',', '.') }}
                        @endif
                    </td>
                    <td>
                        @if($r->dibatalkan_pada)
                            <span class="badge bg-danger fs-6">Dibatalkan</span>
                            <div class="fs-6 text-danger">Alasan: {{ $r->alasan_batal }}</div>
                        @else
                            <span class="badge bg-success fs-6">Lunas</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Tidak ada riwayat pembayaran yang cocok dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection