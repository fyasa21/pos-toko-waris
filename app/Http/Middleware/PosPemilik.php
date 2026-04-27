<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PosPemilik
{
    /**
     * Membatasi akses halaman web hanya untuk pengguna dengan role pemilik.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('user');
        if (!$user || ($user['role'] ?? '') !== 'pemilik') {
            abort(403, 'Halaman ini hanya untuk pemilik toko.');
        }
        return $next($request);
    }
}
