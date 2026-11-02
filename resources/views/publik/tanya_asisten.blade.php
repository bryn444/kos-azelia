@extends('layouts.publik')

@section('title', 'Tanya Asisten Virtual - Kos Azelia')

@section('content')
<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h1 class="fs-judul fw-bold mb-2">Tanya Asisten Kos Azelia</h1>
                <p class="text-muted fs-5">Punya pertanyaan seputar kamar, harga, atau fasilitas? Ketik pertanyaan Anda di bawah ini.</p>
            </div>

            <!-- Tombol Pertanyaan Cepat -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 fs-6 btn-cepat" data-query="Berapa harga sewa per bulan?">Berapa harga per bulan?</button>
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 fs-6 btn-cepat" data-query="Apa saja fasilitas yang disediakan?">Fasilitas kos</button>
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 fs-6 btn-cepat" data-query="Apakah boleh membawa hewan peliharaan?">Hewan peliharaan</button>
            </div>

            <!-- Kotak Hasil Percakapan / Jawaban -->
            @if(session('jawaban_hasil'))
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light border-start border-primary border-5" id="jawabanBox">
                    <h4 class="fw-bold text-primary mb-2">Jawaban Asisten:</h4>
                    <p class="fs-5 mb-0 text-dark">{!! session('jawaban_hasil') !!}</p>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light d-none" id="jawabanBoxAjax">
                    <h4 class="fw-bold text-primary mb-2">Jawaban Asisten:</h4>
                    <p class="fs-5 mb-0 text-dark" id="teksJawabanAjax"></p>
                </div>
            @endif

            <!-- Form Tanya Asisten -->
            <div class="card-kustom bg-white shadow-sm p-4 p-md-5">
                <form action="{{ route('publik.tanya.process') }}" method="POST" id="formTanya">
                    @csrf
                    <div class="mb-4">
                        <label for="pertanyaan" class="form-label fw-bold fs-5 mb-2">Tulis Pertanyaan Anda (Maks 200 karakter):</label>
                        <textarea name="pertanyaan" id="pertanyaan" rows="4" class="form-control form-control-lg fs-5 rounded-4 p-3" maxlength="200" placeholder="Contoh: Apakah listrik sudah termasuk?" required>{{ old('pertanyaan') }}</textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-pil btn-biru py-3">Kirim Pertanyaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('pertanyaan');
        const form = document.getElementById('formTanya');
        const btnCepat = document.querySelectorAll('.btn-cepat');
        const boxAjax = document.getElementById('jawabanBoxAjax');
        const teksAjax = document.getElementById('teksJawabanAjax');

        // Tombol cepat
        btnCepat.forEach(btn => {
            btn.addEventListener('click', function () {
                textarea.value = this.getAttribute('data-query');
                textarea.focus();
            });
        });

        // AJAX Fetch untuk pengalaman chat mulus dengan fallback form biasa
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch("{{ route('publik.tanya.process') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                teksAjax.innerHTML = data.jawaban;
                boxAjax.classList.remove('d-none');
                boxAjax.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(err => {
                // Fallback jika JS gagal, submit form konvensional
                form.submit();
            });
        });
    });
</script>
@endsection