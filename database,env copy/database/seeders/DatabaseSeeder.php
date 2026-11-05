<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Pengguna;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Models\Pembayaran;
use App\Models\TambahanWaktu;
use App\Models\InformasiKos;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pengguna
        Pengguna::create([
            'username' => 'kakek',
            'password' => Hash::make('kos12345'),
            'role' => 'pengelola',
            'nama_lengkap' => 'Kakek Pengelola Kos',
        ]);

        Pengguna::create([
            'username' => 'endang',
            'password' => Hash::make('kos12345'),
            'role' => 'pemilik',
            'nama_lengkap' => 'Ibu Endang Supriyatin',
        ]);

        // 2. 15 Kamar (K01 - K15)
        for ($i = 1; $i <= 15; $i++) {
            $nomor = 'K' . str_pad($i, 2, '0', STR_PAD_LEFT);
            Kamar::create([
                'nomor_kamar' => $nomor,
                'tarif' => 550000,
                'status' => ($i == 15) ? 'kosong' : 'terisi',
            ]);
        }

        // 3. 14 Penghuni Putri
        $namaPenghuni = [
            'Siti Nurhaliza', 'Aulia Rahma', 'Anisa Putri', 'Dewi Lestari',
            'Rina Safitri', 'Bunga Citra', 'Nabila Maharani', 'Tia Astuti',
            'Dian Sastro', 'Putri Utami', 'Maya Kartika', 'Riri Handayani',
            'Siska Kohl', 'Gita Gutawa'
        ];

        $today = Carbon::today();

        foreach ($namaPenghuni as $index => $nama) {
            $kamarId = $index + 1; // Kamar 1 s.d 14
            
            // Variasikan tanggal masuk untuk skenario testing
            if ($index < 3) {
                // 2-3 Penghuni Menunggak (Tanggal masuk lama, tidak bayar bulan ini)
                $tanggalMasuk = $today->copy()->subMonths(3)->day(5);
            } elseif ($index < 6) {
                // 3 Penghuni Segera Jatuh Tempo (Jatuh tempo 3-5 hari ke depan)
                $tanggalMasuk = $today->copy()->subMonth()->addDays(3)->day($today->day + 3 > 28 ? 25 : $today->day + 3);
            } elseif ($index == 6) {
                // 1 Penghuni Diberi Tambahan Waktu
                $tanggalMasuk = $today->copy()->subMonths(2)->day(1);
            } else {
                // Lunas
                $tanggalMasuk = $today->copy()->subMonths(4)->day(10);
            }

            $penghuni = Penghuni::create([
                'kamar_id' => $kamarId,
                'nama_lengkap' => $nama,
                'nomor_hp' => '0812345678' . str_pad($index, 2, '0', STR_PAD_LEFT),
                'tanggal_masuk' => $tanggalMasuk->toDateString(),
                'status' => 'aktif',
            ]);

            // Buat Riwayat Pembayaran Contoh
            if ($index >= 6 && $index != 6) {
                // Lunas untuk periode berjalan
                $jt = $tanggalMasuk->copy()->addMonth()->subDay();
                Pembayaran::create([
                    'penghuni_id' => $penghuni->id,
                    'tanggal_bayar' => $today->copy()->subDays(2)->toDateString(),
                    'jumlah' => 550000,
                    'periode_bulan' => $today->format('Y-m'),
                    'tanggal_jatuh_tempo' => $jt->toDateString(),
                ]);
            }

            // Case Tambahan Waktu
            if ($index == 6) {
                TambahanWaktu::create([
                    'penghuni_id' => $penghuni->id,
                    'tanggal_jatuh_tempo_baru' => $today->copy()->addDays(10)->toDateString(),
                    'alasan' => 'Orang tua baru kirim uang minggu depan',
                    'is_aktif' => true,
                ]);
            }
        }

        // 4. Informasi Kos
        $infoList = [
            ['kategori' => 'harga', 'judul' => 'Tarif Sewa Kamar', 'deskripsi' => 'Rp550.000 per bulan (sudah termasuk listrik, air, dan Wi-Fi).'],
            ['kategori' => 'fasilitas', 'judul' => 'Fasilitas Kamar & Umum', 'deskripsi' => 'Kamar mandi dalam/luar, kasur, lemari baju, Wi-Fi gratis 24 jam, dapur bersama, dan tempat jemuran.'],
            ['kategori' => 'aturan', 'judul' => 'Hewan Peliharaan', 'deskripsi' => 'Dilarang membawa segala jenis hewan peliharaan.'],
            ['kategori' => 'umum', 'judul' => 'Ketentuan Pembayaran', 'deskripsi' => 'Pembayaran dilakukan secara TUNAI langsung kepada Kakek pengelola kos.'],
        ];

        foreach ($infoList as $info) {
            InformasiKos::create($info);
        }
    }
}