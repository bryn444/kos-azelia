@extends('layouts.panel.main')

{{-- 
  NAMA FILE: konfirmasi.blade.php (DIPERBARUI TAHAP 9A)
  FUNGSI: Halaman konfirmasi pembayaran tunai dengan ringkasan periode, riwayat bayar terakhir, dan pengaman input.
  CARA MENGUBAH: Sesuaikan batas hari pembayaran lama pada config/kos.php bagian 'aturan'.
--}}

@section('title', 'Konfirmasi Pembayaran - Kos Azelia')

@section('content')
@php
    // DITAMBAHKAN TAHAP 9A: Hitung kondisi selisih nominal dan tanggal bayar lama untuk peringatan
    $nilaiJumlahSekarang = (int) old('jumlah', $jumlahDefault);
    $nominalBedaSekarang = ($nilaiJumlahSekarang !== (int) $tarifKamar);
    $tampilkanKotakNominalBeda = $nominalBedaSekarang || $errors->has('konfirmasi_nominal_beda') || $errors->has('catatan');

    $tglBayarSekarang = old('tanggal_bayar', $tanggalBayarDefault);
    $selisihHariLalu = 0;
    try {
        $tglParsed = \Carbon\Carbon::parse($tglBayarSekarang)->startOfDay();
        if ($tglParsed->lessThan(\Carbon\Carbon::today())) {
            $selisihHariLalu = $tglParsed->diffInDays(\Carbon\Carbon::today());
        }
    } catch (\Throwable $e) {
        $selisihHariLalu = 0;
    }
    $tanggalTerlaluLama = ($selisihHariLalu > ($bayarLamaHari ?? 30));
    $tampilkanKotakTanggalLama = $tanggalTerlaluLama || $errors->has('konfirmasi_tanggal_lama');
@endphp

{{-- ===== BAGIAN: FORM KONFIRMASI PEMBAYARAN TUNAI ===== --}}
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4 text-primary">Konfirmasi Pembayaran Sewa</h2>

            <form action="{{ route('pengelola.pembayaran.store') }}" method="POST" id="formKonfirmasiPembayaran">
                @csrf
                <input type="hidden" name="penghuni_id" value="{{ $penghuni->id }}">
                <input type="hidden" name="tanggal_jatuh_tempo" value="{{ $jatuhTempo->toDateString() }}">
                <input type="hidden" name="periode_bulan" value="{{ $jatuhTempo->format('Y-m') }}">
                <input type="hidden" name="idempotency_token" value="{{ $tokenIdempoten }}">

                {{-- ===== BAGIAN: KOTAK RINGKASAN BESAR ===== --}}
                <div class="bg-light border rounded-4 p-4 mb-4 fs-5">
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Nama Penghuni:</div>
                        <div class="col-sm-7 fw-bold">{{ $penghuni->nama_lengkap }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Kamar:</div>
                        <div class="col-sm-7 fw-bold">Kamar {{ $penghuni->kamar->nomor_kamar }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Periode:</div>
                        <div class="col-sm-7 fw-bold">{{ $rentangPeriode }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Tarif Kamar:</div>
                        <div class="col-sm-7 fw-bold">Rp{{ number_format($tarifKamar, 0, ',', '.') }} / bulan</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Tanggal Jatuh Tempo:</div>
                        <div class="col-sm-7 fw-bold text-danger">{{ $jatuhTempo->format('d-m-Y') }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5 text-muted">Pembayaran terakhir penghuni ini:</div>
                        <div class="col-sm-7 fw-bold">
                            @if($pembayaranTerakhir)
                                {{ \Carbon\Carbon::parse($pembayaranTerakhir->tanggal_bayar)->format('d-m-Y') }}, Rp{{ number_format($pembayaranTerakhir->jumlah, 0, ',', '.') }}
                            @else
                                Belum ada pembayaran
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ===== BAGIAN: TANGGAL BAYAR & KONFIRMASI TANGGAL LAMA ===== --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tanggal Terima Uang Tunai</label>
                    <input
                        type="date"
                        name="tanggal_bayar"
                        id="inputTanggalBayar"
                        data-batas-hari="{{ $bayarLamaHari ?? 30 }}"
                        max="{{ date('Y-m-d') }}"
                        class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tanggal_bayar') is-invalid @enderror"
                        value="{{ old('tanggal_bayar', $tanggalBayarDefault) }}"
                        required
                    >
                    @error('tanggal_bayar')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror

                    <div id="kotakKonfirmasiTanggalLama" class="alert alert-warning rounded-4 p-3 mt-3 {{ $tampilkanKotakTanggalLama ? '' : 'd-none' }}">
                        <div class="fw-semibold fs-6 text-dark mb-2">
                            Tanggal terima uang lebih lama dari {{ $bayarLamaHari ?? 30 }} hari yang lalu. Periksa kembali tanggalnya.
                        </div>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="konfirmasi_tanggal_lama"
                                value="1"
                                id="konfirmasiTanggalLama"
                                class="form-check-input @error('konfirmasi_tanggal_lama') is-invalid @enderror"
                                {{ old('konfirmasi_tanggal_lama') ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-semibold fs-6 text-dark" for="konfirmasiTanggalLama">
                                Saya yakin tanggal terima uang tunai ini sudah benar.
                            </label>
                            @error('konfirmasi_tanggal_lama')<div class="invalid-feedback d-block fs-6">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- ===== BAGIAN: NOMINAL & KONFIRMASI BEDA TARIF ===== --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Jumlah Pembayaran (Rp)</label>
                    <input
                        type="number"
                        name="jumlah"
                        id="inputJumlahBayar"
                        data-tarif-kamar="{{ $tarifKamar }}"
                        class="form-control form-control-lg fs-5 rounded-pill px-4 @error('jumlah') is-invalid @enderror"
                        value="{{ old('jumlah', $jumlahDefault) }}"
                        min="1"
                        step="1"
                        required
                    >
                    @error('jumlah')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror

                    <div id="kotakKonfirmasiNominalBeda" class="alert alert-warning rounded-4 p-3 mt-3 {{ $tampilkanKotakNominalBeda ? '' : 'd-none' }}">
                        <div class="fw-bold fs-6 text-dark mb-2">
                            Nominal berbeda dari tarif (Rp{{ number_format($tarifKamar, 0, ',', '.') }}). Periksa kembali sebelum menyimpan.
                        </div>
                        <div class="form-check mb-3">
                            <input
                                type="checkbox"
                                name="konfirmasi_nominal_beda"
                                value="1"
                                id="konfirmasiNominalBeda"
                                class="form-check-input @error('konfirmasi_nominal_beda') is-invalid @enderror"
                                {{ old('konfirmasi_nominal_beda') ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-semibold fs-6 text-dark" for="konfirmasiNominalBeda">
                                Nominal berbeda dari tarif (Rp{{ number_format($tarifKamar, 0, ',', '.') }}). Saya yakin ini benar.
                            </label>
                            @error('konfirmasi_nominal_beda')<div class="invalid-feedback d-block fs-6">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="form-label fw-semibold fs-6 text-dark">Catatan (Wajib jika nominal berbeda, maks. 200 karakter)</label>
                            <input
                                type="text"
                                name="catatan"
                                maxlength="200"
                                class="form-control fs-5 rounded-pill px-4 @error('catatan') is-invalid @enderror"
                                value="{{ old('catatan') }}"
                                placeholder="Contoh: Potongan / kesepakatan khusus dengan pengelola"
                            >
                            @error('catatan')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- ===== BAGIAN: TOMBOL SIMPAN & BATAL ===== --}}
                <div class="d-flex gap-3">
                    <button type="submit" id="btnSimpanPembayaran" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan</button>
                    <a href="{{ route('pengelola.pembayaran') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formKonfirmasiPembayaran');
        const btnSimpan = document.getElementById('btnSimpanPembayaran');
        const inputJumlah = document.getElementById('inputJumlahBayar');
        const kotakNominal = document.getElementById('kotakKonfirmasiNominalBeda');
        const inputTanggal = document.getElementById('inputTanggalBayar');
        const kotakTanggal = document.getElementById('kotakKonfirmasiTanggalLama');

        function periksaNominal() {
            if (!inputJumlah || !kotakNominal) return;
            const nilai = parseInt(inputJumlah.value, 10);
            const tarif = parseInt(inputJumlah.getAttribute('data-tarif-kamar'), 10);
            if (!isNaN(nilai) && !isNaN(tarif) && nilai !== tarif) {
                kotakNominal.classList.remove('d-none');
            }
        }

        function periksaTanggal() {
            if (!inputTanggal || !kotakTanggal || !inputTanggal.value) return;
            const batasHari = parseInt(inputTanggal.getAttribute('data-batas-hari'), 10) || 30;
            const parts = inputTanggal.value.split('-');
            if (parts.length !== 3) return;
            const dipilih = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            const sekarang = new Date();
            const hariIni = new Date(sekarang.getFullYear(), sekarang.getMonth(), sekarang.getDate());
            const selisihHari = Math.floor((hariIni - dipilih) / (1000 * 60 * 60 * 24));
            if (selisihHari > batasHari) {
                kotakTanggal.classList.remove('d-none');
            }
        }

        if (inputJumlah) {
            periksaNominal();
            inputJumlah.addEventListener('input', periksaNominal);
        }

        if (inputTanggal) {
            periksaTanggal();
            inputTanggal.addEventListener('change', periksaTanggal);
        }

        // DITAMBAHKAN TAHAP 9A: Nonaktifkan tombol simpan setelah sekali klik untuk mencegah submit ganda
        if (form && btnSimpan) {
            form.addEventListener('submit', function () {
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return;
                }
                btnSimpan.disabled = true;
                btnSimpan.textContent = 'Menyimpan...';
            });
        }
    });
</script>
@endsection