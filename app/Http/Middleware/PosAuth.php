<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PosAuth
{
    /**
     * Memastikan pengguna web memiliki session login yang masih valid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('pos_token') || !session('user')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        $response = $next($request);
        return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }
}
