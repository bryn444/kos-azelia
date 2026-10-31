<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Kos Azelia')</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Panel Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
    @yield('styles')
</head>
<body>

    <div class="panel-wrapper">
        <!-- Sidebar untuk Laptop/Desktop -->
        @include('layouts.panel.sidebar')

        <div class="main-content-area">
            <!-- Navbar Atas -->
            @include('layouts.panel.navbar')

            <!-- Container Konten Utama -->
            <main class="container-fluid py-4 px-3 px-md-4">
                @if(session('sukses'))
                    <div class="alert alert-success fs-5 p-4 rounded-4 shadow-sm mb-4">
                        {{ session('sukses') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger fs-5 p-4 rounded-4 shadow-sm mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.panel.footer')
        </div>
    </div>

    <!-- Bilah Menu Bawah Khusus HP (Maksimal 5 Item) -->
    @include('layouts.panel.mobile_nav')

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>