<?php

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
        // DITAMBAHKAN: percayai proxy (ngrok) supaya Laravel tahu pengunjung memakai HTTPS,
        // sehingga asset() dan route() menghasilkan alamat https://.
        // Untuk demo ngrok cukup '*'. Saat hosting sungguhan, ganti dengan IP proxy server.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'isPengelola' => \App\Http\Middleware\IsPengelola::class,
            'isPemilik' => \App\Http\Middleware\IsPemilik::class,
            'noCache' => \App\Http\Middleware\NoCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();