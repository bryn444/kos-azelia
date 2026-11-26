<?php
/**
 * NAMA FILE: config/kos.php
 * FUNGSI: Menyimpan data kontak, nomor WhatsApp, telepon, jam operasional, aturan bisnis, keamanan, dan log aktivitas.
 * HALAMAN: Seluruh halaman publik dan panel admin.
 * CARA MENGUBAH: Ubah nilai konfigurasi pada array di bawah ini.
 */

return [
    'nama' => 'Kos Azelia',
    'whatsapp' => '6281234567890', // Format internasional tanpa tanda +
    'telepon' => '0812-3456-7890',
    'jam_operasional' => '08:00 - 20:00 WIB',
    'tarif_default' => '550.000',

    // DITAMBAHKAN TAHAP 9A: Batas aturan bisnis untuk mencegah salah input pengelola
    'aturan' => [
        'tarif_min' => 100000,               // Tarif kamar minimum (Rp100.000)
        'tarif_maks' => 5000000,             // Tarif kamar maksimum (Rp5.000.000)
        'tarif_ubah_persen' => 30,           // Perubahan tarif > 30% wajib centang konfirmasi ekstra
        'bayar_lama_hari' => 30,             // Tanggal bayar lebih lama dari 30 hari wajib centang konfirmasi
        'tambahan_waktu_maks_hari' => 30,    // Tenggat tambahan waktu maksimal 30 hari setelah jatuh tempo
        'tunggakan_periksa_hari' => 45,      // Menunggak > 45 hari memunculkan peringatan "sudah pindah?"
    ],

    /*
    |--------------------------------------------------------------------------
    | DITAMBAHKAN TAHAP 9B: Masa Sesi, Keamanan Akun & Log Aktivitas
    |--------------------------------------------------------------------------
    | Masa sesi tidak aktif diatur lewat variabel SESSION_LIFETIME di file .env
    | (bawaan: 60 menit). Ubah nilai SESSION_LIFETIME di .env lalu jalankan
    | `php artisan config:clear` jika konfigurasi sudah di-cache.
    */
    'keamanan' => [
        // Maksimal percobaan login gagal per menit untuk kombinasi username + IP
        'login_maks_percobaan' => 5,
        'login_jendela_detik' => 60,

        // Panjang minimal kata sandi baru
        'password_min_panjang' => 8,

        // Daftar kata sandi terlarang (huruf kecil)
        'password_terlarang' => [
            'kos12345',
            '12345678',
            'password',
            'azelia',
            'kosazelia',
            'admin123',
            'pengelola123',
            'pemilik123',
        ],
    ],

    'log_aktivitas' => [
        // Jumlah baris log yang ditampilkan per halaman
        'per_halaman' => 25,
    ],
];