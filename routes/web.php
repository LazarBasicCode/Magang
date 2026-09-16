<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Controllers\LppmDosenController;
use App\Http\Controllers\LppmMahasiswaController;
use App\Http\Controllers\LppmRekognisiController;
use App\Http\Controllers\KerjaSamaController;
use App\Http\Controllers\UserController;

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

    // ---- Kerja Sama (baru) ----
    Route::get('/kerja-sama', [KerjaSamaController::class, 'index'])->name('kerja-sama.index');
    Route::post('/kerja-sama', [KerjaSamaController::class, 'store'])->name('kerja-sama.store');
    Route::put('/kerja-sama/{kerjaSama}', [KerjaSamaController::class, 'update'])->name('kerja-sama.update');
    Route::delete('/kerja-sama/{kerjaSama}', [KerjaSamaController::class, 'destroy'])->name('kerja-sama.destroy');

    // ---- Data Master Users (baru) ----
    Route::get('/data-master/users', [UserController::class, 'index'])->name('data-master.users.index');
    Route::post('/data-master/users', [UserController::class, 'store'])->name('data-master.users.store');
    Route::put('/data-master/users/{user}', [UserController::class, 'update'])->name('data-master.users.update');
    Route::delete('/data-master/users/{user}', [UserController::class, 'destroy'])->name('data-master.users.destroy');
});