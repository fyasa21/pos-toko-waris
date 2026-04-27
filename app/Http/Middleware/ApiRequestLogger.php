<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestLogger
{
    /**
     * Mencatat ringkasan request API sebelum response dikirimkan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start    = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2);

        // Hanya log request yang bukan GET atau yang lambat (>1000ms)
        if ($request->method() !== 'GET' || $duration > 1000) {
            Log::channel('daily')->info('API Request', [
                'method'   => $request->method(),
                'url'      => $request->fullUrl(),
                'user_id'  => $request->user()?->user_id,
                'role'     => $request->user()?->role,
                'status'   => $response->getStatusCode(),
                'duration' => $duration . 'ms',
                'ip'       => $request->ip(),
            ]);
        }

        // Tambahkan header debug (hanya di local)
        if (config('app.debug')) {
            $response->headers->set('X-Response-Time', $duration . 'ms');
        }

        return $response;
    }
}
