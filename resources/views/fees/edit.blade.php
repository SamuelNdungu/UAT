@extends('layouts.appPages')

@section('content')
<style>
    .form-group label {
        font-weight: bold;
    }

    .group-heading {
        margin-top: 20px;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
        color: #007bff;
        font-size: 1.25rem;
    }
</style>

<div class="container">
    <h1 class="my-4">Edit Fee</h1>

    <form action="{{ route('fees.update', $fee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Invoice Information Section -->
        <div class="group-heading">Invoice Information</div>
        <div class="row mt-3">
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
        <div class="group-heading">Fee Details</div>
        <div class="row mt-3">
            <div class="col-md-4 form-group">
                <label for="date">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('date') is-invalid @enderror" 
                       id="date" name="date" value="{{ old('date', $fee->date) }}" required>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="due_date">Due Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                       id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                @error('due_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="amount">Amount (KES) <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('amount') is-invalid @enderror" 
                       id="amount" name="amount" value="{{ old('amount', str_replace(',', '', number_format($fee->amount, 2))) }}" required>
                @error('amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Additional Information Section -->
        <div class="group-heading">Additional Information</div>
        <div class="row mt-3">
            <div class="col-md-8 form-group">
                <label for="description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description', $fee->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="pending" {{ old('status', $fee->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ old('status', $fee->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ old('status', $fee->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Update Fee</button>
                <a href="{{ route('fees.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('amount').addEventListener('input', function(e) {
        let value = this.value.replace(/[^\d.]/g, '');
        value = value.replace(/(\..*)\./, '$1');
        const parts = value.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        this.value = parts.join('.');
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const amountInput = document.getElementById('amount');
        amountInput.value = amountInput.value.replace(/,/g, '');
    });
</script>
@endpush
