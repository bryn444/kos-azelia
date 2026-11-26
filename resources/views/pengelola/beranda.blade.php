@extends('layouts.panel.main')

{{-- 
  NAMA FILE: beranda.blade.php (DIPERBARUI TAHAP 9A)
  FUNGSI: Menampilkan ringkasan kamar & tunggakan, kartu "Perlu Dicek", daftar menunggak, dan jatuh tempo 7 hari.
  CARA MENGUBAH: Sesuaikan ambang atau teks peringatan pada BerandaController dan config/kos.php.
--}}

@section('title', 'Beranda Pengelola - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: RINGKASAN UTAMA ===== --}}
<div class="row mb-4 g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white bg-primary">
            <h4 class="fs-5 text-white-50 fw-normal">Kamar Terisi</h4>
            <h1 class="display-3 fw-bold text-white mb-0">{{ $totalKamarTerisi }} <span class="fs-4 fw-normal">/ 15 Kamar</span></h1>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white bg-danger">
            <h4 class="fs-5 text-white-50 fw-normal">Penghuni Menunggak</h4>
            <h1 class="display-3 fw-bold text-white mb-0">{{ $totalMenunggak }} <span class="fs-4 fw-normal">Orang</span></h1>
        </div>
    </div>
</div>

{{-- ===== BAGIAN: KARTU PERLU DICEK (TAHAP 9A) ===== --}}
@if(!empty($itemPerluDicek))
    <div class="mb-5">
        <div class="card border-warning border-2 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold text-dark mb-3">Perlu Dicek</h2>
            <ul class="list-group list-group-flush fs-5">
                @foreach($itemPerluDicek as $item)
                    <li class="list-group-item px-0 py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <span class="fw-semibold text-dark">{{ $item['teks'] }}</span>
                        <a href="{{ $item['aksi_url'] }}" class="btn btn-{{ $item['aksi_style'] }} {{ $item['aksi_style'] === 'warning' ? 'text-dark' : '' }} btn-lg rounded-pill px-4 py-2 fw-bold">
                            {{ $item['aksi_label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ===== BAGIAN: DAFTAR KARTU MENUNGGAK ===== --}}
<div class="mb-5">
    <h2 class="fw-bold text-danger mb-3">Menunggak Pembayaran</h2>
    @forelse($daftarMenunggak as $p)
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-3 border-start border-danger border-5">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <span class="badge bg-secondary fs-6 mb-2">Kamar {{ $p->kamar->nomor_kamar }}</span>
                    <h3 class="fw-bold text-dark mb-1">{{ $p->nama_lengkap }}</h3>
                    <p class="text-danger fw-bold fs-5 mb-0">Terlambat {{ $p->status_detail['hari_terlambat'] }} hari (Jatuh tempo: {{ $p->status_detail['jatuh_tempo'] }})</p>
                </div>
                <div class="col-md-5 text-md-end">
                    <form action="{{ route('pengelola.pembayaran.konfirmasi') }}" method="POST">
                        @csrf
                        <input type="hidden" name="penghuni_id" value="{{ $p->id }}">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold">Catat Bayar</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-success fs-5 p-4 rounded-4 shadow-sm">
            Tidak ada penghuni yang menunggak pembayaran saat ini.
        </div>
    @endforelse
</div>

{{-- ===== BAGIAN: DAFTAR JATUH TEMPO 7 HARI KE DEPAN ===== --}}
<div>
    <h2 class="fw-bold text-dark mb-3">Jatuh Tempo 7 Hari Ke Depan</h2>
    @forelse($daftarJatuhTempo7Hari as $p)
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-3 border-start border-warning border-5">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <span class="badge bg-secondary fs-6 mb-2">Kamar {{ $p->kamar->nomor_kamar }}</span>
                    @if($p->status_detail['kode'] === 'tambahan_waktu')
                        <span class="badge bg-warning text-dark fs-6 mb-2 ms-1">Diberi tambahan waktu</span>
                    @endif
                    <h3 class="fw-bold text-dark mb-1">{{ $p->nama_lengkap }}</h3>
                    <p class="text-dark fs-5 mb-0">{{ $p->status_detail['keterangan'] }} (Tanggal: {{ $p->status_detail['jatuh_tempo'] }})</p>
                </div>
                <div class="col-md-5 text-md-end">
                    <form action="{{ route('pengelola.pembayaran.konfirmasi') }}" method="POST">
                        @csrf
                        <input type="hidden" name="penghuni_id" value="{{ $p->id }}">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold">Catat Bayar</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info fs-5 p-4 rounded-4 shadow-sm">
            Tidak ada jatuh tempo dalam 7 hari ke depan.
        </div>
    @endforelse
</div>
@endsection