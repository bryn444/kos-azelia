@extends('layouts.panel.main')

{{-- 
  NAMA FILE: ganti_password.blade.php (DIPERBARUI TAHAP 9B)
  FUNGSI: Halaman ganti kata sandi dengan pesan wajib ganti sandi bawaan dan panduan aturan kata sandi.
  CARA MENGUBAH: Sesuaikan teks aturan kata sandi sesuai config/kos.php bagian 'keamanan'.
--}}

@section('title', 'Ganti Kata Sandi - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: FORM GANTI KATA SANDI ===== --}}
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h2 class="fw-bold mb-3">Ganti Kata Sandi</h2>

            {{-- DITAMBAHKAN TAHAP 9B: Tampilkan pesan wajib ganti kata sandi bawaan --}}
            @if(!empty($pesanWajib))
                <div class="alert alert-warning rounded-4 p-3 fs-5 mb-4">
                    {{ $pesanWajib }}
                </div>
            @endif

            <div class="bg-light border rounded-4 p-3 mb-4 fs-6 text-muted">
                Minimal {{ $minPanjang ?? 8 }} karakter, mengandung huruf dan angka, berbeda dari kata sandi lama, dan bukan kata sandi bawaan/terlarang.
            </div>

            <form action="{{ route('password.change') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Kata Sandi Lama</label>
                    <input type="password" name="password_lama" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('password_lama') is-invalid @enderror" required>
                    @error('password_lama')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Kata Sandi Baru</label>
                    <input type="password" name="password_baru" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('password_baru') is-invalid @enderror" required>
                    @error('password_baru')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Ulangi Kata Sandi Baru</label>
                    <input type="password" name="password_baru_confirmation" class="form-control form-control-lg fs-5 rounded-pill px-4" required>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Simpan Kata Sandi Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection