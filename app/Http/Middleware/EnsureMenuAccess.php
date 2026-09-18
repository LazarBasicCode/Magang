<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    /**
     * Memblokir akses ke suatu route kalau user yang login tidak punya
     * level hak akses minimal untuk menu terkait.
     *
     * Pemakaian di routes: ->middleware('menu.access:kemahasiswaan,readonly')
     * $menu = key menu (lihat App\Models\HakAkses::MENUS)
     * $min  = level minimal yang dibutuhkan: readonly (default), biasa, atau penuh
     */
    public function handle(Request $request, Closure $next, string $menu, string $min = 'readonly'): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->canAccessMenu($menu, $min)) {
            abort(403, 'Anda tidak memiliki hak akses ke menu ini.');
        }

        return $next($request);
    }
}
