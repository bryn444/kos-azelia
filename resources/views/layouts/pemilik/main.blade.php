<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Pemilik - Kos Azelia')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pemilik-glass.css') }}">
    <style>
        :root {
            --latar-halaman: {!! auth()->user()->css_latar_halaman !!};
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="d-flex min-vh-100 position-relative">
        @include('layouts.pemilik.sidebar')

        <div class="main-content-area">
            @include('layouts.pemilik.navbar')

            <main class="container-fluid px-0">
                @if(session('sukses'))
                    <div class="alert alert-success fs-5 p-4 rounded-4 shadow-sm mb-4 border-0">
                        {{ session('sukses') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger fs-5 p-4 rounded-4 shadow-sm mb-4 border-0">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="konten-kaca">
                    @yield('content')
                </div>
            </main>

            <footer class="text-center py-4 text-white-50 mt-auto fs-6" style="text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                <p class="mb-0">&copy; 2026 Kos Azelia. Dikelola dengan Sistem Informasi Sederhana.</p>
            </footer>
        </div>
    </div>

    @include('layouts.pemilik.mobile_nav')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>