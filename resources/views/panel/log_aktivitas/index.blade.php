@extends('layouts.panel.main')

{{-- 
  NAMA FILE: index.blade.php (DITAMBAHKAN TAHAP 9B)
  FUNGSI: Halaman Log Aktivitas hanya baca dengan filter tanggal & aksi serta modal rincian data lama/baru.
  CARA MENGUBAH: Sesuaikan kolom atau label filter bila diperlukan.
--}}

@section('title', 'Log Aktivitas - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: HEADER HALAMAN ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <h2 class="fw-bold mb-0">Log Aktivitas</h2>
</div>

{{-- ===== BAGIAN: FILTER TANGGAL DAN JENIS AKSI ===== --}}
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <form action="{{ route($ruteLog) }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fs-5 fw-semibold">Filter Tanggal</label>
            <input type="date" name="tanggal" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ $tanggal }}">
        </div>

        <div class="col-md-5">
            <label class="form-label fs-5 fw-semibold">Filter Jenis Aksi</label>
            <select name="aksi" class="form-select form-select-lg fs-5 rounded-pill px-4">
                <option value="">Semua Aksi</option>
                @foreach($daftarAksi as $jenisAksi)
                    <option value="{{ $jenisAksi }}" {{ $aksi === $jenisAksi ? 'selected' : '' }}>{{ $jenisAksi }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-4 flex-fill fw-bold fs-5">Filter</button>
            <a href="{{ route($ruteLog) }}" class="btn btn-secondary btn-lg rounded-pill px-3 fs-5 d-inline-flex align-items-center">Reset</a>
        </div>
    </form>
</div>

{{-- ===== BAGIAN: TABEL LOG AKTIVITAS (HANYA BACA) ===== --}}
<div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table table-hover align-middle mb-0 fs-5">
        <thead class="table-light">
            <tr>
                <th class="py-3">Waktu</th>
                <th class="py-3">Siapa</th>
                <th class="py-3">Deskripsi</th>
                <th class="py-3 text-end">Rincian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="text-nowrap">
                        {{ $log->created_at ? $log->created_at->format('d-m-Y H:i:s') : '-' }}
                    </td>
                    <td>
                        <strong>{{ $log->pengguna->nama_lengkap ?? 'Sistem / Tamu' }}</strong>
                        @if($log->pengguna)
                            <div class="text-muted fs-6 text-capitalize">{{ $log->pengguna->peran }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border fs-6 mb-1">{{ $log->aksi }}</span>
                        <div>{{ $log->deskripsi }}</div>
                    </td>
                    <td class="text-end">
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-lg rounded-pill px-3"
                            data-bs-toggle="modal"
                            data-bs-target="#modalRincianLog{{ $log->id }}"
                        >
                            Lihat rincian
                        </button>

                        {{-- Modal rincian data lama & baru (di-escape, tanpa HTML) --}}
                        <div class="modal fade" id="modalRincianLog{{ $log->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content rounded-4 p-3 text-start">
                                    <div class="modal-header border-0">
                                        <h4 class="modal-title fw-bold">Rincian Aktivitas</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body fs-6">
                                        <p class="mb-2"><strong>Waktu:</strong> {{ $log->created_at ? $log->created_at->format('d-m-Y H:i:s') : '-' }}</p>
                                        <p class="mb-2"><strong>Pelaku:</strong> {{ $log->pengguna->nama_lengkap ?? 'Sistem / Tamu' }}</p>
                                        <p class="mb-2"><strong>Aksi:</strong> {{ $log->aksi }}</p>
                                        <p class="mb-3"><strong>Deskripsi:</strong> {{ $log->deskripsi }}</p>

                                        <div class="mb-3">
                                            <div class="fw-bold mb-1">Data Lama</div>
                                            <pre class="bg-light border rounded-3 p-3 mb-0 text-break">{{ $log->data_lama !== null ? json_encode($log->data_lama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</pre>
                                        </div>

                                        <div>
                                            <div class="fw-bold mb-1">Data Baru</div>
                                            <pre class="bg-light border rounded-3 p-3 mb-0 text-break">{{ $log->data_baru !== null ? json_encode($log->data_baru, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</pre>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-secondary btn-lg rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">Belum ada catatan log aktivitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ===== BAGIAN: PAGINASI ===== --}}
<div class="mt-4">
    {{ $logs->links() }}
</div>
@endsection