@extends('layouts.panel.main')

{{-- 
  NAMA FILE: edit.blade.php (Modul Kamar - DITAMBAHKAN TAHAP 5A, TAHAP 8 & TAHAP 9A)
  FUNGSI: Form ubah kamar dengan status otomatis, pratinjau & konfirmasi tarif, galeri foto, dan zona berbahaya.
  CARA MENGUBAH: Sesuaikan batas tarif dan perubahan tarif pada config/kos.php bagian 'aturan'.
--}}

@section('title', 'Ubah Kamar - Kos Azelia')

@section('content')
@php
    // DITAMBAHKAN TAHAP 8: Cek apakah kamar pernah/sedang dihuni atau berstatus terisi untuk status tombol hapus
    $punyaPenghuni = $kamar->penghuni()->exists();
    $tidakBisaDihapus = $punyaPenghuni || $kamar->status === 'terisi';
    $alasanNonaktif = $punyaPenghuni
        ? "Kamar {$kamar->nomor_kamar} tidak bisa dihapus karena pernah atau sedang dihuni (riwayat pembayaran harus tetap tersimpan)."
        : ($kamar->status === 'terisi'
            ? "Kamar {$kamar->nomor_kamar} tidak bisa dihapus karena statusnya sedang terisi."
            : null);

    // DITAMBAHKAN TAHAP 9A: Hitung apakah tarif input saat ini berbeda > batas persen dari tarif lama
    $tarifLama = (int) $kamar->tarif;
    $tarifInputSekarang = (int) old('tarif', $tarifLama);
    $persenBedaSekarang = ($tarifLama > 0) ? (abs($tarifInputSekarang - $tarifLama) / $tarifLama) * 100 : 0;
    $perluCentangTarif = ($persenBedaSekarang > ($tarifUbahPersen ?? 30)) || session()->has('perlu_konfirmasi_tarif') || $errors->has('yakin_tarif_berubah');
    $teksPeringatanTarif = session('perlu_konfirmasi_tarif')
        ?? $errors->first('yakin_tarif_berubah')
        ?? ('Tarif berubah dari Rp' . number_format($tarifLama, 0, ',', '.') . ' menjadi Rp' . number_format($tarifInputSekarang, 0, ',', '.') . '. Periksa kembali.');
@endphp

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Ubah Data Kamar {{ $kamar->nomor_kamar }}</h2>

            <form action="{{ route('pengelola.kamar.update', $kamar->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Nomor Kamar --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nomor Kamar</label>
                    <input type="text" name="nomor_kamar" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('nomor_kamar') is-invalid @enderror" value="{{ old('nomor_kamar', $kamar->nomor_kamar) }}" required>
                    @error('nomor_kamar')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                {{-- Tarif Sewa, Pratinjau Rupiah & Konfirmasi Perubahan Besar --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tarif Sewa Per Bulan (Rp)</label>
                    <input
                        type="number"
                        name="tarif"
                        id="inputTarifEdit"
                        data-tarif-lama="{{ $tarifLama }}"
                        data-batas-persen="{{ $tarifUbahPersen ?? 30 }}"
                        class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tarif') is-invalid @enderror"
                        value="{{ old('tarif', $kamar->tarif) }}"
                        min="{{ $tarifMin ?? 100000 }}"
                        max="{{ $tarifMaks ?? 5000000 }}"
                        step="1"
                        required
                    >
                    <small class="text-muted d-block mt-2 fs-6">
                        Pratinjau Tarif: <strong id="previewRupiahEdit" class="text-primary">Rp0</strong>
                        (Rentang: Rp{{ number_format($tarifMin ?? 100000, 0, ',', '.') }} - Rp{{ number_format($tarifMaks ?? 5000000, 0, ',', '.') }})
                    </small>
                    @error('tarif')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror

                    {{-- DITAMBAHKAN TAHAP 9A: Kotak konfirmasi ekstra bila perubahan tarif melebihi batas persen --}}
                    <div id="kotakKonfirmasiTarif" class="alert alert-warning rounded-4 p-3 mt-3 {{ $perluCentangTarif ? '' : 'd-none' }}">
                        <div class="fw-bold fs-6 text-dark mb-2" id="teksPeringatanTarif">{{ $teksPeringatanTarif }}</div>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="yakin_tarif_berubah"
                                value="1"
                                id="yakinTarifBerubah"
                                class="form-check-input @error('yakin_tarif_berubah') is-invalid @enderror"
                                {{ old('yakin_tarif_berubah') ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-semibold fs-6 text-dark" for="yakinTarifBerubah">
                                Saya yakin tarif ini benar
                            </label>
                            @error('yakin_tarif_berubah')<div class="invalid-feedback d-block fs-6">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- DITAMBAHKAN TAHAP 9A: Status kamar hanya ditampilkan sebagai teks (tidak bisa diubah manual) --}}
                <div class="mb-4 p-3 bg-light rounded-4 border">
                    <div class="fs-5 fw-semibold">
                        Status Kamar Saat Ini:
                        @if($kamar->status === 'terisi')
                            <span class="badge bg-danger fs-6">Terisi</span>
                        @else
                            <span class="badge bg-success fs-6">Kosong</span>
                        @endif
                    </div>
                    <small class="text-muted d-block mt-1 fs-6">Status berubah otomatis saat penghuni masuk atau keluar</small>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Deskripsi Singkat Kamar --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Deskripsi Singkat Kamar (Opsional, Maks 300 karakter)</label>
                    <textarea name="deskripsi" class="form-control fs-5 rounded-4 p-3 @error('deskripsi') is-invalid @enderror" rows="3" maxlength="300">{{ old('deskripsi', $kamar->deskripsi) }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Saklar Kamar Unggulan --}}
                <div class="mb-4 form-check form-switch p-3 bg-light rounded-4">
                    <input type="checkbox" name="unggulan" value="1" class="form-check-input ms-0 me-3" id="unggulanSwitch" {{ old('unggulan', $kamar->unggulan) ? 'checked' : '' }}>
                    <label class="form-check-label fs-5 fw-semibold pt-1" for="unggulanSwitch">
                        ⭐ Jadikan Kamar Unggulan <span class="d-block text-muted fs-6">Tampil di bagian paling atas halaman calon penghuni.</span>
                    </label>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Galeri Foto Eksisting --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold mb-3">Galeri Foto Saat Ini ({{ $kamar->fotos->count() }} / 6)</label>
                    <div class="row g-3">
                        @forelse($kamar->fotos as $foto)
                            <div class="col-md-4 col-sm-6">
                                <div class="card border shadow-sm rounded-4 p-2 position-relative">
                                    <img src="{{ $foto->url }}" class="rounded-3 object-fit-cover w-100">
                                    
                                    @if($foto->is_sampul)
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-3 shadow-sm">Sampul Utama</span>
                                    @endif

                                    <div class="d-flex gap-2 mt-2">
                                        @if(!$foto->is_sampul)
                                            <button type="submit" form="formSampul{{ $foto->id }}" class="btn btn-sm btn-outline-primary flex-fill rounded-pill">Jadikan Sampul</button>
                                        @endif
                                        <button type="submit" form="formHapusFoto{{ $foto->id }}" class="btn btn-sm btn-outline-danger flex-fill rounded-pill" onclick="return confirm('Hapus foto ini?')">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><p class="text-muted italic">Belum ada foto untuk kamar ini.</p></div>
                        @endforelse
                    </div>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Tambah Foto Baru --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tambah Foto Baru (Opsional)</label>
                    <input type="file" name="fotos[]" id="inputFoto" class="form-control form-control-lg fs-5 rounded-pill px-4" accept="image/jpeg,image/png,image/jpg,image/webp" multiple>
                    <small class="text-muted d-block mt-2">Anda dapat menambahkan beberapa foto baru sekaligus.</small>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Pratinjau Foto Baru --}}
                <div id="previewContainer" class="row g-3 mb-4 d-none">
                    <div class="col-12"><span class="fw-semibold text-muted">Pratinjau Foto Tambahan:</span></div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan Perubahan</button>
                    <a href="{{ route('pengelola.kamar') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>

            {{-- Form terpisah di luar form utama untuk aksi hapus dan jadikan sampul foto --}}
            @foreach($kamar->fotos as $foto)
                <form id="formHapusFoto{{ $foto->id }}" action="{{ route('pengelola.kamar.foto.hapus', [$kamar->id, $foto->id]) }}" method="POST" class="d-none">@csrf</form>
                <form id="formSampul{{ $foto->id }}" action="{{ route('pengelola.kamar.foto.sampul', [$kamar->id, $foto->id]) }}" method="POST" class="d-none">@csrf</form>
            @endforeach

            {{-- ===== BAGIAN: ZONA BERBAHAYA HAPUS KAMAR (TAHAP 8) ===== --}}
            <hr class="my-5">
            <div class="border border-danger rounded-4 p-4 bg-light">
                <h3 class="h4 fw-bold text-danger mb-2">Zona Berbahaya</h3>
                <p class="text-muted fs-5 mb-4">
                    Menghapus kamar akan menghapus data kamar beserta seluruh fotonya secara permanen.
                </p>

                @if($tidakBisaDihapus)
                    <button type="button" class="btn btn-outline-danger btn-lg rounded-pill px-4 py-3 fw-bold fs-5 disabled" aria-disabled="true" disabled>
                        Hapus Kamar Ini
                    </button>
                    <div class="text-danger fw-semibold fs-6 mt-3">
                        {{ $alasanNonaktif }}
                    </div>
                @else
                    <a href="{{ route('pengelola.kamar.hapus', $kamar->id) }}" class="btn btn-outline-danger btn-lg rounded-pill px-4 py-3 fw-bold fs-5 d-inline-flex align-items-center justify-content-center">
                        Hapus Kamar Ini
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputTarif = document.getElementById('inputTarifEdit');
        const previewRupiah = document.getElementById('previewRupiahEdit');
        const kotakKonfirmasi = document.getElementById('kotakKonfirmasiTarif');
        const teksPeringatan = document.getElementById('teksPeringatanTarif');

        function formatRupiah(nilai) {
            const angka = parseInt(nilai, 10);
            if (isNaN(angka)) return 'Rp0';
            return 'Rp' + angka.toLocaleString('id-ID');
        }

        function evaluasiTarif() {
            if (!inputTarif) return;
            const nilaiBaru = parseInt(inputTarif.value, 10);
            const tarifLama = parseInt(inputTarif.getAttribute('data-tarif-lama'), 10);
            const batasPersen = parseFloat(inputTarif.getAttribute('data-batas-persen'));

            if (previewRupiah) {
                previewRupiah.textContent = formatRupiah(inputTarif.value);
            }

            if (kotakKonfirmasi && !isNaN(nilaiBaru) && tarifLama > 0 && nilaiBaru !== tarifLama) {
                const selisihPersen = (Math.abs(nilaiBaru - tarifLama) / tarifLama) * 100;
                if (selisihPersen > batasPersen) {
                    kotakKonfirmasi.classList.remove('d-none');
                    if (teksPeringatan) {
                        teksPeringatan.textContent = 'Tarif berubah dari ' + formatRupiah(tarifLama) + ' menjadi ' + formatRupiah(nilaiBaru) + '. Periksa kembali.';
                    }
                }
            }
        }

        if (inputTarif) {
            evaluasiTarif();
            inputTarif.addEventListener('input', evaluasiTarif);
        }

        const inputFoto = document.getElementById('inputFoto');
        const previewContainer = document.getElementById('previewContainer');

        if (inputFoto) {
            inputFoto.addEventListener('change', function (e) {
                const existingPreviews = previewContainer.querySelectorAll('.preview-item');
                existingPreviews.forEach(el => el.remove());

                if (this.files && this.files.length > 0) {
                    previewContainer.classList.remove('d-none');
                    
                    Array.from(this.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-4 col-sm-6 preview-item';
                            col.innerHTML = `
                                <div class="card border shadow-sm rounded-4 p-2 text-center">
                                    <img src="${e.target.result}" class="rounded-3 object-fit-cover">
                                    <span class="small text-muted mt-1">Foto Baru ${index + 1}</span>
                                </div>
                            `;
                            previewContainer.appendChild(col);
                        }
                        reader.readAsDataURL(file);
                    });
                } else {
                    previewContainer.classList.add('d-none');
                }
            });
        }
    });
</script>
@endsection