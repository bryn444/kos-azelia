<?php
/**
 * NAMA FILE: WajibGantiPassword.php (DITAMBAHKAN TAHAP 9B)
 * FUNGSI: Mengarahkan pengguna dengan wajib_ganti_password = true ke halaman ganti kata sandi.
 * HALAMAN: Seluruh rute panel setelah login.
 * CARA MENGUBAH: Sesuaikan pengecualian route bila diperlukan.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WajibGantiPassword
{
    /**
     * Memeriksa status wajib_ganti_password pada pengguna yang sedang login.
     * Alasan: Memaksa penggantian kata sandi bawaan sebelum mengakses fitur panel lain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && (bool) $user->wajib_ganti_password) {
            if ($request->routeIs('password.change', 'logout')) {
                return $next($request);
            }

            return redirect()
                ->route('password.change')
                ->with('peringatan_sandi', 'Demi keamanan, ganti kata sandi bawaan terlebih dahulu.')
                ->with('error', 'Demi keamanan, ganti kata sandi bawaan terlebih dahulu.');
        }

        return $next($request);
    }
}