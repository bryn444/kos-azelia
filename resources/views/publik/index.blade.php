{{-- 
  NAMA FILE: resources/views/publik/index.blade.php
  FUNGSI: Halaman utama beranda publik dengan hero kamar unggulan dan slider foto kamar, fasilitas, dan informasi kos.
  HALAMAN: Halaman Utama (/) dan Pratinjau Editor Pengelola (/pengelola/latar-beranda/pratinjau).
  CARA MENGUBAH: Sesuaikan urutan seksi atau teks pada file ini.
--}}
@extends('layouts.publik')

@section('title', 'Kos Azelia - Beranda Kos Putri')

@section('content')
@php
    $daftarLatarPublik = $latarUtama ?? [];
@endphp

{{-- ===== BAGIAN: 1. KANVAS LATAR BELAKANG PUBLIK ===== --}}
<div id="latar-kanvas" class="latar-kanvas" aria-hidden="true">
    @foreach($daftarLatarPublik as $lf)
        @php
            $lap = $lf['laptop'] ?? ['jangkar' => 'atas', 'x' => 50, 'y' => 40, 'lebar' => 30, 'rot' => 0];
            $hp = $lf['hp'] ?? $lap;
            $kelasJangkarLap = ($lap['jangkar'] ?? 'atas') === 'bawah' ? 'lap-bawah' : 'lap-atas';
            $kelasJangkarHp = ($hp['jangkar'] ?? $lap['jangkar'] ?? 'atas') === 'bawah' ? 'hp-bawah' : 'hp-atas';
            $styleLatar = sprintf(
                '--lap-x:%s;--lap-y:%s;--lap-lebar:%s;--lap-rot:%s;--hp-x:%s;--hp-y:%s;--hp-lebar:%s;--hp-rot:%s;z-index:%d;',
                $lap['x'],
                $lap['y'],
                $lap['lebar'],
                $lap['rot'],
                $hp['x'],
                $hp['y'],
                $hp['lebar'],
                $hp['rot'],
                (int) ($lf['urutan'] ?? 1)
            );
        @endphp
        <img
            src="{{ $lf['url'] }}"
            alt=""
            class="latar-foto {{ $kelasJangkarLap }} {{ $kelasJangkarHp }}"
            data-foto-id="{{ $lf['id'] }}"
            style="{{ $styleLatar }}"
        >
    @endforeach
</div>

<div class="container my-4" data-mode-pratinjau="{{ !empty($modePratinjau) ? '1' : '0' }}">

    {{-- ===== BAGIAN: 2. HERO KAMAR UNGGULAN & SLIDER FOTO KAMAR ===== --}}
    {{-- DITAMBAHKAN TAHAP 7: Hero menampilkan satu kamar unggulan ($kamarHero) dan menggeser foto-fotonya ($fotoHero) --}}
    <div class="blok-hero position-relative p-4 p-md-5 mb-5" id="heroSliderContainer">
        @if($kamarHero && !empty($fotoHero))
            @php
                $totalFotoHero = count($fotoHero);
            @endphp
            <div class="row align-items-center g-4">
                {{-- ===== BAGIAN: 2A. TEKS & AKSI KAMAR UNGGULAN (TETAP) ===== --}}
                <div class="col-lg-6 z-1 py-md-4">
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill mb-3 fw-bold shadow-sm">KAMAR UNGGULAN</span>
                    <h1 class="display-5 fw-bold hero-judul-kamar mb-3">Kamar {{ $kamarHero->nomor_kamar }}</h1>
                    <p class="hero-deskripsi-kamar fs-5 mb-4">{{ $kamarHero->deskripsi ?? 'Kamar putri eksklusif dengan fasilitas lengkap, bersih, dan nyaman.' }}</p>
                    <div class="fs-4 fw-bold hero-harga-kamar mb-4">Rp{{ number_format($kamarHero->tarif, 0, ',', '.') }} / bulan</div>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="https://wa.me/{{ config('kos.whatsapp') }}?text={{ urlencode('Halo, saya ingin menanyakan ketersediaan Kamar ' . $kamarHero->nomor_kamar . '.') }}" target="_blank" class="btn-pil-biru">Tanya Ketersediaan Kamar</a>
                        <a href="{{ route('publik.kamar.show', $kamarHero->id) }}" class="btn btn-light rounded-circle p-3 d-flex align-items-center justify-content-center shadow hero-tombol-detail" aria-label="Detail Kamar {{ $kamarHero->nomor_kamar }}">→</a>
                    </div>
                </div>

                {{-- ===== BAGIAN: 2B. SLIDER FOTO KAMAR UNGGULAN ===== --}}
                <div class="col-lg-6 position-relative">
                    <div
                        id="heroFotoSlider"
                        class="hero-slider-bingkai position-relative shadow-sm"
                        tabindex="0"
                        role="region"
                        aria-label="Galeri foto Kamar {{ $kamarHero->nomor_kamar }}"
                    >
                        @foreach($fotoHero as $idx => $itemFoto)
                            <img
                                src="{{ $itemFoto['url'] }}"
                                alt="{{ $itemFoto['alt'] }}"
                                loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                                class="hero-foto-slide {{ $idx === 0 ? 'aktif' : '' }}"
                                data-foto-index="{{ $idx }}"
                            >
                        @endforeach

                        <div class="hero-foto-gradasi"></div>

                        @if($totalFotoHero > 1)
                            <button type="button" id="heroFotoPrev" class="hero-foto-panah hero-foto-panah-kiri" aria-label="Foto sebelumnya">←</button>
                            <button type="button" id="heroFotoNext" class="hero-foto-panah hero-foto-panah-kanan" aria-label="Foto berikutnya">→</button>

                            <div class="hero-foto-hud">
                                <span id="heroFotoPenanda" class="hero-foto-penanda" aria-live="polite">01 / {{ str_pad($totalFotoHero, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="hero-foto-titik" role="tablist" aria-label="Pilih foto kamar">
                                    @foreach($fotoHero as $idx => $itemFoto)
                                        <button
                                            type="button"
                                            class="hero-foto-titik-btn {{ $idx === 0 ? 'aktif' : '' }}"
                                            data-foto-titik="{{ $idx }}"
                                            aria-label="Tampilkan foto {{ $idx + 1 }}"
                                        ></button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            {{-- ===== BAGIAN: 2C. TAMPILAN BAWAAN HERO TANPA KAMAR UNGGULAN BERFOTO ===== --}}
            <div class="row align-items-center g-4">
                <div class="col-lg-7 py-md-4">
                    <h1 class="display-5 fw-bold text-dark mb-3">Tempat tinggal yang terasa seperti rumah</h1>
                    <p class="text-muted fs-5 mb-4">Kos putri eksklusif, aman, dan strategis dengan fasilitas lengkap di Kos Azelia.</p>
                    <a href="https://wa.me/{{ config('kos.whatsapp') }}?text={{ urlencode('Halo, saya ingin menanyakan ketersediaan kamar.') }}" target="_blank" class="btn-pil-biru">Tanya Ketersediaan Kamar</a>
                </div>
                <div class="col-lg-5">
                    <img src="{{ asset('img/kamar-placeholder.svg') }}" alt="Placeholder Kamar" class="w-100 rounded-4 shadow-sm">
                </div>
            </div>
        @endif
    </div>

    {{-- ===== BAGIAN: 3. BARIS FASILITAS CHIP ===== --}}
    <section class="row g-3 mb-5 justify-content-center" id="tentang">
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-4 text-center border">
                <div class="fw-bold fs-5 mb-1">⚡ Listrik</div>
                <small class="text-muted">Sudah termasuk</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-4 text-center border">
                <div class="fw-bold fs-5 mb-1">💧 Air</div>
                <small class="text-muted">Sudah termasuk</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-4 text-center border">
                <div class="fw-bold fs-5 mb-1">📶 WiFi</div>
                <small class="text-muted">Sudah termasuk</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-4 text-center border">
                <div class="fw-bold fs-5 mb-1">♀ Khusus Putri</div>
                <small class="text-muted">Aman & Tenang</small>
            </div>
        </div>
    </section>

    <!-- {{-- ===== BAGIAN: 4. KAMAR LAINNYA (SLIDER HORIZONTAL) ===== --}}
    <section class="mb-5 py-4" id="kamar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Kamar Lainnya</h2>
                <p class="text-muted mb-0">Pilih kamar yang paling cocok untukmu</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="scrollKiri" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center hero-tombol-geser" aria-label="Geser Kiri">←</button>
                <button type="button" id="scrollKanan" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center hero-tombol-geser" aria-label="Geser Kanan">→</button>
            </div>
        </div>

        <div class="slider-horizontal pb-3" id="containerKamarLainnya">
            @foreach($kamarLainnyas as $kl)
                <div class="slider-item">
                    <a href="{{ route('publik.kamar.show', $kl->id) }}" class="text-decoration-none text-dark">
                        <div class="kartu-kamar h-100">
                            <div class="kartu-kamar-media position-relative">
                                <img src="{{ $kl->sampul_url }}" alt="Kamar {{ $kl->nomor_kamar }}" class="w-100 h-100 object-fit-cover">
                                @if($kl->status === 'terisi')
                                    <span class="badge bg-secondary position-absolute top-0 end-0 m-3 px-3 py-2">Terisi</span>
                                @else
                                    <span class="badge bg-primary position-absolute top-0 end-0 m-3 px-3 py-2">Tersedia</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="fw-bold fs-5 mb-2">Kamar {{ $kl->nomor_kamar }}</h3>
                                <p class="text-muted small mb-3 text-truncate">{{ $kl->deskripsi ?? 'Kamar nyaman siap huni.' }}</p>
                                <div class="fw-bold text-primary">Rp{{ number_format($kl->tarif, 0, ',', '.') }} / bulan</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section> -->

    {{-- ===== BAGIAN: 5. CARA MENGEKOS JADI MUDAH ===== --}}
    <section class="my-5 py-5 bg-light rounded-4 p-4 p-md-5" id="cara">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Cara Mengekos Jadi Mudah!</h2>
            <p class="text-muted">Tiga langkah sederhana untuk mulai tinggal di Kos Azelia.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 text-center border">
                    <div class="fs-1 fw-bold text-primary mb-3">1</div>
                    <h3 class="fw-bold fs-4 mb-2">Lihat Kamar</h3>
                    <p class="text-muted mb-0">Pilih nomor kamar yang tersedia dan cek fasilitas lengkapnya secara online.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-white p-4 rounded-4 shadow h-100 text-center langkah-utama-card">
                    <div class="fs-1 fw-bold mb-3">2</div>
                    <h3 class="fw-bold fs-4 mb-2 text-white">Hubungi Pengelola</h3>
                    <p class="text-white-50 mb-4">Konsultasikan jadwal survei atau ketersediaan kamar via WhatsApp.</p>
                    <a href="https://wa.me/{{ config('kos.whatsapp') }}" target="_blank" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-2">Chat WhatsApp</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 text-center border">
                    <div class="fs-1 fw-bold text-primary mb-3">3</div>
                    <h3 class="fw-bold fs-4 mb-2">Survei & Bayar Tunai</h3>
                    <p class="text-muted mb-0">Datang langsung untuk survei lokasi dan lakukan pembayaran sewa secara tunai kepada pengelola.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BAGIAN: 6. INFORMASI KOS DARI DATABASE ===== --}}
    <section class="my-5 py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Informasi & Peraturan Kos</h2>
            <p class="text-muted">Transparansi aturan demi kenyamanan bersama.</p>
        </div>
        <div class="row g-4">
            @forelse($informasis as $info)
                @php $isAturan = str_contains(strtolower($info->kategori), 'aturan'); @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 rounded-4 h-100 d-flex flex-column {{ $isAturan ? 'text-white kartu-aturan-kos' : 'border bg-light' }}">
                        <span class="badge {{ $isAturan ? 'bg-white text-dark' : 'bg-primary text-white' }} align-self-start mb-3 px-3 py-2 rounded-pill">{{ strtoupper($info->kategori) }}</span>
                        <h3 class="fw-bold fs-5 mb-2 {{ $isAturan ? 'text-white' : '' }}">{{ $info->judul }}</h3>
                        <p class="{{ $isAturan ? 'text-white-50' : 'text-muted' }} small mb-0 mt-auto">{{ $info->deskripsi }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">Belum ada informasi tambahan.</div>
            @endforelse
        </div>
    </section>

    {{-- ===== BAGIAN: 7. BLOK AJAKAN AKHIR (CTA) ===== --}}
    <section class="my-5 py-5 bg-white border rounded-4 p-4 p-md-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="cta-media-bingkai">
                    <img src="{{ $kamarHero?->sampul_url ?? asset('img/kamar-placeholder.svg') }}" alt="Kamar Kos Azelia" class="w-100 h-100 object-fit-cover">
                </div>
            </div>
            <div class="col-lg-7">
                <h2 class="display-6 fw-bold mb-3">TEMPAT PULANG YANG NYAMAN</h2>
                <p class="text-muted mb-3">Nikmati fasilitas lengkap dengan lingkungan yang tenang, bersih, dan eksklusif khusus putri di Kos Azelia.</p>
                <p class="text-muted mb-4">Lokasi sangat strategis dekat kampus dan pusat perbelanjaan dengan akses transportasi mudah.</p>
                
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 p-3 bg-light rounded-4 border">
                    <div class="fs-5 fw-bold">Mulai Rp{{ number_format($tarifTermurah, 0, ',', '.') }} / bulan</div>
                    <a href="https://wa.me/{{ config('kos.whatsapp') }}?text={{ urlencode('Halo, saya tertarik dengan kamar di Kos Azelia.') }}" target="_blank" class="btn-pil-biru ms-sm-auto">Tanya Ketersediaan Kamar →</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@section('scripts')
<script src="{{ asset('js/publik.js') }}"></script>
@if(!empty($modePratinjau))
<script src="{{ asset('js/latar-kanvas.js') }}"></script>
@endif
@endsection