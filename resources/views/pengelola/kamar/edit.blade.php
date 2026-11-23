@extends('layouts.panel.main')

{{-- 
  NAMA FILE: edit.blade.php (Modul Kamar - DITAMBAHKAN TAHAP 5A & TAHAP 8)
  FUNGSI: Form mengubah data kamar, mengelola galeri foto eksisting, menetapkan sampul, menghapus foto, dan zona berbahaya hapus kamar.
  CARA MENGUBAH: Ubah tata letak grid galeri foto, atribut form, atau blok Zona Berbahaya di bawah ini.
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
                    <input type="text" name="nomor_kamar" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('nomor_kamar', $kamar->nomor_kamar) }}" required>
                </div>

                {{-- Tarif Sewa --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tarif Sewa Per Bulan (Rp)</label>
                    <input type="number" name="tarif" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('tarif', $kamar->tarif) }}" required>
                </div>

                {{-- Status Kamar --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Status Kamar</label>
                    <select name="status" class="form-select form-select-lg fs-5 rounded-pill px-4">
                        <option value="kosong" {{ $kamar->status === 'kosong' ? 'selected' : '' }}>Kosong</option>
                        <option value="terisi" {{ $kamar->status === 'terisi' ? 'selected' : '' }}>Terisi</option>
                    </select>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Deskripsi Singkat Kamar --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Deskripsi Singkat Kamar (Opsional, Maks 300 karakter)</label>
                    <textarea name="deskripsi" class="form-control fs-5 rounded-4 p-3" rows="3" maxlength="300">{{ old('deskripsi', $kamar->deskripsi) }}</textarea>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Saklar Kamar Unggulan --}}
                <div class="mb-4 form-check form-switch p-3 bg-light rounded-4">
                    <input type="checkbox" name="unggulan" value="1" class="form-check-input ms-0 me-3" id="unggulanSwitch" style="width: 3em; height: 1.5em;" {{ old('unggulan', $kamar->unggulan) ? 'checked' : '' }}>
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
                                    <img src="{{ $foto->url }}" class="rounded-3 object-fit-cover w-100" style="height: 140px;">
                                    
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
            {{-- DITAMBAHKAN TAHAP 8: Blok terpisah di bawah form ubah untuk masuk ke konfirmasi hapus kamar --}}
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
                                    <img src="${e.target.result}" class="rounded-3 object-fit-cover" style="height: 120px; width: 100%;">
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