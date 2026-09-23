<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Jaring pengaman kasar per-IP untuk endpoint login: maksimal 20 kali
        // request (berhasil ataupun gagal) per menit dari satu alamat IP.
        // Ini mencegah flood/scan cepat lewat banyak username sekaligus.
        // Pembatasan yang lebih presisi (per akun + pesan sisa waktu tunggu)
        // ditangani terpisah di AuthController::loginProcess().
        RateLimiter::for('login-ip', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // Semua halaman dengan sidebar butuh user yang login untuk
        // memutuskan menu mana yang ditampilkan (lihat User::canAccessMenu()).
        // Dengan view composer ini, $__user otomatis tersedia di semua view
        // tsb tanpa perlu di-pass manual dari tiap controller, jadi kalau
        // superadmin mengubah hak akses seseorang, sidebar-nya langsung
        // mengikuti tanpa perlu sentuh controller lain.
        View::composer([
            'kemahasiswaan',
            'kemahasiswaan-dashboard',
            'lppm_dosen',
            'lppm_mahasiswa',
            'lppm_rekognisi',
            'kerja-sama',
            'data-master-users',
            'hak-akses',
        ], function ($view) {
            $view->with('__user', auth()->user());
        });
    }
}
