@extends('layouts.panel.main')

@section('title', 'Ganti Kata Sandi - Kos Azelia')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4" style="color: var(--panel-primary); font-size: 28px;">Ganti Kata Sandi</h2>

            @if(session('sukses'))
                <div class="alert alert-success fs-5 p-3 rounded-4 mb-4">{{ session('sukses') }}</div>
            @endif

            <form action="{{ route('password.change') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="current_password" class="form-label fs-5 fw-semibold">Kata Sandi Saat Ini</label>
                    <input type="password" class="form-control form-control-lg fs-5 rounded-pill px-4 py-3 @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="invalid-feedback fs-6">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fs-5 fw-semibold">Kata Sandi Baru</label>
                    <input type="password" class="form-control form-control-lg fs-5 rounded-pill px-4 py-3 @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback fs-6">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fs-5 fw-semibold">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" class="form-control form-control-lg fs-5 rounded-pill px-4 py-3" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold fs-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection