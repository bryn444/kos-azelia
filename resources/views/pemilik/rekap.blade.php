@extends('layouts.panel.main')

@section('title', 'Rekap Pemilik - Kos Azelia')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h2 class="fw-bold mb-0">Rekapitulasi Usaha Kos</h2>
    
    <form action="{{ route('pemilik.rekap') }}" method="GET" class="d-flex align-items-center gap-2">
        <label for="bulan" class="fs-5 fw-semibold text-nowrap">Pilih Bulan:</label>
        <input type="month" id="bulan" name="bulan" class="form-control form-control-lg fs-5 rounded-pill px-3" value="{{ $bulanSelected }}" onchange="this.form.submit()">
    </form>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white bg-primary">
            <h4 class="fs-5 text-white-50 fw-normal">Pemasukan Bulan Ini</h4>
            <h1 class="display-4 fw-bold text-white mb-0">Rp{{ number_format($totalPemasukanBulanIni, 0, ',', '.') }}</h1>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white bg-success">
            <h4 class="fs-5 text-white-50 fw-normal">Kamar Terisi</h4>
            <h1 class="display-4 fw-bold text-white mb-0">{{ $totalKamarTerisi }} <span class="fs-4 fw-normal">/ 15 Kamar</span></h1>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white bg-danger">
            <h4 class="fs-5 text-white-50 fw-normal">Belum Bayar</h4>
            <h1 class="display-4 fw-bold text-white mb-0">{{ $jumlahBelumBayar }} <span class="fs-4 fw-normal">Penghuni</span></h1>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h3 class="fw-bold mb-4">Grafik Pemasukan 6 Bulan Terakhir</h3>
    <!-- Tinggi canvas diperbesar menjadi 420px agar font besar muat dengan nyaman -->
    <div style="position: relative; height: 420px;">
        <canvas id="chartPemasukan"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartPemasukan').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels6Bulan) !!},
            datasets: [{
                label: 'Total Pemasukan (Rp)',
                data: {!! json_encode($dataPemasukan6Bulan) !!},
                backgroundColor: '#3B5F8A',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 16, // Memperbesar font label legenda di atas grafik
                            weight: 'bold'
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: 14, // Memperbesar font label bulan di sumbu bawah (X)
                            weight: '500'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 14, // Memperbesar font angka nominal di sumbu samping (Y)
                            weight: '500'
                        },
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>
@endsection