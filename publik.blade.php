<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kos Azelia - Kos Putri Nyaman & Strategis')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/publik.css') }}">
    @yield('styles')
</head>
<body>

    <!-- 1. Bilah Atas Charcoal -->
    <div style="background-color: var(--charcoal); color: #DCE5F1;" class="py-2 px-4 fs-6">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div>📞 WhatsApp Pengelola: <strong class="text-white">+62 812-3456-7890</strong></div>
            <div>⏰ Jam Operasional: <strong>08:00 - 20:00 WIB</strong></div>
        </div>
    </div>

    <!-- 2. Navbar Putih -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-extrabold fs-3 text-primary" href="{{ route('publik.index') }}" style="color: var(--biru-baja) !important; font-weight: 800;">KOS AZELIA</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-3 fs-5">
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.index') }}#informasi">Informasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.index') }}#fasilitas">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.tanya') }}">Tanya Asisten</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('publik.hubungi') }}">Kontak</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="https://wa.me/{{ config('kos.whatsapp') }}" target="_blank" class="btn btn-pil btn-biru">Hubungi Pengelola</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <main>
        @yield('content')
    </main>

    <!-- 6. Footer Charcoal -->
    <footer style="background-color: var(--charcoal); color: #DCE5F1;" class="py-5 mt-5">
        <div class="container text-center">
            <h3 class="text-white fw-bold mb-3">KOS AZELIA</h3>
            <p class="mb-4">Kos putri yang aman, nyaman, dan tenang untuk mahasiswi & karyawati.</p>
            <div class="border-top border-secondary pt-4 d-flex justify-content-center align-items-center gap-3">
                <span>&copy; 2026 Kos Azelia.</span>
                <a href="{{ route('login') }}" class="text-white text-decoration-underline small">Masuk (Pengelola/Pemilik)</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>