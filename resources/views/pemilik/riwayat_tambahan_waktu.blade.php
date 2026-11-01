@extends('layouts.panel.main')

@section('title', 'Riwayat Tambahan Waktu - Kos Azelia')

@section('content')
<h2 class="fw-bold mb-4">Riwayat Keringanan Tambahan Waktu</h2>

<div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table table-hover align-middle mb-0" style="font-size: 18px;">
        <thead class="table-light">
            <tr>
                <th class="py-3">Tgl Diberikan</th>
                <th class="py-3">Penghuni</th>
                <th class="py-3">Kamar</th>
                <th class="py-3">Tenggat Baru</th>
                <th class="py-3">Alasan Keringanan</th>
                <th class="py-3">Diberikan Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayats as $t)
                <tr>
                    <td>{{ $t->created_at->format('d-m-Y') }}</td>
                    <td><strong>{{ $t->penghuni->nama_lengkap ?? '-' }}</strong></td>
                    <td>Kamar {{ $t->penghuni->kamar->nomor_kamar ?? '-' }}</td>
                    <td><strong class="text-primary">{{ \Carbon\Carbon::parse($t->tanggal_jatuh_tempo_baru)->format('d-m-Y') }}</strong></td>
                    <td><em>"{{ $t->alasan }}"</em></td>
                    <td>{{ $t->pembuat->nama_lengkap ?? 'Kakek' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data riwayat tambahan waktu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection