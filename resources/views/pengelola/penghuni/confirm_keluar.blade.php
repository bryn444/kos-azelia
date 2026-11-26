@extends('layouts.panel.main')

{{-- 
  NAMA FILE: confirm_keluar.blade.php (Modul Penghuni - DIPERBARUI TAHAP 9A)
  FUNGSI: Konfirmasi penghuni keluar dengan informasi tunggakan bulan dan tanggal keluar.
  CARA MENGUBAH: Sesuaikan teks konfirmasi atau tata letak form bila diperlukan.
--}}

@section('title', 'Konfirmasi Penghuni Keluar - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: KONFIRMASI PENGHUNI KELUAR ===== --}}
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white border-top border-danger border-5">
            <h2 class="fw-bold text-danger mb-3 text-center">Konfirmasi Tandai Keluar</h2>
            <p class="fs-5 text-dark mb-4 text-center">
                Apakah Anda yakin ingin menandai penghuni <strong>{{ $penghuni->nama_lengkap }}</strong> (Kamar {{ $penghuni->kamar->nomor_kamar ?? '-' }}) telah keluar dari kos?
            </p>

            @if(($jumlahBulanMenunggak ?? 0) > 0)
                <div class="alert alert-warning rounded-4 p-4 mb-4 fs-5">
                    Penghuni tercatat masih menunggak <strong>{{ $jumlahBulanMenunggak }} bulan</strong>.
                </div>
            @endif

            <p class="text-muted fs-6 mb-4 text-center">Kamar akan otomatis menjadi **Kosong** setelah penghuni ditandai keluar.</p>

            <form action="{{ route('pengelola.penghuni.keluar.process', $penghuni->id) }}" method="POST">
                @csrf

                <div class="mb-4 text-start">
                    <label class="form-label fs-5 fw-semibold">Tanggal Keluar</label>
                    <input
                        type="date"
                        name="tanggal_keluar"
                        max="{{ date('Y-m-d') }}"
                        class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tanggal_keluar') is-invalid @enderror"
                        value="{{ old('tanggal_keluar', date('Y-m-d')) }}"
                        required
                    >
                    @error('tanggal_keluar')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                @if(($jumlahBulanMenunggak ?? 0) > 0)
                    <div class="mb-4 p-3 bg-light rounded-4 border text-start">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="konfirmasi_tunggakan"
                                value="1"
                                id="konfirmasiTunggakan"
                                class="form-check-input @error('konfirmasi_tunggakan') is-invalid @enderror"
                                {{ old('konfirmasi_tunggakan') ? 'checked' : '' }}
                                required
                            >
                            <label class="form-check-label fw-semibold fs-5 text-dark" for="konfirmasiTunggakan">
                                Penghuni masih menunggak {{ $jumlahBulanMenunggak }} bulan. Tetap tandai keluar.
                            </label>
                            @error('konfirmasi_tunggakan')<div class="invalid-feedback d-block fs-6">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endif

                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill py-3 fw-bold fs-4">Ya, Tandai Keluar</button>
                    <a href="{{ route('pengelola.penghuni') }}" class="btn btn-outline-secondary btn-lg rounded-pill py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection