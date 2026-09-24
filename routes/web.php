<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Controllers\LppmDosenController;
use App\Http\Controllers\LppmMahasiswaController;
use App\Http\Controllers\LppmRekognisiController;
use App\Http\Controllers\KerjaSamaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginAuditController;

// Halaman Login (index.blade.php)
Route::get('/', function () {
    return view('index');
})->name('login');

// Rute Pemrosesan Login & Logout
Route::post('/login-process', [AuthController::class, 'loginProcess'])
    ->middleware('throttle:login-ip');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ---- Lupa Password ----
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Rute yang dilindungi (Hanya bisa diakses jika sudah login)
Route::middleware(['auth'])->group(function () {
    // ---- Dashboard ----
    Route::middleware('menu.access:dashboard,readonly')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    });

    // ---- Kemahasiswaan ----
    Route::middleware('menu.access:kemahasiswaan,readonly')->group(function () {
        Route::get('/kemahasiswaan', [KemahasiswaanController::class, 'index'])->name('kemahasiswaan.index');
    });
    Route::middleware('menu.access:kemahasiswaan,biasa')->group(function () {
        Route::post('/kemahasiswaan', [KemahasiswaanController::class, 'store'])->name('kemahasiswaan.store');
        Route::put('/kemahasiswaan/{kemahasiswaan}', [KemahasiswaanController::class, 'update'])->name('kemahasiswaan.update');
        Route::delete('/kemahasiswaan/{kemahasiswaan}', [KemahasiswaanController::class, 'destroy'])->name('kemahasiswaan.destroy');
    });

    // ---- LPPM Dosen ----
    Route::middleware('menu.access:lppm_dosen,readonly')->group(function () {
        Route::get('/lppm/dosen', [LppmDosenController::class, 'index'])->name('lppm.dosen.index');
    });
    Route::middleware('menu.access:lppm_dosen,biasa')->group(function () {
        Route::post('/lppm/dosen', [LppmDosenController::class, 'store'])->name('lppm.dosen.store');
        Route::put('/lppm/dosen/{lppmDosen}', [LppmDosenController::class, 'update'])->name('lppm.dosen.update');
        Route::delete('/lppm/dosen/{lppmDosen}', [LppmDosenController::class, 'destroy'])->name('lppm.dosen.destroy');
    });

    // ---- LPPM Mahasiswa ----
    Route::middleware('menu.access:lppm_mahasiswa,readonly')->group(function () {
        Route::get('/lppm/mahasiswa', [LppmMahasiswaController::class, 'index'])->name('lppm.mahasiswa.index');
    });
    Route::middleware('menu.access:lppm_mahasiswa,biasa')->group(function () {
        Route::post('/lppm/mahasiswa', [LppmMahasiswaController::class, 'store'])->name('lppm.mahasiswa.store');
        Route::put('/lppm/mahasiswa/{lppmMahasiswa}', [LppmMahasiswaController::class, 'update'])->name('lppm.mahasiswa.update');
        Route::delete('/lppm/mahasiswa/{lppmMahasiswa}', [LppmMahasiswaController::class, 'destroy'])->name('lppm.mahasiswa.destroy');
    });

    // ---- LPPM Rekognisi ----
    Route::middleware('menu.access:rekognisi,readonly')->group(function () {
        Route::get('/lppm/rekognisi', [LppmRekognisiController::class, 'index'])->name('lppm.rekognisi.index');
    });
    Route::middleware('menu.access:rekognisi,biasa')->group(function () {
        Route::post('/lppm/rekognisi', [LppmRekognisiController::class, 'store'])->name('lppm.rekognisi.store');
        Route::put('/lppm/rekognisi/{rekognisi}', [LppmRekognisiController::class, 'update'])->name('lppm.rekognisi.update');
        Route::delete('/lppm/rekognisi/{rekognisi}', [LppmRekognisiController::class, 'destroy'])->name('lppm.rekognisi.destroy');
    });

    // ---- Kerja Sama ----
    Route::middleware('menu.access:kerja_sama,readonly')->group(function () {
        Route::get('/kerja-sama', [KerjaSamaController::class, 'index'])->name('kerja-sama.index');
    });
    Route::middleware('menu.access:kerja_sama,biasa')->group(function () {
        Route::post('/kerja-sama', [KerjaSamaController::class, 'store'])->name('kerja-sama.store');
        Route::put('/kerja-sama/{kerjaSama}', [KerjaSamaController::class, 'update'])->name('kerja-sama.update');
        Route::delete('/kerja-sama/{kerjaSama}', [KerjaSamaController::class, 'destroy'])->name('kerja-sama.destroy');
    });

    // ---- Data Master Users ----
    Route::middleware('menu.access:data_master,readonly')->group(function () {
        Route::get('/data-master/users', [UserController::class, 'index'])->name('data-master.users.index');
    });
    Route::middleware('menu.access:data_master,biasa')->group(function () {
        Route::post('/data-master/users', [UserController::class, 'store'])->name('data-master.users.store');
        Route::put('/data-master/users/{user}', [UserController::class, 'update'])->name('data-master.users.update');
        Route::delete('/data-master/users/{user}', [UserController::class, 'destroy'])->name('data-master.users.destroy');
    });

    // ---- Hak Akses ----
    // Akses menu ini sendiri diatur lewat sistem hak akses yang sama:
    // default admin = tidak diberi akses, sampai superadmin mengubahnya
    // lewat halaman ini juga.
    Route::middleware('menu.access:hak_akses,readonly')->group(function () {
        Route::get('/hak-akses', [HakAksesController::class, 'index'])->name('hak-akses.index');
    });
    Route::middleware('menu.access:hak_akses,biasa')->group(function () {
        Route::put('/hak-akses/{user}', [HakAksesController::class, 'update'])->name('hak-akses.update');
    });

    // ---- Log Aktivitas (audit percobaan login) ----
    // Superadmin selalu bisa (bypass, lihat User::menuLevel()); admin cuma
    // bisa kalau superadmin memberi akses lewat halaman Hak Akses.
    // Mahasiswa/dosen tidak pernah punya opsi ini sama sekali.
    Route::middleware('menu.access:log,readonly')->group(function () {
        Route::get('/login-audit', [LoginAuditController::class, 'index'])->name('login-audit.index');
        Route::get('/login-audit/data', [LoginAuditController::class, 'data'])->name('login-audit.data');
        Route::get('/login-audit/{attempt}', [LoginAuditController::class, 'show'])->name('login-audit.show');
    });
});
