<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/kemahasiswaan', function () {
    return view('kemahasiswaan');
});

Route::get('/kemahasiswaanOld', function () {
    return view('kemahasiswaanOld');
});

Route::get('/kemahasiswaanOld2', function () {
    return view('kemahasiswaanOld2');
});

