<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PenggunaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPengguna()) {
            abort(403, 'Halaman ini khusus untuk akun Penghasil / Pengguna.');
        }

        return $next($request);
    }
}

