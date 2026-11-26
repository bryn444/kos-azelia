@extends('layouts.panel.main')

{{--
  NAMA FILE: sinkron_status.blade.php (DITAMBAHKAN TAHAP 9A)
  FUNGSI: Halaman konfirmasi sinkronisasi status kamar berdasarkan data penghuni aktif.
  HALAMAN: Panel Pengelola - Konfirmasi Sinkronisasi Status Kamar.
  CARA MENGUBAH: Sesuaikan tata letak tabel perbandingan status bila diperlukan.
--}}

@section('title', 'Sinkronisasi Status Kamar - Kos Azelia')

@section('content')
{{-- ===== BAGIAN: KONFIRMASI SINKRONISASI STATUS KAMAR ===== --}}
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <h2 class="fw-bold mb-3">Konfirmasi Perbaikan Status Kamar</h2>
            <p class="text-muted fs-5 mb-4">
                Status kamar ditentukan otomatis dari ada tidaknya penghuni yang berstatus aktif.
            </p>

            @if($kamarBermasalah->isEmpty())
                <div class="alert alert-success rounded-4 p-4 fs-5 mb-4">
                    Semua kamar sudah memiliki status yang sesuai dengan penghuni aktif.
                </div>
                <a href="{{ route('pengelola.kamar') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4">
                    Kembali ke Daftar Kamar
                </a>
            @else
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle fs-5 mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kamar</th>
                                <th>Penghuni Aktif</th>
                                <th>Status Sekarang</th>
                                <th>Status Seharusnya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kamarBermasalah as $kb)
                                @php $seharusnya = $kb->statusSeharusnya(); @endphp
                                <tr>
                                    <td class="fw-bold">Kamar {{ $kb->nomor_kamar }}</td>
                                    <td>{{ $kb->penghuniAktif->nama_lengkap ?? '-' }}</td>
                                    <td><span class="badge bg-secondary fs-6 text-capitalize">{{ $kb->status }}</span></td>
                                    <td><span class="badge bg-primary fs-6 text-capitalize">{{ $seharusnya }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form action="{{ route('pengelola.kamar.sinkron.proses') }}" method="POST">
                    @csrf
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 flex-fill">
                            Ya, Perbaiki
                        </button>
                        <a href="{{ route('pengelola.kamar') }}" class="btn btn-secondary btn-lg rounded-pill px-5 py-3 fw-bold fs-4 d-inline-flex align-items-center justify-content-center">
                            Batal
                        </a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection