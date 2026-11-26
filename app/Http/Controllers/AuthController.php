<?php
/**
 * NAMA FILE: AuthController.php (DIPERBARUI TAHAP 9B)
 * FUNGSI: Menangani login (dengan rate limit), logout, dan ganti kata sandi (dengan aturan keamanan & log aktivitas).
 * HALAMAN: Halaman Login & Ganti Kata Sandi.
 * CARA MENGUBAH: Sesuaikan aturan kata sandi dan batas login pada config/kos.php bagian 'keamanan'.
 */

namespace App\Http\Controllers;

use App\Services\LogAktivitasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login beserta pesan sesi berakhir bila ada.
     * Alasan: Memberi tahu pengguna ketika sesi tidak aktif telah habis.
     */
    public function showLogin(Request $request)
    {
        // DITAMBAHKAN TAHAP 9B: Deteksi jika pengguna dialihkan karena sesi sebelumnya berakhir
        $sesiHabis = $request->boolean('expired') || $request->session()->has('sesi_habis');

        return view('auth.login', compact('sesiHabis'));
    }

    /**
     * Memproses autentikasi pengguna dengan pembatasan percobaan gagal per username + IP.
     * Alasan: Mencegah tebakan kata sandi berulang dan mencatat hasil login ke log aktivitas.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // DITAMBAHKAN TAHAP 9B: Batasan percobaan login per kombinasi username dan IP
        $maksPercobaan = (int) config('kos.keamanan.login_maks_percobaan', 5);
        $jendelaDetik = (int) config('kos.keamanan.login_jendela_detik', 60);
        $kunciLimit = 'login:' . Str::lower(trim($validated['username'])) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($kunciLimit, $maksPercobaan)) {
            $detikSisa = RateLimiter::availableIn($kunciLimit);
            $pesanLimit = "Terlalu banyak percobaan. Coba lagi dalam {$detikSisa} detik.";

            // DITAMBAHKAN TAHAP 9B: Catat kegagalan login karena batas percobaan tanpa menyimpan kata sandi
            LogAktivitasService::catat(
                'login_gagal',
                'Percobaan login diblokir sementara untuk username ' . trim($validated['username']),
                null,
                null,
                ['username' => trim($validated['username']), 'alasan' => 'batas_percobaan']
            );

            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => $pesanLimit]);
        }

        if (Auth::attempt(['username' => $validated['username'], 'password' => $validated['password']])) {
            RateLimiter::clear($kunciLimit);
            $request->session()->regenerate();

            $user = Auth::user();

            // DITAMBAHKAN TAHAP 9B: Catat login berhasil
            LogAktivitasService::catat(
                'login_berhasil',
                "{$user->nama_lengkap} berhasil masuk ke sistem",
                $user,
                null,
                ['username' => $user->username, 'peran' => $user->peran]
            );

            if ((bool) $user->wajib_ganti_password) {
                return redirect()
                    ->route('password.change')
                    ->with('peringatan_sandi', 'Demi keamanan, ganti kata sandi bawaan terlebih dahulu.');
            }

            if ($user->peran === 'pemilik') {
                return redirect()->route('pemilik.rekap');
            }

            return redirect()->route('pengelola.beranda');
        }

        RateLimiter::hit($kunciLimit, $jendelaDetik);

        // DITAMBAHKAN TAHAP 9B: Catat login gagal tanpa menyimpan kata sandi
        LogAktivitasService::catat(
            'login_gagal',
            'Gagal masuk dengan username ' . trim($validated['username']),
            null,
            null,
            ['username' => trim($validated['username'])]
        );

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['username' => 'Username atau kata sandi tidak sesuai.']);
    }

    /**
     * Mengakhiri sesi pengguna yang sedang login.
     * Alasan: Membersihkan sesi secara aman dan mencatat aktivitas keluar sistem.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // DITAMBAHKAN TAHAP 9B: Catat aktivitas logout sebelum sesi dihapus
            LogAktivitasService::catat(
                'logout',
                "{$user->nama_lengkap} keluar dari sistem",
                $user,
                null,
                ['username' => $user->username],
                (int) $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sukses', 'Anda telah keluar dari sistem.');
    }

    /**
     * Menampilkan form ganti kata sandi.
     * Alasan: Memungkinkan pengguna mengganti kata sandi bawaan atau kata sandi berkala.
     */
    public function showGantiPassword()
    {
        $user = Auth::user();
        $pesanWajib = null;

        if ($user && (bool) $user->wajib_ganti_password) {
            $pesanWajib = session('peringatan_sandi') ?? 'Demi keamanan, ganti kata sandi bawaan terlebih dahulu.';
        }

        $minPanjang = (int) config('kos.keamanan.password_min_panjang', 8);

        return view('auth.ganti_password', compact('user', 'pesanWajib', 'minPanjang'));
    }

    /**
     * Memvalidasi aturan kata sandi baru di server dan memperbarui akun pengguna.
     * Alasan: Menegakkan panjang minimal, kombinasi huruf & angka, beda dari sandi lama, dan daftar terlarang.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $minPanjang = (int) config('kos.keamanan.password_min_panjang', 8);
        $terlarang = array_map('strtolower', (array) config('kos.keamanan.password_terlarang', []));

        $validated = $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => ['required', 'string', "min:{$minPanjang}", 'confirmed'],
        ], [
            'password_lama.required' => 'Kata sandi lama wajib diisi.',
            'password_baru.required' => 'Kata sandi baru wajib diisi.',
            'password_baru.min' => "Kata sandi baru minimal {$minPanjang} karakter.",
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($validated['password_lama'], $user->password)) {
            return back()
                ->withInput()
                ->withErrors(['password_lama' => 'Kata sandi lama tidak sesuai.']);
        }

        $sandiBaru = $validated['password_baru'];

        // DITAMBAHKAN TAHAP 9B: Harus mengandung huruf dan angka
        if (!preg_match('/[A-Za-z]/', $sandiBaru) || !preg_match('/[0-9]/', $sandiBaru)) {
            return back()
                ->withInput()
                ->withErrors(['password_baru' => 'Kata sandi baru harus mengandung huruf dan angka.']);
        }

        // DITAMBAHKAN TAHAP 9B: Harus berbeda dari kata sandi lama
        if (Hash::check($sandiBaru, $user->password)) {
            return back()
                ->withInput()
                ->withErrors(['password_baru' => 'Kata sandi baru harus berbeda dari kata sandi lama.']);
        }

        // DITAMBAHKAN TAHAP 9B: Tidak boleh termasuk daftar kata sandi terlarang
        if (in_array(Str::lower($sandiBaru), $terlarang, true)) {
            return back()
                ->withInput()
                ->withErrors(['password_baru' => 'Kata sandi baru terlalu umum atau termasuk daftar terlarang. Pilih kata sandi lain.']);
        }

        $user->update([
            'password' => Hash::make($sandiBaru),
            'wajib_ganti_password' => false,
        ]);

        // DITAMBAHKAN TAHAP 9B: Catat penggantian kata sandi tanpa menyimpan nilai kata sandi
        LogAktivitasService::catat(
            'ganti_password',
            "{$user->nama_lengkap} mengganti kata sandi akun",
            $user
        );

        if ($user->peran === 'pemilik') {
            return redirect()->route('pemilik.rekap')->with('sukses', 'Kata sandi berhasil diperbarui.');
        }

        return redirect()->route('pengelola.beranda')->with('sukses', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Alias method tampilkan form ganti kata sandi.
     * Alasan: Menjaga kompatibilitas jika ada pemanggilan showChangePassword.
     */
    public function showChangePassword()
    {
        return $this->showGantiPassword();
    }

    /**
     * Alias method proses ganti kata sandi.
     * Alasan: Menjaga kompatibilitas jika ada pemanggilan changePassword.
     */
    public function changePassword(Request $request)
    {
        return $this->updatePassword($request);
    }
}