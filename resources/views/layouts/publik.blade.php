{{-- 
  NAMA FILE: resources/views/layouts/publik.blade.php
  FUNGSI: Kerangka utama tata letak halaman publik (Navbar putih & Footer gelap).
  HALAMAN: Seluruh halaman publik calon penghuni.
  CARA MENGUBAH: Ubah menu navigasi atau tautan footer di bawah ini.
--}}
@php
    // DITAMBAHKAN TAHAP 6B: Penanda apakah halaman sedang dibuka di dalam iframe editor pengelola
    $sedangPratinjau = !empty($modePratinjau);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kos Azelia - Kos Putri Nyaman & Strategis')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/publik.css') }}">
    @if ($sedangPratinjau)
        {{-- DITAMBAHKAN TAHAP 6B: Sembunyikan scrollbar di dalam iframe agar tinggi diukur murni oleh panggung editor --}}
        <style>
            html, body {
                overflow: hidden !important;
            }
        </style>
    @endif
    @yield('styles')
</head>
<body class="{{ $sedangPratinjau ? 'mode-pratinjau-latar' : '' }}">

    {{-- DITAMBAHKAN TAHAP 6A: Kanvas latar belakang utama sebagai anak langsung <body> --}}
    @include('publik.partials.latar-kanvas')

    {{-- ===== BAGIAN: NAVBAR PUTIH ===== --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-dark" href="{{ route('publik.index') }}">Kos Azelia</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublik" aria-label="Toggle navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarPublik">
                <ul class="navbar-nav mx-auto align-items-center gap-4 fs-6">
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.index') }}#tentang">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.index') }}#kamar">Kamar</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.index') }}#cara">Cara Mengekos</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.tanya') }}">Tanya Asisten</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.hubungi') }}">Kontak</a></li>
                </ul>
                <div class="d-flex">
                    <a href="https://wa.me/{{ config('kos.whatsapp') }}?text={{ urlencode('Halo, saya ingin menanyakan ketersediaan kamar di Kos Azelia.') }}" target="_blank" class="btn-pil-hitam">Hubungi</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- ===== BAGIAN: KONTEN UTAMA ===== --}}
    <main>
        @yield('content')
    </main>

    {{-- ===== BAGIAN: FOOTER GELAP ===== --}}
    <footer class="py-5 mt-5" style="background-color: var(--warna-footer-bg); color: var(--warna-footer-teks);">
        <div class="container">
            <div class="row g-4 justify-content-between align-items-center">
                <div class="col-md-6">
                    <h3 class="text-white fw-bold mb-2">Kos Azelia</h3>
                    <p class="mb-0">Tempat tinggal putri yang nyaman, aman, dan strategis.</p>
                    <p class="small mt-2 mb-0">Jam Pengelola: {{ config('kos.jam_operasional') }} | Telp/WA: {{ config('kos.telepon') }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('login') }}" class="text-white text-decoration-underline small">Masuk (Pengelola/Pemilik)</a>
                </div>
            </div>
            <div class="border-top border-secondary mt-4 pt-4 text-center small">
                &copy; 2026 Kos Azelia. Dikelola dengan Sistem Informasi Sederhana.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    @if ($sedangPratinjau)
        {{-- DITAMBAHKAN TAHAP 6B: Skrip sinkronisasi kanvas & tinggi halaman dengan editor pengelola --}}
        <script src="{{ asset('js/latar-kanvas.js') }}"></script>
    @endif
</body>
</html>