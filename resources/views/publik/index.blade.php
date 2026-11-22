{{-- 
  NAMA FILE: resources/views/publik/index.blade.php
  FUNGSI: Halaman utama beranda publik dengan slider hero unggulan, fasilitas, dan informasi kos.
  HALAMAN: Halaman Utama (/) dan Pratinjau Editor Pengelola (/pengelola/latar-beranda/pratinjau).
  CARA MENGUBAH: Sesuaikan urutan seksi atau teks pada file ini.
--}}
@extends('layouts.publik')

@section('title', 'Kos Azelia - Beranda Kos Putri')

@section('content')
@php
    $daftarLatarPublik = $latarUtama ?? [];
@endphp

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
    
    {{-- ===== BAGIAN: 2. HERO BERGAYA REFERENSI DENGAN SLIDER UNGGULAN ===== --}}
    <div class="blok-hero position-relative p-4 p-md-5 mb-5" id="heroSliderContainer">
        @if($kamarUnggulans->isNotEmpty())
            @foreach($kamarUnggulans as $index => $ku)
                <div class="hero-slide row align-items-center g-4 {{ $index > 0 ? 'd-none' : '' }}" data-slide-index="{{ $index }}">
                    <div class="col-lg-6 z-1 py-md-4">
                        <span class="badge bg-white text-dark px-3 py-2 rounded-pill mb-3 fw-bold shadow-sm">KAMAR UNGGULAN</span>
                   <!-- <h1 class="display-5 fw-bold text-white mb-3" style="text-shadow: 0 2px 4px rgba(9, 9, 9, 0.3);">Kamar {{ $ku->nomor_kamar }}</h1> -->
                        <h1 class="display-5 fw-bold mb-3" style="color: #6c9fe1;" >Kamar {{ $ku->nomor_kamar }}</h1>
                        <p class="text-white-50 fs-5 mb-4" style="text-shadow: 0 1px 2px rgb(0, 0, 0);">{{ $ku->deskripsi ?? 'Kamar putri eksklusif dengan fasilitas lengkap, bersih, dan nyaman.' }}</p>
                        <div class="fs-4 fw-bold text-white mb-4">Rp{{ number_format($ku->tarif, 0, ',', '.') }} / bulan</div>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="https://wa.me/{{ config('kos.whatsapp') }}?text={{ urlencode('Halo, saya ingin menanyakan ketersediaan Kamar ' . $ku->nomor_kamar . '.') }}" target="_blank" class="btn-pil-biru">Tanya Ketersediaan Kamar</a>
                            <a href="{{ route('publik.kamar.show', $ku->id) }}" class="btn btn-light rounded-circle p-3 d-flex align-items-center justify-content-center shadow" style="width: 52px; height: 52px;" aria-label="Detail Kamar {{ $ku->nomor_kamar }}">→</a>
                        </div>
                    </div>
                    <div class="col-lg-6 position-relative">
                        <div style="height: 380px; border-radius: 24px; overflow: hidden;" class="shadow-sm position-relative">
                            <img src="{{ $ku->sampul_url }}" alt="Kamar {{ $ku->nomor_kamar }}" class="w-100 h-100 object-fit-cover">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0) 50%, rgba(0,0,0,0.4) 100%);"></div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Navigasi Indikator Vertikal & Panah --}}
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-secondary border-opacity-25">
                <div class="d-flex align-items-center gap-3">
                    @foreach($kamarUnggulans as $index => $ku)
                        <button type="button" class="hero-indikator btn btn-link p-0 text-white text-decoration-none fs-6 fw-bold {{ $index === 0 ? 'active text-primary' : 'text-opacity-50' }}" data-slide="{{ $index }}" aria-label="Slide {{ $index + 1 }}">0{{ $index + 1 }}</button>
                    @endforeach
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="heroPrev" class="btn btn-outline-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" aria-label="Sebelumnya">←</button>
                    <button type="button" id="heroNext" class="btn btn-outline-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" aria-label="Berikutnya">→</button>
                </div>
            </div>
        @else
            {{-- Slide Bawaan Jika Belum Ada Kamar Unggulan --}}
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
<!-- 
    {{-- ===== BAGIAN: 4. KAMAR LAINNYA (SLIDER HORIZONTAL) ===== --}}
    <section class="mb-5 py-4" id="kamar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Kamar Lainnya</h2>
                <p class="text-muted mb-0">Pilih kamar yang paling cocok untukmu</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="scrollKiri" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" aria-label="Geser Kiri">←</button>
                <button type="button" id="scrollKanan" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;" aria-label="Geser Kanan">→</button>
            </div>
        </div>

        <div class="slider-horizontal pb-3" id="containerKamarLainnya">
            @foreach($kamarLainnyas as $kl)
                <div class="slider-item">
                    <a href="{{ route('publik.kamar.show', $kl->id) }}" class="text-decoration-none text-dark">
                        <div class="kartu-kamar h-100">
                            <div style="height: 200px; background-color: #E5E7EB;" class="position-relative">
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
                <div class="text-white p-4 rounded-4 shadow h-100 text-center" style="background-color: var(--warna-primer);">
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
                    <div class="p-4 rounded-4 h-100 d-flex flex-column {{ $isAturan ? 'text-white' : 'border bg-light' }}" style="{{ $isAturan ? 'background-color: var(--warna-primer);' : '' }}">
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
                <div style="height: 300px; border-radius: 20px; overflow: hidden;">
                    <img src="{{ $kamarUnggulans->first()->sampul_url ?? asset('img/kamar-placeholder.svg') }}" alt="Kamar Kos Azelia" class="w-100 h-100 object-fit-cover">
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