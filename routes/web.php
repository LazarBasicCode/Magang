<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Controllers\LppmDosenController;
use App\Http\Controllers\LppmMahasiswaController;
use App\Http\Controllers\LppmRekognisiController;

// Halaman Login (index.blade.php)
Route::get('/', function () {
    return view('index');
})->name('login');

// Rute Pemrosesan Login & Logout
Route::post('/login-process', [AuthController::class, 'loginProcess']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute yang dilindungi (Hanya bisa diakses jika sudah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/kemahasiswaan', [KemahasiswaanController::class, 'index'])->name('kemahasiswaan.index');
    Route::post('/kemahasiswaan', [KemahasiswaanController::class, 'store'])->name('kemahasiswaan.store');
    Route::put('/kemahasiswaan/{kemahasiswaan}', [KemahasiswaanController::class, 'update'])->name('kemahasiswaan.update');
    Route::delete('/kemahasiswaan/{kemahasiswaan}', [KemahasiswaanController::class, 'destroy'])->name('kemahasiswaan.destroy');

    // ---- LPPM Dosen (baru) ----
    Route::get('/lppm/dosen', [LppmDosenController::class, 'index'])->name('lppm.dosen.index');
    Route::post('/lppm/dosen', [LppmDosenController::class, 'store'])->name('lppm.dosen.store');
    Route::put('/lppm/dosen/{lppmDosen}', [LppmDosenController::class, 'update'])->name('lppm.dosen.update');
    Route::delete('/lppm/dosen/{lppmDosen}', [LppmDosenController::class, 'destroy'])->name('lppm.dosen.destroy');

    // ---- LPPM Mahasiswa (baru) ----
    Route::get('/lppm/mahasiswa', [LppmMahasiswaController::class, 'index'])->name('lppm.mahasiswa.index');
    Route::post('/lppm/mahasiswa', [LppmMahasiswaController::class, 'store'])->name('lppm.mahasiswa.store');
    Route::put('/lppm/mahasiswa/{lppmMahasiswa}', [LppmMahasiswaController::class, 'update'])->name('lppm.mahasiswa.update');
    Route::delete('/lppm/mahasiswa/{lppmMahasiswa}', [LppmMahasiswaController::class, 'destroy'])->name('lppm.mahasiswa.destroy');

    // ---- LPPM Rekognisi (baru) ----
    Route::get('/lppm/rekognisi', [LppmRekognisiController::class, 'index'])->name('lppm.rekognisi.index');
    Route::post('/lppm/rekognisi', [LppmRekognisiController::class, 'store'])->name('lppm.rekognisi.store');
    Route::put('/lppm/rekognisi/{rekognisi}', [LppmRekognisiController::class, 'update'])->name('lppm.rekognisi.update');
    Route::delete('/lppm/rekognisi/{rekognisi}', [LppmRekognisiController::class, 'destroy'])->name('lppm.rekognisi.destroy');

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
