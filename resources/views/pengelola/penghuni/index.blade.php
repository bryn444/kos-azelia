@extends('layouts.panel.main')

@section('title', 'Kelola Penghuni - Kos Azelia')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h2 class="fw-bold mb-0">Daftar Penghuni Kos</h2>
    <a href="{{ route('pengelola.penghuni.create') }}" class="btn btn-primary rounded-pill px-4 py-3 fw-bold fs-5">Tambah Penghuni Baru</a>
</div>

<!-- Form Pencarian -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form action="{{ route('pengelola.penghuni') }}" method="GET" class="row g-2">
        <div class="col-md-10">
            <input type="text" name="q" class="form-control form-control-lg fs-5 rounded-pill px-4" placeholder="Cari berdasarkan nama, nomor HP, atau nomor kamar..." value="{{ $search }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fs-5">Cari</button>
        </div>
    </form>
</div>

<div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table table-hover align-middle mb-0" style="font-size: 18px;">
        <thead class="table-light">
            <tr>
                <th class="py-3">Kamar</th>
                <th class="py-3">Nama Penghuni</th>
                <th class="py-3">No. HP</th>
                <th class="py-3">Tgl Masuk</th>
                <th class="py-3">Status Bayar</th>
                <th class="py-3 text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penghunis as $p)
                @php $st = $p->status_pembayaran; @endphp
                <tr class="{{ $p->status === 'keluar' ? 'table-secondary text-muted' : '' }}">
                    <td class="fw-bold text-primary fs-4">{{ $p->kamar->nomor_kamar ?? '-' }}</td>
                    <td>
                        <strong>{{ $p->nama_lengkap }}</strong>
                        @if($p->status === 'keluar')
                            <span class="badge bg-secondary fs-6 ms-2">Sudah Keluar</span>
                        @endif
                    </td>
                    <td>{{ $p->nomor_hp ?: '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d-m-Y') }}</td>
                    <td>
                        @if($p->status === 'aktif')
                            @if($st['warna'] === 'hijau')
                                <span class="badge bg-success fs-6">{{ $st['label'] }}</span>
                            @elseif($st['warna'] === 'kuning')
                                <span class="badge bg-warning text-dark fs-6">{{ $st['label'] }}</span>
                            @else
                                <span class="badge bg-danger fs-6">{{ $st['label'] }}</span>
                            @endif
                            <div class="fs-6 text-muted">{{ $st['keterangan'] }}</div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            @if($p->status === 'aktif')
                                <a href="{{ route('pengelola.penghuni.edit', $p->id) }}" class="btn btn-outline-secondary btn-lg rounded-pill px-3 me-2">Ubah</a>
                                <a href="{{ route('pengelola.pembayaran.riwayat', $p->id) }}" class="btn btn-outline-info btn-lg rounded-pill px-3 me-2">Riwayat</a>
                                <a href="{{ route('pengelola.tambahan_waktu.create', $p->id) }}" class="btn btn-outline-warning text-dark btn-lg rounded-pill px-3 me-2">+Waktu</a>
                                <a href="{{ route('pengelola.penghuni.keluar', $p->id) }}" class="btn btn-outline-danger btn-lg rounded-pill px-3">Keluar</a>
                            @else
                                <a href="{{ route('pengelola.pembayaran.riwayat', $p->id) }}" class="btn btn-outline-info btn-lg rounded-pill px-3">Riwayat</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Tidak ada data penghuni.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection