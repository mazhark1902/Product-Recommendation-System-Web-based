<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;

class PaymentProofController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transaction,id',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        // Simpan file ke storage
        $path = $request->file('proof')->store('proofs', 'public');

        // Update record
        $transaction->update([
            'proof' => $path,
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload.');
    }
}
