<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;

// Redirect root URL to /admin
Route::redirect('/', '/admin');

// Tambahkan route lain jika perlu
