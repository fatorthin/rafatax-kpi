<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Auth\CustomLogin;

Route::get('/', function () {
    return redirect()->to('/login');
});

Route::get('/staff/login', function () {
    return redirect()->to('/login');
});

Route::get('/app/login', function () {
    return redirect()->to('/login');
});

// Custom login route untuk semua user
Route::get('/login', CustomLogin::class)->name('login');
