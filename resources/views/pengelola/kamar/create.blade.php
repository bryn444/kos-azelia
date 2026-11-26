@extends('layouts.panel.main')

{{-- 
  NAMA FILE: create.blade.php (Modul Kamar - DITAMBAHKAN TAHAP 5A & TAHAP 9A)
  FUNGSI: Form tambah kamar baru dengan status otomatis kosong, pratinjau tarif Rupiah, dan unggah foto.
  CARA MENGUBAH: Sesuaikan batas tarif di config/kos.php bagian 'aturan'.
--}}

@section('title', 'Tambah Kamar Baru - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: FORM TAMBAH KAMAR BARU ===== --}}
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h2 class="fw-bold mb-4">Tambah Kamar Baru</h2>

            <form action="{{ route('pengelola.kamar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Nomor Kamar --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Nomor Kamar</label>
                    <input type="text" name="nomor_kamar" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('nomor_kamar') is-invalid @enderror" value="{{ old('nomor_kamar') }}" placeholder="Contoh: K16" required>
                    @error('nomor_kamar')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                {{-- Tarif Sewa & Pratinjau Rupiah --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Tarif Sewa Per Bulan (Rp)</label>
                    <input type="number" name="tarif" id="inputTarifCreate" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('tarif') is-invalid @enderror" value="{{ old('tarif', max(550000, $tarifMin ?? 100000)) }}" min="{{ $tarifMin ?? 100000 }}" max="{{ $tarifMaks ?? 5000000 }}" step="1" required>
                    <small class="text-muted d-block mt-2 fs-6">
                        Pratinjau Tarif: <strong id="previewRupiahCreate" class="text-primary">Rp0</strong>
                        (Rentang: Rp{{ number_format($tarifMin ?? 100000, 0, ',', '.') }} - Rp{{ number_format($tarifMaks ?? 5000000, 0, ',', '.') }})
                    </small>
                    @error('tarif')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                {{-- DITAMBAHKAN TAHAP 9A: Status kamar ditampilkan sebagai teks & berubah otomatis --}}
                <div class="mb-4 p-3 bg-light rounded-4 border">
                    <div class="fs-5 fw-semibold">Status Awal: <span class="badge bg-success fs-6">Kosong</span></div>
                    <small class="text-muted d-block mt-1 fs-6">Status berubah otomatis saat penghuni masuk atau keluar</small>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Deskripsi Singkat Kamar --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Deskripsi Singkat Kamar (Opsional, Maks 300 karakter)</label>
                    <textarea name="deskripsi" class="form-control fs-5 rounded-4 p-3 @error('deskripsi') is-invalid @enderror" rows="3" maxlength="300" placeholder="Contoh: Kamar luas menghadap taman, kasur queen size, jendela besar...">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Saklar Kamar Unggulan --}}
                <div class="mb-4 form-check form-switch p-3 bg-light rounded-4">
                    <input type="checkbox" name="unggulan" value="1" class="form-check-input ms-0 me-3" id="unggulanSwitch" {{ old('unggulan') ? 'checked' : '' }}>
                    <label class="form-check-label fs-5 fw-semibold pt-1" for="unggulanSwitch">
                        ⭐ Jadikan Kamar Unggulan <span class="d-block text-muted fs-6">Tampil di bagian paling atas halaman calon penghuni. (Wajib memiliki minimal 1 foto).</span>
                    </label>
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Unggah Foto Multiple --}}
                <div class="mb-4">
                    <label class="form-label fs-5 fw-semibold">Unggah Foto Kamar (Maksimal 6 Foto, 2MB per foto)</label>
                    <input type="file" name="fotos[]" id="inputFoto" class="form-control form-control-lg fs-5 rounded-pill px-4 @error('fotos.*') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp" multiple>
                    <small class="text-muted d-block mt-2">Foto pertama yang diunggah akan otomatis menjadi foto sampul utama.</small>
                    @error('fotos.*')<div class="invalid-feedback fs-6">{{ $message }}</div>@enderror
                </div>

                {{-- DITAMBAHKAN TAHAP 5A: Pratinjau Foto Sebelum Disimpan (JavaScript) --}}
                <div id="previewContainer" class="row g-3 mb-4 d-none">
                    <div class="col-12"><span class="fw-semibold text-muted">Pratinjau Foto yang Dipilih:</span></div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">Ya, Simpan</button>
                    <a href="{{ route('pengelola.kamar') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputTarif = document.getElementById('inputTarifCreate');
        const previewRupiah = document.getElementById('previewRupiahCreate');

        function formatRupiah(nilai) {
            const angka = parseInt(nilai, 10);
            if (isNaN(angka)) return 'Rp0';
            return 'Rp' + angka.toLocaleString('id-ID');
        }

        if (inputTarif && previewRupiah) {
            previewRupiah.textContent = formatRupiah(inputTarif.value);
            inputTarif.addEventListener('input', function () {
                previewRupiah.textContent = formatRupiah(this.value);
            });
        }

        const inputFoto = document.getElementById('inputFoto');
        const previewContainer = document.getElementById('previewContainer');

        if (inputFoto) {
            inputFoto.addEventListener('change', function (e) {
                const existingPreviews = previewContainer.querySelectorAll('.preview-item');
                existingPreviews.forEach(el => el.remove());

                if (this.files && this.files.length > 0) {
                    previewContainer.classList.remove('d-none');
                    
                    Array.from(this.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-4 col-sm-6 preview-item';
                            col.innerHTML = `
                                <div class="card border shadow-sm rounded-4 p-2 text-center">
                                    <img src="${e.target.result}" class="rounded-3 object-fit-cover">
                                    <span class="small text-muted mt-1">Foto ${index + 1} ${index === 0 ? '(Sampul Utama)' : ''}</span>
                                </div>
                            `;
                            previewContainer.appendChild(col);
                        }
                        reader.readAsDataURL(file);
                    });
                } else {
                    previewContainer.classList.add('d-none');
                }
            });
        }
    });
</script>
@endsection