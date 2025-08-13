<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use App\Filament\Resources\TransactionResource;

class PaymentProofController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transaction,id',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5000',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        // Simpan file ke storage
        $path = $request->file('proof')->store('proofs', 'public');

        // Update record
        $transaction->update([
            'proof' => $path,
        ]);

        return redirect()
    ->route('filament.admin.resources.transactions.email-payment', [
        'record' => $transaction->id,
    ])
    ->with('success', '✅ Proof uploaded successfully for Sales Order: ' . ($transaction->salesOrder->sales_order_no ?? 'Unknown'));

    }
}
