<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melarang browser menyimpan halaman di cache / back-forward cache.
 *
 * Tanpa ini, setelah logout tombol "Back" masih bisa menampilkan halaman
 * dashboard dari cache, dan setelah login tombol "Back" masih bisa
 * menampilkan form login. Dengan header ini browser wajib meminta ulang ke
 * server, sehingga middleware auth/guest yang memutuskan tampilannya.
 */
class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
