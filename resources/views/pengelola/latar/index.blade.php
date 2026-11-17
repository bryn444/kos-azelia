{{--
  NAMA FILE: resources/views/pengelola/latar/index.blade.php
  FUNGSI: Halaman pengelola sederhana (Tahap 6A) untuk mengunggah foto latar belakang,
          mengatur saklar tampil, urutan lapisan, jangkar atas/bawah, serta angka posisi Laptop & HP.
  HALAMAN: Panel Pengelola -> Atur Latar Belakang (`/pengelola/latar-beranda`).
  CARA MENGUBAH:
  - Halaman ini bersifat sementara (akan diganti editor visual ala Canva pada Tahap 6B).
  - Tinggi tombol utama minimal 60px agar mudah ditekan di layar sentuh maupun desktop.
--}}
@extends('layouts.panel.main')

@section('title', 'Atur Latar Belakang')

@section('styles')
<style>
    /* Gaya halaman 6A memakai variabel panel.css yang sudah ada */
    .latar-6a-kartu {
        background: var(--panel-surface, #ffffff);
        border: 1px solid var(--panel-border, #dce3ec);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .latar-6a-tombol {
        min-height: 60px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
    }

    .latar-6a-thumb {
        width: 112px;
        height: 84px;
        object-fit: contain;
        border-radius: 10px;
        border: 1px solid var(--panel-border, #dce3ec);
        background: var(--panel-bg, #f4f6f9);
        padding: 4px;
    }

    .latar-6a-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.65rem;
    }

    @media (max-width: 991.98px) {
        .latar-6a-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endsection

@section('content')
{{-- ===== BAGIAN: HEADER HALAMAN ===== --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1">Atur Latar Belakang Halaman Calon Penghuni</h1>
        <p class="text-muted small mb-0">
            Susun foto elemen latar belakang halaman publik (Tahap 6A).
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a
            href="{{ route('pengelola.lainnya') }}"
            class="btn btn-outline-secondary latar-6a-tombol"
        >
            Kembali ke Lainnya
        </a>
        <a
            href="{{ route('publik.index') }}"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn-primary latar-6a-tombol"
        >
            Lihat Halaman Calon Penghuni
        </a>
    </div>
</div>

{{-- ===== BAGIAN: PESAN NOTIFIKASI & ERROR ===== --}}
@if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" role="alert">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== BAGIAN: FORM UPLOAD GAMBAR ===== --}}
<div class="latar-6a-kartu">
    <h2 class="h6 mb-2">Upload Gambar Latar</h2>
    <p class="text-muted small mb-3">
        Mendukung PNG transparan, WEBP, JPG, atau JPEG (maks. {{ $maksUkuranMb }} MB per file, maksimal {{ $maksFoto }} foto).
        Transparansi gambar dipertahankan apa adanya.
    </p>
    <form
        method="POST"
        action="{{ route('pengelola.latar.unggah') }}"
        enctype="multipart/form-data"
        class="row g-3 align-items-end"
    >
        @csrf
        <div class="col-md-8">
            <label for="input-upload-latar" class="form-label">Pilih File Gambar</label>
            <input
                type="file"
                id="input-upload-latar"
                name="foto[]"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
                multiple
                required
            >
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100 latar-6a-tombol">
                Upload Gambar
            </button>
        </div>
    </form>
</div>

{{-- ===== BAGIAN: FORM PENGATURAN SUSUNAN FOTO ===== --}}
<form
    id="form-simpan-latar"
    method="POST"
    action="{{ route('pengelola.latar.simpan') }}"
>
    @csrf

    <div class="latar-6a-kartu">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 mb-0">Daftar Foto Latar ({{ $daftarFoto->count() }}/{{ $maksFoto }})</h2>
        </div>

        @if ($daftarFoto->isEmpty())
            <div class="alert alert-info mb-0" role="status">
                Belum ada foto latar. Klik Upload Gambar untuk memulai.
            </div>
        @else
            @foreach ($daftarFoto as $idx => $foto)
                <div class="border rounded-3 p-3 mb-3">
                    <input type="hidden" name="foto[{{ $idx }}][id]" value="{{ $foto->id }}">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <img
                                src="{{ $foto->url }}"
                                alt="{{ $foto->nama_asli ?? 'Foto latar' }}"
                                class="latar-6a-thumb"
                            >
                            <div>
                                <div class="fw-semibold">{{ $foto->nama_asli ?? ('Foto #' . $foto->id) }}</div>
                                <div class="text-muted small">
                                    ID #{{ $foto->id }} &bull; Rasio: {{ number_format((float) $foto->rasio, 3) }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="form-check form-switch m-0">
                                <input type="hidden" name="foto[{{ $idx }}][aktif]" value="0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="aktif-foto-{{ $foto->id }}"
                                    name="foto[{{ $idx }}][aktif]"
                                    value="1"
                                    {{ $foto->aktif ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="aktif-foto-{{ $foto->id }}">
                                    Tampilkan
                                </label>
                            </div>

                            <div style="width: 110px;">
                                <label class="form-label small mb-1" for="urutan-foto-{{ $foto->id }}">Urutan</label>
                                <input
                                    type="number"
                                    id="urutan-foto-{{ $foto->id }}"
                                    name="foto[{{ $idx }}][urutan]"
                                    value="{{ (int) $foto->urutan }}"
                                    min="0"
                                    class="form-control form-control-sm"
                                >
                            </div>

                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm btn-hapus-foto"
                                data-hapus-url="{{ route('pengelola.latar.hapus', ['foto' => $foto->id]) }}"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>

                    {{-- Susunan Laptop --}}
                    <div class="mb-3">
                        <div class="small fw-semibold mb-1">
                            Susunan Laptop (X: -50..150 | Y: -50..1500 | Lebar: 3..200 cqw | Rotasi: -180..180°)
                        </div>
                        <div class="latar-6a-grid">
                            <div>
                                <label class="form-label small mb-1">Jangkar</label>
                                <select name="foto[{{ $idx }}][jangkar]" class="form-select form-select-sm" required>
                                    <option value="atas" {{ $foto->jangkar === 'atas' ? 'selected' : '' }}>Atas</option>
                                    <option value="bawah" {{ $foto->jangkar === 'bawah' ? 'selected' : '' }}>Bawah</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label small mb-1">X (cqw)</label>
                                <input type="number" step="0.01" min="-50" max="150" name="foto[{{ $idx }}][lap_x]" value="{{ $foto->lap_x }}" class="form-control form-control-sm" required>
                            </div>
                            <div>
                                <label class="form-label small mb-1">Y (cqw)</label>
                                <input type="number" step="0.01" min="-50" max="1500" name="foto[{{ $idx }}][lap_y]" value="{{ $foto->lap_y }}" class="form-control form-control-sm" required>
                            </div>
                            <div>
                                <label class="form-label small mb-1">Lebar (cqw)</label>
                                <input type="number" step="0.01" min="3" max="200" name="foto[{{ $idx }}][lap_lebar]" value="{{ $foto->lap_lebar }}" class="form-control form-control-sm" required>
                            </div>
                            <div>
                                <label class="form-label small mb-1">Rotasi (°)</label>
                                <input type="number" step="0.01" min="-180" max="180" name="foto[{{ $idx }}][lap_rot]" value="{{ $foto->lap_rot }}" class="form-control form-control-sm" required>
                            </div>
                        </div>
                    </div>

                    {{-- Susunan HP --}}
                    <div>
                        <div class="small fw-semibold mb-1">
                            Susunan HP (kosongkan semua bila ingin ikut Laptop)
                        </div>
                        <div class="latar-6a-grid">
                            <div>
                                <label class="form-label small mb-1">Jangkar HP</label>
                                <select name="foto[{{ $idx }}][hp_jangkar]" class="form-select form-select-sm">
                                    <option value="" {{ $foto->hp_jangkar === null ? 'selected' : '' }}>Ikut laptop</option>
                                    <option value="atas" {{ $foto->hp_jangkar === 'atas' ? 'selected' : '' }}>Atas</option>
                                    <option value="bawah" {{ $foto->hp_jangkar === 'bawah' ? 'selected' : '' }}>Bawah</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label small mb-1">HP X (cqw)</label>
                                <input type="number" step="0.01" min="-50" max="150" name="foto[{{ $idx }}][hp_x]" value="{{ $foto->hp_x }}" placeholder="Ikut laptop" class="form-control form-control-sm">
                            </div>
                            <div>
                                <label class="form-label small mb-1">HP Y (cqw)</label>
                                <input type="number" step="0.01" min="-50" max="1500" name="foto[{{ $idx }}][hp_y]" value="{{ $foto->hp_y }}" placeholder="Ikut laptop" class="form-control form-control-sm">
                            </div>
                            <div>
                                <label class="form-label small mb-1">HP Lebar (cqw)</label>
                                <input type="number" step="0.01" min="3" max="200" name="foto[{{ $idx }}][hp_lebar]" value="{{ $foto->hp_lebar }}" placeholder="Ikut laptop" class="form-control form-control-sm">
                            </div>
                            <div>
                                <label class="form-label small mb-1">HP Rotasi (°)</label>
                                <input type="number" step="0.01" min="-180" max="180" name="foto[{{ $idx }}][hp_rot]" value="{{ $foto->hp_rot }}" placeholder="Ikut laptop" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ===== BAGIAN: TOMBOL AKSI BAWAH ===== --}}
    <div class="latar-6a-kartu">
        <div class="row g-3">
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary w-100 latar-6a-tombol">
                    Terapkan
                </button>
            </div>
            <div class="col-md-6">
                <button
                    type="button"
                    id="btn-reset-latar"
                    class="btn btn-outline-secondary w-100 latar-6a-tombol"
                    data-reset-url="{{ route('pengelola.latar.reset') }}"
                >
                    Kembalikan ke Tampilan Tanpa Latar
                </button>
            </div>
        </div>
    </div>
</form>

{{-- Form POST tersembunyi untuk aksi Hapus dan Reset --}}
<form id="form-aksi-tunggal" method="POST" action="" class="d-none">
    @csrf
</form>
@endsection

@section('scripts')
<script>
    (function () {
        const formSimpan = document.getElementById('form-simpan-latar');
        const btnReset = document.getElementById('btn-reset-latar');
        const formAksi = document.getElementById('form-aksi-tunggal');

        if (formSimpan) {
            formSimpan.addEventListener('submit', function (event) {
                if (!window.confirm('Terapkan perubahan latar belakang ke halaman calon penghuni?')) {
                    event.preventDefault();
                }
            });
        }

        document.querySelectorAll('.btn-hapus-foto').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const url = btn.getAttribute('data-hapus-url');
                if (!url || !formAksi) return;
                if (window.confirm('Hapus foto latar ini secara permanen?')) {
                    formAksi.setAttribute('action', url);
                    formAksi.submit();
                }
            });
        });

        if (btnReset && formAksi) {
            btnReset.addEventListener('click', function () {
                const url = btnReset.getAttribute('data-reset-url');
                if (!url) return;
                if (window.confirm('Kembalikan ke Tampilan Tanpa Latar? Semua foto akan dinonaktifkan (tidak dihapus).')) {
                    formAksi.setAttribute('action', url);
                    formAksi.submit();
                }
            });
        }
    })();
</script>
@endsection