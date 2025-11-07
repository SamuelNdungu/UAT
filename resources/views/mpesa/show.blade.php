@extends('layouts.app')

@section('content')
<div class="container">
    <h1>MPESA Transaction #{{ $tx->id }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <p><strong>Transaction Code:</strong> {{ $tx->transaction_code }}</p>
            <p><strong>Amount:</strong> {{ $tx->amount }}</p>
            <p><strong>Phone:</strong> {{ $tx->phone_number }}</p>
            <p><strong>Status:</strong> {{ $tx->status }}</p>
            <p><strong>Receipt ID:</strong> {{ $tx->receipt_id }}</p>
            <p><strong>Payment ID:</strong> {{ $tx->payment_id }}</p>

            <h5>Raw payload</h5>
            <pre style="max-height:400px;overflow:auto;background:#f8f9fa;padding:10px">{{ json_encode($tx->raw_payload, JSON_PRETTY_PRINT) }}</pre>

            @if($tx->receipt_id && $tx->status !== 'applied')
                <form method="POST" action="{{ route('mpesa.transactions.apply', $tx->id) }}">
                    @csrf
                    <button class="btn btn-success">Apply allocation</button>
                </form>
            @endif
        </div>
    </div>

    <a href="{{ route('mpesa.transactions.index') }}" class="btn btn-link mt-3">Back to list</a>
</div>
@endsection
