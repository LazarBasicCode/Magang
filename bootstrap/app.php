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
        $middleware->alias([
            'menu.access' => \App\Http\Middleware\EnsureMenuAccess::class,
        ]);

        // Pengguna yang SUDAH login dan mencoba membuka route khusus tamu
        // (halaman login, proses login, lupa/reset password) dialihkan ke dashboard.
        $middleware->redirectUsersTo(fn () => route('dashboard.index'));

        // Cegah halaman tersimpan di cache/Back button (lihat PreventBackHistory).
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
