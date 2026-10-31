@extends('layouts.panel.main')

@section('title', 'Riwayat Pembayaran - Kos Azelia')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Riwayat Bayar: {{ $penghuni->nama_lengkap }} (Kamar {{ $penghuni->kamar->nomor_kamar }})</h2>
    <a href="{{ route('pengelola.penghuni') }}" class="btn btn-secondary rounded-pill px-4 py-2 fs-5">Kembali</a>
</div>

<div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table table-hover align-middle mb-0" style="font-size: 18px;">
        <thead class="table-light">
            <tr>
                <th class="py-3">Tgl Bayar</th>
                <th class="py-3">Jatuh Tempo</th>
                <th class="py-3">Jumlah</th>
                <th class="py-3">Dicatat Oleh</th>
                <th class="py-3">Status</th>
                <th class="py-3 text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayats as $r)
                <tr class="{{ $r->dibatalkan_pada ? 'table-danger' : '' }}">
                    <td>{{ \Carbon\Carbon::parse($r->tanggal_bayar)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->tanggal_jatuh_tempo)->format('d-m-Y') }}</td>
                    <td>Rp{{ number_format($r->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $r->pencatat->nama_lengkap ?? 'Kakek' }}</td>
                    <td>
                        @if($r->dibatalkan_pada)
                            <span class="badge bg-danger fs-6">Dibatalkan</span>
                            <div class="fs-6 text-muted">Alasan: {{ $r->alasan_batal }}</div>
                        @else
                            <span class="badge bg-success fs-6">Lunas (Tunai)</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if(!$r->dibatalkan_pada)
                            <button class="btn btn-outline-danger btn-lg rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBatal{{ $r->id }}">Batalkan</button>

                            <!-- Modal Batalkan -->
                            <div class="modal fade" id="modalBatal{{ $r->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 p-3 text-start">
                                        <form action="{{ route('pengelola.pembayaran.batalkan', $r->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-0">
                                                <h4 class="modal-title fw-bold text-danger">Batalkan Pembayaran</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p class="fs-5">Masukkan alasan pembatalan pencatatan ini:</p>
                                                <textarea name="alasan_batal" class="form-control fs-5 rounded-4" rows="3" required></textarea>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="submit" class="btn btn-danger btn-lg rounded-pill px-4">Ya, Batalkan</button>
                                                <button type="button" class="btn btn-secondary btn-lg rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Belum ada catatan pembayaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection