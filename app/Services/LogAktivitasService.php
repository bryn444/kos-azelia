<?php
/**
 * NAMA FILE: LogAktivitasService.php (DITAMBAHKAN TAHAP 9B)
 * FUNGSI: Layanan statis untuk mencatat aktivitas sistem secara aman tanpa mengganggu proses utama.
 * HALAMAN: Seluruh modul yang mencatat log aktivitas.
 * CARA MENGUBAH: Tambahkan kunci sensitif ke $kunciSensitif jika ada field rahasia baru.
 */

namespace App\Services;

use App\Models\LogAktivitas;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogAktivitasService
{
    /**
     * Daftar nama field sensitif yang tidak boleh ikut tercatat.
     * Alasan: Melindungi kata sandi, token, dan kredensial rahasia.
     */
    private static array $kunciSensitif = [
        'password',
        'password_lama',
        'password_baru',
        'password_confirmation',
        'remember_token',
        '_token',
        'idempotency_token',
        'api_key',
        'secret',
        'token',
    ];

    /**
     * Mencatat satu baris log aktivitas ke tabel log_aktivitas.
     * Alasan: Menyediakan jejak audit yang aman, manusiawi, dan tidak menggagalkan aksi utama bila error.
     */
    public static function catat(
        string $aksi,
        string $deskripsi,
        mixed $subjek = null,
        mixed $lama = null,
        mixed $baru = null,
        ?int $penggunaIdOverride = null
    ): void {
        try {
            $penggunaId = $penggunaIdOverride ?? Auth::id();
            $ip = request() ? request()->ip() : null;

            $subjekTipe = null;
            $subjekId = null;

            if ($subjek instanceof Model) {
                $subjekTipe = get_class($subjek);
                $subjekId = $subjek->getKey() !== null ? (int) $subjek->getKey() : null;
            } elseif (is_array($subjek)) {
                $subjekTipe = isset($subjek['tipe']) ? (string) $subjek['tipe'] : null;
                $subjekId = isset($subjek['id']) && is_numeric($subjek['id']) ? (int) $subjek['id'] : null;
            }

            $dataLamaBersih = self::bersihkanData($lama);
            $dataBaruBersih = self::bersihkanData($baru);

            LogAktivitas::create([
                'pengguna_id' => $penggunaId ? (int) $penggunaId : null,
                'aksi' => mb_substr(trim($aksi), 0, 40),
                'deskripsi' => mb_substr(trim($deskripsi), 0, 255),
                'subjek_tipe' => $subjekTipe ? mb_substr($subjekTipe, 0, 120) : null,
                'subjek_id' => $subjekId,
                'data_lama' => $dataLamaBersih,
                'data_baru' => $dataBaruBersih,
                'ip' => $ip ? mb_substr((string) $ip, 0, 45) : null,
                'created_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Membersihkan data agar tidak memuat field sensitif atau konten .env.
     * Alasan: Mencegah kebocoran data sensitif pada kolom JSON log aktivitas.
     */
    private static function bersihkanData(mixed $data): ?array
    {
        if ($data === null) {
            return null;
        }

        if ($data instanceof Model) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return ['nilai' => self::bersihkanNilai($data)];
        }

        $hasil = [];
        foreach ($data as $kunci => $nilai) {
            $namaKunci = strtolower((string) $kunci);
            if (in_array($namaKunci, self::$kunciSensitif, true) || str_contains($namaKunci, 'password') || str_contains($namaKunci, 'token')) {
                continue;
            }

            if (is_array($nilai) || is_object($nilai)) {
                $hasil[$kunci] = self::bersihkanData($nilai);
            } else {
                $hasil[$kunci] = self::bersihkanNilai($nilai);
            }
        }

        return $hasil;
    }

    /**
     * Menyaring string agar tidak menyimpan pola variabel lingkungan sensitif.
     * Alasan: Menjaga agar isi atau potongan .env tidak pernah masuk ke log.
     */
    private static function bersihkanNilai(mixed $nilai): mixed
    {
        if (!is_string($nilai)) {
            return $nilai;
        }

        if (str_contains($nilai, 'APP_KEY=') || str_contains($nilai, 'DB_PASSWORD=')) {
            return '[disensor]';
        }

        return $nilai;
    }
}