@extends('layouts.appPages')

@section('content')
<div class="container">
    <h3 class="my-4 text-center">Fee Details</h3>

    <!-- Customer Information Section -->
    <div class="group-heading bg-primary text-white p-2 mb-4">Customer Information</div>
    <div class="row mb-4">
        <div class="col-md-4 form-group">
            <label>Invoice Number</label>
            <input type="text" class="form-control" value="{{ $fee->invoice_number }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Customer Code</label>
            <input type="text" class="form-control" value="{{ $fee->customer ? $fee->customer->customer_code : 'N/A' }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Customer Name</label>
            <input type="text" class="form-control" value="{{ $fee->customer ? $fee->customer->customer_name : 'N/A' }}" readonly>
        </div>
    </div>

    <!-- Fee Details Section -->
    <div class="group-heading bg-primary text-white p-2 mb-4">Fee Details</div>
    <div class="row mb-4">
        <div class="col-md-4 form-group">
            <label>Date</label>
            <input type="text" class="form-control" value="{{ $fee->date }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Amount</label>
            <input type="text" class="form-control" value="{{ number_format($fee->amount, 2) }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Status</label>
            <div class="form-control" readonly>
                @if($fee->status == 'paid')
                    <span class="badge badge-success">Paid</span>
                @elseif($fee->status == 'pending')
                    <span class="badge badge-warning">Pending</span>
                @elseif($fee->status == 'overdue')
                    <span class="badge badge-danger">Overdue</span>
                @else
                    <span class="badge badge-secondary">{{ ucfirst($fee->status) }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Description Section -->
    <div class="group-heading bg-primary text-white p-2 mb-4">Description</div>
    <div class="form-group mb-4">
        <label>Description</label>
        <textarea class="form-control" readonly>{{ $fee->description }}</textarea>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-2">
            <a href="{{ route('fees.index') }}" class="btn btn-primary">Go Back</a>
        </div>
        <div class="col-md-2">
            <a href="{{ route('fees.edit', $fee->id) }}" class="btn btn-warning">Edit Fee</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('fees.create', ['customer_id' => $fee->customer ? $fee->customer->id : '']) }}" class="btn btn-success">
                <i class="fas fa-file-invoice"></i> Create Invoice
            </a>
        </div>
    </div>
</div>
@endsection