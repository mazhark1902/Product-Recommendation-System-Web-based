<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;

// Auth::routes(); // Ini akan auto-include semua route untuk login, register, reset password, dll

Route::get('/', function () {
    return view('welcome');
});
