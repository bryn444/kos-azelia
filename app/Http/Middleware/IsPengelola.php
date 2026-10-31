<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPengelola
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'pengelola') {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Halaman ini khusus untuk Pengelola Kos.');
    }
}