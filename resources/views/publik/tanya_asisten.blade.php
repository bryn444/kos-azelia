{{-- 
  NAMA FILE: resources/views/publik/tanya_asisten.blade.php
  FUNGSI: Antarmuka chat interaktif dengan asisten virtual berbasis pencocokan database.
  HALAMAN: Tanya Asisten (/tanya-asisten).
  CARA MENGUBAH: Sesuaikan teks pertanyaan cepat atau tata letak kotak chat.
--}}
@extends('layouts.publik')

@section('title', 'Tanya Asisten Virtual - Kos Azelia')

@section('content')
<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h1 class="fw-bold mb-2">Tanya Asisten Kos Azelia</h1>
                <p class="text-muted">Punya pertanyaan seputar kamar, harga, atau fasilitas? Ketik pertanyaan Anda di bawah ini.</p>
            </div>

            {{-- Tombol Pertanyaan Cepat --}}
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2 small btn-pertanyaan-cepat" data-query="Berapa harga sewa per bulan?">Berapa harga per bulan?</button>
                <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2 small btn-pertanyaan-cepat" data-query="Apa saja fasilitas yang disediakan?">Fasilitas kos</button>
                <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2 small btn-pertanyaan-cepat" data-query="Apakah boleh membawa hewan peliharaan?">Hewan peliharaan</button>
            </div>

            {{-- Kotak Jawaban Asisten (AJAX & Fallback) --}}
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light border-start border-primary border-5 {{ session('jawaban_hasil') ? '' : 'd-none' }}" id="kotakJawabanAsisten">
                <h4 class="fw-bold text-primary mb-2">Jawaban Asisten:</h4>
                <p class="fs-5 mb-0 text-dark" id="teksJawabanAsisten">{!! session('jawaban_hasil') !!}</p>
            </div>

            {{-- Form Pertanyaan --}}
            <div class="bg-white border rounded-4 shadow-sm p-4 p-md-5">
                <form action="{{ route('publik.tanya.process') }}" method="POST" id="formTanyaAsisten">
                    @csrf
                    <div class="mb-4">
                        <label for="inputPertanyaan" class="form-label fw-bold mb-2">Tulis Pertanyaan Anda (Maks 200 karakter):</label>
                        <textarea name="pertanyaan" id="inputPertanyaan" rows="4" class="form-control rounded-4 p-3" maxlength="200" placeholder="Contoh: Apakah listrik sudah termasuk?" required>{{ old('pertanyaan') }}</textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn-pil-biru py-3">Kirim Pertanyaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/publik.js') }}"></script>
@endsection