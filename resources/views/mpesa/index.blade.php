@extends('layouts.app')

@section('content')
<div class="container">
    <h1>MPESA Transactions</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Transaction Code</th>
                <th>Amount</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Received</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
            <tr>
                <td>{{ $tx->id }}</td>
                <td>{{ $tx->transaction_code }}</td>
                <td>{{ $tx->amount }}</td>
                <td>{{ $tx->phone_number }}</td>
                <td>{{ $tx->status }}</td>
                <td>{{ $tx->created_at }}</td>
                <td>
                    <a href="{{ route('mpesa.transactions.show', $tx->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                    @if($tx->receipt_id && $tx->status !== 'applied')
                        <form method="POST" action="{{ route('mpesa.transactions.apply', $tx->id) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-success" onclick="return confirm('Apply allocation for this transaction?')">Apply allocation</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $transactions->links() }}
</div>
@endsection
