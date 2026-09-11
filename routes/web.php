<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
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

