<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProofUploadController;
use Illuminate\Support\Facades\Password;

// Redirect root URL to /admin
Route::redirect('/', '/admin');
Route::post('/upload-proof/{id}', [ProofUploadController::class, 'store']);
use App\Http\Controllers\PaymentProofController;

Route::post('/upload-proof', [PaymentProofController::class, 'upload'])->name('proof.upload');


// Tambahkan route lain jika perlu
