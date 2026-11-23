@extends('layouts.panel.main')

{{--
  NAMA FILE: konfirmasi_hapus.blade.php (DITAMBAHKAN TAHAP 8)
  FUNGSI: Halaman konfirmasi sebelum menghapus permanen data kamar beserta seluruh fotonya.
  HALAMAN: Panel Pengelola - Konfirmasi Hapus Kamar.
  CARA MENGUBAH: Sesuaikan teks ringkasan kamar atau label tombol konfirmasi bila diperlukan.
--}}

@section('title', 'Konfirmasi Hapus Kamar - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: KARTU KONFIRMASI HAPUS KAMAR ===== --}}
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card border-danger shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h2 class="fw-bold text-danger mb-3">Konfirmasi Hapus Kamar {{ $kamar->nomor_kamar }}</h2>

            {{-- ===== BAGIAN: PERINGATAN UTAMA ===== --}}
            <div class="alert alert-danger rounded-4 p-4 mb-4" role="alert">
                <p class="fs-4 fw-bold mb-0">Kamar yang dihapus TIDAK bisa dikembalikan, termasuk foto-fotonya.</p>
            </div>

            {{-- ===== BAGIAN: RINGKASAN DATA KAMAR ===== --}}
            <div class="bg-light border rounded-4 p-4 mb-4">
                <h3 class="h5 fw-bold mb-3">Ringkasan Kamar</h3>
                <ul class="list-group list-group-flush bg-transparent fs-5">
                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                        <span class="text-muted">Nomor Kamar</span>
                        <span class="fw-bold">{{ $kamar->nomor_kamar }}</span>
                    </li>
                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                        <span class="text-muted">Tarif Sewa</span>
                        <span class="fw-bold">Rp{{ number_format($kamar->tarif, 0, ',', '.') }} / bulan</span>
                    </li>
                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                        <span class="text-muted">Status Kamar</span>
                        <span class="fw-bold text-capitalize">{{ $kamar->status }}</span>
                    </li>
                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                        <span class="text-muted">Jumlah Foto</span>
                        <span class="fw-bold">{{ $kamar->fotos_count }} foto</span>
                    </li>
                    <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                        <span class="text-muted">Penanda Unggulan</span>
                        <span class="fw-bold">{{ $kamar->unggulan ? 'Ya (Unggulan)' : 'Tidak' }}</span>
                    </li>
                </ul>
            </div>

            {{-- ===== BAGIAN: FORM AKSI YA / BATAL ===== --}}
            <form action="{{ route('pengelola.kamar.destroy', $kamar->id) }}" method="POST">
                @csrf
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-4 py-3 fw-bold fs-4 flex-fill">
                        Ya, Hapus Kamar
                    </button>
                    <a href="{{ route('pengelola.kamar.edit', $kamar->id) }}" class="btn btn-secondary btn-lg rounded-pill px-4 py-3 fw-bold fs-4 d-inline-flex align-items-center justify-content-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection