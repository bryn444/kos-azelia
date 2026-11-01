<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanBulananExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected string $bulan;

    public function __construct(string $bulan)
    {
        $this->bulan = $bulan;
    }

    public function collection(): Collection
    {
        return Pembayaran::with(['penghuni.kamar', 'pencatat'])
            ->where('periode_bulan', $this->bulan)
            ->whereNull('dibatalkan_pada')
            ->orderBy('tanggal_bayar', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Pembayaran',
            'Nomor Kamar',
            'Nama Penghuni',
            'Tanggal Bayar',
            'Tanggal Jatuh Tempo',
            'Jumlah (Rp)',
            'Dicatat Oleh',
        ];
    }

    public function map($row): array
    {
        /** @var Pembayaran $row */
        return [
            $row->id,
            $row->penghuni->kamar->nomor_kamar ?? '-',
            $row->penghuni->nama_lengkap ?? '-',
            $row->tanggal_bayar,
            $row->tanggal_jatuh_tempo,
            $row->jumlah,
            $row->pencatat->nama_lengkap ?? 'Kakek',
        ];
    }

    public function title(): string
    {
        return 'Laporan ' . $this->bulan;
    }
}