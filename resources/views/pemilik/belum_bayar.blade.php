@extends('layouts.pemilik.main')

@section('title', 'Daftar Belum Bayar - Kos Azelia')

@section('content')
<h2 class="fw-bold mb-4">Daftar Penghuni Belum Bayar</h2>

<div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table table-hover align-middle mb-0" style="font-size: 18px;">
        <thead class="table-light">
            <tr>
                <th class="py-3">Kamar</th>
                <th class="py-3">Nama Penghuni</th>
                <th class="py-3">Jatuh Tempo</th>
                <th class="py-3">Status / Tunggakan</th>
                <th class="py-3">Keterangan / Tambahan Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarBelumBayar as $p)
                <tr>
                    <td class="fw-bold text-primary fs-4">{{ $p->kamar->nomor_kamar ?? '-' }}</td>
                    <td><strong>{{ $p->nama_lengkap }}</strong></td>
                    <td>{{ $p->status_detail['jatuh_tempo'] }}</td>
                    <td>
                        @if($p->status_detail['warna'] === 'merah')
                            <span class="badge bg-danger fs-6">{{ $p->status_detail['label'] }}</span>
                            <div class="fs-6 text-danger fw-bold">Terlambat {{ $p->status_detail['hari_terlambat'] ?? 0 }} hari</div>
                        @else
                            <span class="badge bg-warning text-dark fs-6">{{ $p->status_detail['label'] }}</span>
                        @endif
                    </td>
                    <td>
                        @if($p->tambahan_waktu_aktif)
                            <span class="badge bg-warning text-dark fs-6 mb-1">Diberi tambahan waktu</span>
                            <div class="fs-6"><strong>Tenggat Baru:</strong> {{ \Carbon\Carbon::parse($p->tambahan_waktu_aktif->tanggal_jatuh_tempo_baru)->format('d-m-Y') }}</div>
                            <div class="fs-6 text-muted"><em>"{{ $p->tambahan_waktu_aktif->alasan }}"</em></div>
                        @else
                            <span class="text-muted">{{ $p->status_detail['keterangan'] }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Semua penghuni telah melunasi pembayaran bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection