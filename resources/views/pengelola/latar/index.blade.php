@extends('layouts.panel.main')

@section('title', 'Atur Latar Belakang')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/latar-editor.css') }}">
@endsection

@section('content')
<div class="le-editor-pembungkus">

    {{-- ===== BAGIAN: BARIS KONTROL ATAS ===== --}}
    <div class="le-toolbar-atas">
        <div class="le-toolbar-kiri">
            <input type="file" id="le-input-unggah" accept="image/png,image/jpeg,image/webp" multiple hidden>
            <button type="button" id="le-btn-unggah" class="le-tombol-pil">
                <span>+ Upload Gambar</span>
            </button>
        </div>

        <div class="le-toolbar-kanan">
            <div class="le-saklar-perangkat" role="group" aria-label="Pilih pratinjau perangkat">
                <button type="button" class="le-tombol-perangkat aktif" data-perangkat="laptop">Laptop</button>
                <button type="button" class="le-tombol-perangkat" data-perangkat="hp">HP</button>
            </div>

            <button type="button" id="le-btn-urungkan" class="le-tombol-sekunder" disabled title="Ctrl+Z">Urungkan</button>
            <button type="button" id="le-btn-ulangi" class="le-tombol-sekunder" disabled title="Ctrl+Y">Ulangi</button>

            <button type="button" id="le-btn-apply" class="le-tombol-sekunder" title="Kunci perubahan posisi ke pratinjau">Apply</button>
            <button type="button" id="le-btn-terapkan" class="le-tombol-terapkan">Terapkan</button>

            <a href="{{ route('publik.index') }}" target="_blank" rel="noopener noreferrer" class="le-tombol-sekunder">
                Lihat Halaman Calon Penghuni ↗
            </a>
        </div>
    </div>

    {{-- ===== BAGIAN: BANNER STATUS & PETUNJUK SINGKAT ===== --}}
    <div id="le-banner-belum-simpan" class="le-banner-status peringatan">
        Ada perubahan yang belum diterapkan. Klik "Apply" untuk mengunci ke pratinjau, lalu klik "Terapkan" agar tampil di halaman calon penghuni.
    </div>

    <div id="le-banner-pesan" class="le-banner-status" role="status"></div>

    <div class="le-petunjuk-singkat">
        <p>1. Klik foto untuk memilih.</p>
        <p>2. Tarik untuk menggeser, sudut untuk ukuran, tombol bulat di bawah foto untuk memutar.</p>
        <p>3. Klik Apply untuk mengunci posisi di pratinjau, lalu tekan Terapkan.</p>
    </div>

    {{-- ===== BAGIAN: AREA KERJA DUA KOLOM ===== --}}
    <div class="le-area-kerja">

        {{-- KOLOM KIRI: PANGGUNG PRATINJAU --}}
        <div id="le-panggung" class="le-panggung">
            <div id="le-kosong-panggung" class="le-kosong-panggung" hidden>
                Belum ada foto latar. Klik <strong>Upload Gambar</strong> di kiri atas untuk menambahkan hiasan.
            </div>

            <div id="le-wadah-skala" class="le-wadah-skala">
                <iframe
                    id="le-iframe-pratinjau"
                    class="le-iframe-pratinjau"
                    src="{{ route('pengelola.latar.pratinjau') }}"
                    title="Pratinjau halaman calon penghuni"
                    sandbox="allow-same-origin allow-scripts"
                ></iframe>

                <div id="le-overlay-kanvas" class="le-overlay-kanvas" tabindex="0" aria-label="Kanvas editor latar">
                    <div id="le-garis-tengah" class="le-garis-tengah"></div>

                    <div id="le-kotak-terpilih" class="le-kotak-terpilih">
                        <img id="le-gambar-terpilih" class="le-gambar-terpilih" src="" alt="">
                        <div class="le-bingkai-ungu"></div>
                        <div class="le-pegangan-sudut" data-sudut="tl" title="Ubah ukuran"></div>
                        <div class="le-pegangan-sudut" data-sudut="tr" title="Ubah ukuran"></div>
                        <div class="le-pegangan-sudut" data-sudut="bl" title="Ubah ukuran"></div>
                        <div class="le-pegangan-sudut" data-sudut="br" title="Ubah ukuran"></div>
                        <div id="le-pegangan-putar" class="le-pegangan-putar" title="Tahan dan tarik untuk memutar">
                            <div class="le-pegangan-putar-lingkaran">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                                    <path d="M21 3v5h-5"></path>
                                </svg>
                            </div>
                        </div>
                        <div id="le-label-derajat" class="le-label-derajat">0°</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: PANEL EDIT + TABEL GAMBAR TERUPLOAD --}}
        <div class="le-kolom-kanan">
            <aside class="le-panel-edit" aria-label="Panel pengaturan posisi latar">
                <h2 class="le-judul-panel-edit">edit</h2>

                <div class="le-kartu-posisi">
                    <div class="le-header-kartu-posisi">
                        <h3>Posisi</h3>
                        <button type="button" id="le-btn-batal-pilih" class="le-tombol-tutup" title="Batalkan pilihan foto">✕</button>
                    </div>

                    <div class="le-tab-bar" role="tablist">
                        <button type="button" class="le-tab-btn aktif" data-tab="atur" role="tab">Atur</button>
                        <button type="button" class="le-tab-btn" data-tab="lapisan" role="tab">Lapisan</button>
                    </div>

                    {{-- ===== BAGIAN: TAB ATUR ===== --}}
                    <div id="le-tab-atur" class="le-isi-tab aktif">
                        <div class="le-grid-2">
                            <button type="button" class="le-tombol-panel" data-aksi="maju">Maju</button>
                            <button type="button" class="le-tombol-panel" data-aksi="mundur">Mundur</button>
                            <button type="button" class="le-tombol-panel" data-aksi="paling-depan">Ke paling depan</button>
                            <button type="button" class="le-tombol-panel" data-aksi="paling-belakang">Ke paling belakang</button>
                        </div>

                        <div class="le-subjudul-panel">Ukuran dan Putaran</div>
                        <div class="le-grid-2">
                            <button type="button" class="le-tombol-panel" data-aksi="perkecil">Perkecil</button>
                            <button type="button" class="le-tombol-panel" data-aksi="perbesar">Perbesar</button>
                            <button type="button" class="le-tombol-panel" data-aksi="putar-kiri">Putar Kiri</button>
                            <button type="button" class="le-tombol-panel" data-aksi="putar-kanan">Putar Kanan</button>
                            <button type="button" class="le-tombol-panel" data-aksi="luruskan">Luruskan</button>
                            <button type="button" class="le-tombol-panel" data-aksi="pusatkan">Pusatkan</button>
                        </div>

                        <div class="le-subjudul-panel">Posisi pada halaman</div>
                        <div class="le-grid-2">
                            <button type="button" class="le-tombol-panel" data-aksi="jangkar-atas">Tempel ke atas halaman</button>
                            <button type="button" class="le-tombol-panel" data-aksi="jangkar-bawah">Tempel ke bawah halaman</button>
                            <button type="button" id="le-btn-tampil-sembunyi" class="le-tombol-panel" data-aksi="toggle-aktif">Sembunyikan</button>
                            <button type="button" class="le-tombol-panel bahaya" data-aksi="hapus">Hapus</button>
                        </div>

                        <div id="le-kontrol-khusus-hp" hidden>
                            <div class="le-subjudul-panel">Pengaturan Khusus HP</div>
                            <div class="le-grid-2">
                                <button type="button" class="le-tombol-panel" data-aksi="salin-laptop">Salin dari Laptop</button>
                                <button type="button" class="le-tombol-panel" data-aksi="hp-ikut-laptop">HP Ikut Laptop</button>
                            </div>
                            <div id="le-status-hp" class="le-badge-mode-hp">HP: ikut laptop</div>
                        </div>

                        <div class="le-info-angka" aria-label="Koordinat foto terpilih">
                            <div><span>X</span><strong id="le-val-x">-</strong></div>
                            <div><span>Y</span><strong id="le-val-y">-</strong></div>
                            <div><span>Lebar</span><strong id="le-val-lebar">-</strong></div>
                            <div><span>Rotasi</span><strong id="le-val-rotasi">-</strong></div>
                        </div>
                    </div>

                    {{-- ===== BAGIAN: TAB LAPISAN ===== --}}
                    <div id="le-tab-lapisan" class="le-isi-tab">
                        <div id="le-daftar-lapisan" class="le-daftar-lapisan"></div>
                    </div>

                    {{-- ===== BAGIAN: TOMBOL BATAL & RESET BAWAH ===== --}}
                    <div class="le-footer-panel">
                        <button type="button" id="le-btn-batalkan-perubahan" class="le-tombol-panel">
                            Batalkan Perubahan
                        </button>
                        <button type="button" id="le-btn-reset-total" class="le-tombol-panel bahaya">
                            Kembalikan ke Tampilan Tanpa Latar
                        </button>
                    </div>
                </div>
            </aside>

            {{-- ===== BAGIAN: TABEL GAMBAR YANG TELAH TERUPLOAD ===== --}}
            <section class="le-kartu-galeri" aria-label="Daftar gambar yang telah terupload">
                <div class="le-header-galeri">
                    <h3>Gambar Terupload</h3>
                    <span id="le-jumlah-gambar" class="le-badge-jumlah">0</span>
                </div>

                <div class="le-wadah-tabel-gambar">
                    <table class="le-tabel-gambar">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama & Posisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="le-tbody-gambar">
                            <tr>
                                <td colspan="3" style="text-align:center; color:#64748b; padding:14px;">
                                    Belum ada gambar yang diupload.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

{{-- Data awal dan URL endpoint untuk JavaScript --}}
@php
    $_leDataAwal = [
        'foto' => $dataAwalEditor ?? [],
        'maksFoto' => $maksFoto ?? 15,
        'maksUkuranMb' => $maksUkuranMb ?? 3,
        'urlUnggah' => route('pengelola.latar.unggah'),
        'urlSimpan' => route('pengelola.latar.simpan'),
        'urlReset' => route('pengelola.latar.reset'),
        'urlHapusTemplate' => route('pengelola.latar.hapus', ['foto' => '__ID__']),
        'csrfToken' => csrf_token(),
    ];
@endphp
<script id="le-data-awal" type="application/json">{!! json_encode($_leDataAwal) !!}</script>
@endsection

@section('scripts')
    <script src="{{ asset('js/latar-editor.js') }}"></script>
@endsection