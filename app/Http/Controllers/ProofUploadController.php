<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class ProofUploadController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $transaction = Transaction::findOrFail($id);
        $path = $request->file('proof')->store('proofs', 'public');

        $transaction->update([
            'proof' => $path,
            'status' => 'paid',
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diupload.');
    }
}
