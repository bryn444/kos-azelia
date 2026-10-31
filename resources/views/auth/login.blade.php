<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Kos Azelia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary mb-2" style="font-size: 32px;">Masuk</h2>
                        <p class="text-muted fs-5">Sistem Informasi Pengelolaan Kos Azelia</p>
                    </div>

                    @if($errors->has('login'))
                        <div class="alert alert-danger fs-5 p-4 rounded-4 text-center shadow-sm mb-4">
                            {{ $errors->first('login') }}
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="username" class="form-label fs-5 fw-semibold text-dark">Username</label>
                            <input type="text" class="form-control form-control-lg fs-5 rounded-pill px-4 py-3 @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required autofocus>
                            @error('username')
                                <div class="invalid-feedback fs-6">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fs-5 fw-semibold text-dark">Kata Sandi</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg fs-5 rounded-start-pill px-4 py-3 @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan kata sandi" required>
                                <button class="btn btn-outline-secondary px-4 rounded-end-pill fs-5" type="button" id="togglePassword">Lihat</button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback fs-6 d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold fs-4">Masuk</button>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('publik.index') }}" class="text-decoration-none text-muted fs-5">Kembali ke halaman informasi</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Lihat' : 'Sembunyikan';
        });
    </script>
</body>
</html>