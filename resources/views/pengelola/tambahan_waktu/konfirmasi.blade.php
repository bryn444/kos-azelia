@extends('layouts.panel.main')

{{-- 
  NAMA FILE: konfirmasi.blade.php (Modul Tambahan Waktu - DIPERBARUI TAHAP 9A)
  FUNGSI: Konfirmasi tambahan waktu dengan jatuh tempo semula, tenggat baru, selisih hari, dan cek duplikasi aktif.
  CARA MENGUBAH: Sesuaikan tambahan_waktu_maks_hari pada config/kos.php bagian 'aturan'.
--}}

@section('title', 'Konfirmasi Tambahan Waktu - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: KONFIRMASI TAMBAHAN WAKTU ===== --}}
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4 text-warning">Konfirmasi Tambahan Waktu</h2>

            <form action="{{ route('pengelola.tambahan_waktu.store', $penghuni->id) }}" method="POST">
                @csrf
                <input type="hidden" name="tanggal_jatuh_tempo_baru" value="{{ $tenggatBaru }}">
                <input type="hidden" name="alasan" value="{{ $alasan }}">

                <div class="bg-light border rounded-4 p-4 mb-4 fs-5">
                    <div class="mb-2">Nama Penghuni: <strong>{{ $penghuni->nama_lengkap }}</strong></div>
                    <div class="mb-2">Kamar: <strong>Kamar {{ $penghuni->kamar->nomor_kamar }}</strong></div>
                    <div class="mb-2">Jatuh Tempo Semula: <strong>{{ $jatuhTempoSemula->format('d-m-Y') }}</strong></div>
                    <div class="mb-2">Tenggat Pembayaran Baru: <strong class="text-primary">{{ \Carbon\Carbon::parse($tenggatBaru)->format('d-m-Y') }}</strong></div>
                    <div class="mb-2">Selisih Waktu: <strong class="text-success">+{{ $selisihHari }} hari</strong></div>
                    <div>Alasan: <em>"{{ $alasan }}"</em></div>
                </div>

                @if(!empty($tambahanAktif))
                    <div class="alert alert-warning rounded-4 p-4 mb-4 fs-5">
                        <div class="fw-bold text-dark mb-2">
                            Penghuni sudah memiliki tambahan waktu aktif (Tenggat aktif saat ini: {{ \Carbon\Carbon::parse($tambahanAktif->tanggal_jatuh_tempo_baru)->format('d-m-Y') }}).
                        </div>
                        <div class="form-check mt-2">
                            <input
                                type="checkbox"
                                name="konfirmasi_duplikat_aktif"
                                value="1"
                                id="konfirmasiDuplikatAktif"
                                class="form-check-input @error('konfirmasi_duplikat_aktif') is-invalid @enderror"
                                {{ old('konfirmasi_duplikat_aktif') ? 'checked' : '' }}
                                required
                            >
                            <label class="form-check-label fw-semibold fs-6 text-dark" for="konfirmasiDuplikatAktif">
                                Saya mengerti dan tetap ingin mengganti dengan tambahan waktu baru ini.
                            </label>
                            @error('konfirmasi_duplikat_aktif')<div class="invalid-feedback d-block fs-6">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endif

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan Keringanan</button>
                    <a href="{{ route('pengelola.penghuni') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection