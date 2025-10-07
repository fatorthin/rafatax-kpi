<?php

use App\Filament\Pages\Auth\CustomLogin;
use Illuminate\Support\Facades\Route;

Route::get('/login', CustomLogin::class)->name('login');
