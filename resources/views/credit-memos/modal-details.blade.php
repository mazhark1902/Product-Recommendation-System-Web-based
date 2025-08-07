<div>
    <p><strong>Credit Memo ID:</strong> {{ $record->credit_memos_id }}</p>
    <p><strong>Return ID:</strong> {{ $record->return_id }}</p>
    <p><strong>Customer ID:</strong> {{ $record->customer_id }}</p>
    <p><strong>Amount:</strong> Rp{{ number_format($record->amount, 2, ',', '.') }}</p>
    <p><strong>Issued Date:</strong> {{ $record->issued_date }}</p>
    <p><strong>Status:</strong> {{ $record->status }}</p>
</div>
