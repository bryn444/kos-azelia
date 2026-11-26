<?php
/**
 * NAMA FILE: bootstrap/app.php (DIPERBARUI TAHAP 9B)
 * FUNGSI: Mendaftarkan alias middleware dan middleware grup panel pada Laravel 13.
 * CARA MENGUBAH: Tambahkan alias middleware baru di bagian withMiddleware.
 */

use App\Http\Middleware\IsPemilik;
use App\Http\Middleware\IsPengelola;
use App\Http\Middleware\NoCache;
use App\Http\Middleware\WajibGantiPassword;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'isPengelola' => IsPengelola::class,
            'isPemilik' => IsPemilik::class,
            'noCache' => NoCache::class,
            'wajibGantiPassword' => WajibGantiPassword::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();