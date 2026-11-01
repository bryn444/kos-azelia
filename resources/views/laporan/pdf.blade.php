<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan Kos Azelia - {{ $bulanSelected }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1F2430;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3B5F8A;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            color: #3B5F8A;
            font-size: 18px;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #6c757d;
        }
        .summary-box {
            background-color: #F5F7FA;
            border: 1px solid #DCE5F1;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .summary-box table {
            width: 100%;
            border: none;
        }
        .summary-box td {
            font-size: 12px;
            border: none;
            padding: 4px 0;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #3B5F8A;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cccccc;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }
        table.data-table th {
            background-color: #3B5F8A;
            color: #ffffff;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>KOS AZELIA - LAPORAN BULANAN</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($bulanSelected . '-01')->translatedFormat('F Y') }}</p>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td><strong>Total Pemasukan Tunai:</strong> Rp{{ number_format($totalPemasukan, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Jumlah Pembayaran:</strong> {{ $pembayarans->count() }} Transaksi</td>
            </tr>
        </table>
    </div>

    <div class="section-title">1. Rincian Pembayaran Lunas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tgl Bayar</th>
                <th style="width: 15%;">Kamar</th>
                <th style="width: 30%;">Nama Penghuni</th>
                <th style="width: 15%;">Jatuh Tempo</th>
                <th style="width: 20%;" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $index => $bayar)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->format('d-m-Y') }}</td>
                    <td>Kamar {{ $bayar->penghuni->kamar->nomor_kamar ?? '-' }}</td>
                    <td>{{ $bayar->penghuni->nama_lengkap ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($bayar->tanggal_jatuh_tempo)->format('d-m-Y') }}</td>
                    <td class="text-right">{{ number_format($bayar->jumlah, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi pembayaran pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">2. Daftar Belum Bayar / Menunggak</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kamar</th>
                <th style="width: 35%;">Nama Penghuni</th>
                <th style="width: 25%;">Status</th>
                <th style="width: 20%;">Jatuh Tempo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarBelumBayar as $index => $bb)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>Kamar {{ $bb->kamar->nomor_kamar ?? '-' }}</td>
                    <td>{{ $bb->nama_lengkap }}</td>
                    <td>{{ $bb->status_detail['label'] }}</td>
                    <td>{{ $bb->status_detail['jatuh_tempo'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Semua pembayaran telah lunas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Informasi Kos Azelia pada {{ date('d-m-Y H:i') }}
    </div>

</body>
</html>