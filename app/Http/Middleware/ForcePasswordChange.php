<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Selama mahasiswa/dosen masih memakai password default (NIM/NIDN), satu-satunya yang boleh
 * dilakukan adalah membuka dashboard (tempat pop-up wajib ganti password muncul), membuat &
 * menyimpan password baru, membaca notifikasi, dan logout.
 *
 * Penguncian dilakukan di SERVER, bukan cuma pop-up di browser, supaya tidak bisa dilewati
 * dengan mengetik URL halaman lain langsung.
 */
class ForcePasswordChange
{
    /** Nama route yang tetap boleh diakses selama password belum diganti. */
    private const ALLOWED = [
        'dashboard.index',
        'account.password.generate',
        'account.password',
        'notifications.index',
        'notifications.mark-all-read',
        'notifications.read',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->mustChangePassword() || $request->routeIs(...self::ALLOWED)) {
            return $next($request);
        }

        $message = 'Ganti password default Anda terlebih dahulu sebelum memakai fitur lain.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message'               => $message,
                'force_password_change' => true,
                'redirect'              => route('dashboard.index'),
            ], 423);
        }

        return redirect()->route('dashboard.index')->with('force_password_notice', $message);
    }
}
