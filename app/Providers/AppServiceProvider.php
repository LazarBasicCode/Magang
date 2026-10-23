<?php

namespace App\Providers;

use App\Support\ChangeTracker;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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
        // Pengumpul perubahan data per request (dibaca middleware LogActivity; lihat App\Support\ChangeTracker).
        $this->app->singleton(ChangeTracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Catat sebelum/sesudah setiap data yang dibuat, diubah, atau dihapus lewat model Eloquent,
        // supaya Log Aktivitas bisa menampilkan input -> output. Mencatat tidak boleh merusak proses utama.
        Event::listen(['eloquent.created: *', 'eloquent.updated: *', 'eloquent.deleted: *'], function (string $event, array $payload) {
            try {
                $model = $payload[0] ?? null;
                if ($model instanceof Model) {
                    // "eloquent.updated: App\Models\User" -> "update"
                    $name = substr(explode(':', $event, 2)[0], strlen('eloquent.'));
                    app(ChangeTracker::class)->track(match ($name) {
                        'created' => 'create',
                        'updated' => 'update',
                        'deleted' => 'delete',
                        default   => $name,
                    }, $model);
                }
            } catch (\Throwable $e) {
                // Sengaja diam: gagal mencatat tidak boleh menggagalkan aksi pengguna.
            }
        });

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
            'persetujuan',
            'dashboard',
            'lppm_dosen',
            'lppm_mahasiswa',
            'lppm_rekognisi',
            'kerja-sama',
            'data-master-users',
            'hak-akses',
            'laporan',
            'backup',
            'login-audit',
            'profil',
            // Jaring pengaman untuk partial sidebar: view-view di atas meng-include
            // 'partials.sidebar' dan sudah membawa $__user, tapi dengan mendaftarkan
            // nama partial ini juga, $__user tetap terjamin walau suatu saat
            // dipanggil dari tempat lain.
            'partials.sidebar',
            // Sama untuk partial header (profil dropdown memakai $__user).
            'partials.header',
        ], function ($view) {
            $view->with('__user', auth()->user());
        });
    }
}
