<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProofUploadController;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\DeliveryNoteController;

// Redirect root URL to /admin
Route::redirect('/', '/admin');
Route::post('/upload-proof/{id}', [ProofUploadController::class, 'store']);
use App\Http\Controllers\PaymentProofController;

Route::post('/upload-proof', [PaymentProofController::class, 'upload'])->name('proof.upload');

Route::get('/delivery-notes/{record}/print', [DeliveryNoteController::class, 'print'])->name('print.delivery.note');


// Tambahkan route lain jika perlu
