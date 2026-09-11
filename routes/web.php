<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/kemahasiswaan', function () {
    return view('kemahasiswaan');
});

