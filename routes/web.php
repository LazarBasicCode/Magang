<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Halaman Login (index.blade.php)
Route::get('/', function () {
    return view('index');
})->name('login');

// Rute Pemrosesan Login & Logout
Route::post('/login-process', [AuthController::class, 'loginProcess']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang dilindungi (Hanya bisa diakses jika sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/kemahasiswaan', function () {
        return view('kemahasiswaan');
    });

    Route::get('/lppm/mahasiswa', function () {
        return view('lppm_mahasiswa');
    });

    Route::get('/lppm/dosen', function () {
        return view('lppm_dosen');
    });

    Route::get('/lppm/rekognisi', function () {
        return view('lppm_rekognisi');
    });

    Route::get('/kerja-sama', function () {
        return view('kerja-sama');
    });
});






Route::get('/kemahasiswaan', function () {
    return view('kemahasiswaan');
});

Route::get('/lppm/mahasiswa', function () {
    return view('lppm_mahasiswa');
});

Route::get('/lppm/dosen', function () {
    return view('lppm_dosen');
});

Route::get('/lppm/rekognisi', function () {
    return view('lppm_rekognisi');
});
