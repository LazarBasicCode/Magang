<?php

namespace App\Providers;

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
