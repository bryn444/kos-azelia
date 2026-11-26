<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Kos Azelia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body class="bg-light">
{{-- ===== BAGIAN: FORM LOGIN ===== --}}
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h1 class="h2 fw-bold text-center text-primary mb-4">Kos Azelia</h1>

            {{-- DITAMBAHKAN TAHAP 9B: Peringatan kecil jika dialihkan karena sesi berakhir --}}
            @if(!empty($sesiHabis))
                <div class="alert alert-warning rounded-4 py-2 px-3 fs-6 mb-3">
                    Sesi Anda berakhir, silakan masuk kembali.
                </div>
            @endif

            @if(session('sukses'))
                <div class="alert alert-success rounded-4 py-2 px-3 fs-6 mb-3">
                    {{ session('sukses') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger rounded-4 py-2 px-3 fs-6 mb-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fs-5 fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control form-control-lg fs-5 rounded-pill px-4" value="{{ old('username') }}" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Kata Sandi</label>
                    <input type="password" name="password" class="form-control form-control-lg fs-5 rounded-pill px-4" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold fs-4">Masuk</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>