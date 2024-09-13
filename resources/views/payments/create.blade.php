@extends('layouts.appPages')

@section('content')

<style>
    /* Styling for search results */
    .result-item {
        padding: 8px;
        cursor: pointer;
        background-color: #f8f9fa;
        border-bottom: 1px solid #ddd;
    }
    .result-item:hover {
        background-color: #e9ecef;
        color: #0056b3;
    }

    /* Form styling for a modern look */
    .form-group label {
        font-weight: bold;
    }
</style>

<div class="container">
    <h1 class="my-4">Add Payment</h1>

    <!-- Payment creation form -->
    <form action="{{ route('payments.store') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Customer Search and Selection -->
        <div id="customerCodeField" class="col-md-4 form-group">
            <label for="search">Search Customer</label>
            <input type="text" id="search" class="form-control" placeholder="Search Customer" value="{{ old('search') }}">
            <div id="results" class="mt-2"></div>
        </div>

        <!-- Read-only Customer Details -->
        <div class="col-md-3 form-group">
            <label for="customer_code_display">Code</label>
            <input type="text" id="customer_code_display" class="form-control" readonly value="{{ old('customer_code') }}">
            @error('customer_code')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <input type="hidden" id="customer_code" name="customer_code" value="{{ old('customer_code') }}">
        </div>

        <div class="col-md-6 form-group">
            <label for="customer_name_display">Customer Name <span class="text-danger">*</span></label>
            <input type="text" id="customer_name_display" class="form-control @error('customer_name') is-invalid @enderror" readonly value="{{ old('customer_name') }}">
            <input type="hidden" id="customer_name" name="customer_name" value="{{ old('customer_name') }}">
            @error('customer_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group">
        <label for="payment_date">Payment Date</label>
        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date') }}">
        @error('payment_date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
    </div>

    <div class="form-group">
        <label for="payment_amount">Payment Amount</label>
        <input type="number" step="0.01" name="payment_amount" id="payment_amount" class="form-control" value="{{ old('payment_amount') }}">
        @error('payment_amount')
                <div class="text-danger">{{ $message }}</div>
            @enderror
    </div>

    <div class="form-group">
    <label for="payment_method">Payment Method</label>
    <select name="payment_method" id="payment_method" class="form-control" onchange="checkPaymentMethod()">
        <option value="">Select</option>
        <option value="MPESA">MPESA</option>
        <option value="Cash">Cash</option>
        <!-- other options -->
    </select>
</div>

<script>
function checkPaymentMethod() {
    var paymentMethod = document.getElementById('payment_method').value;
    if(paymentMethod === 'MPESA') {
        var phone = prompt("Please enter your M-PESA phone number:");
        var amount = document.getElementById('payment_amount').value; // Ensure this element exists and holds the payment amount
        initiateMpesaPayment(phone, amount);
    }
}

function initiateMpesaPayment(phone, amount) {
    // Use AJAX to send a request to your STK Push initiation route
    $.post('/payments/initiate-mpesa', {phone_number: phone, amount: amount, _token: '{{ csrf_token() }}'}, function(data) {
        alert(data.message);
    });
}
</script>



    <div class="form-group">
        <label for="payment_reference">Payment Reference</label>
        <input type="text" name="payment_reference" id="payment_reference" class="form-control" value="{{ old('payment_reference') }}">
    </div>

    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Submit</button>
    </form>
</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle customer search and selection
        $('#search').on('keyup', function() {
            let query = $(this).val();
            if (query.length > 2) {
                $.ajax({
                    url: "{{ url('/search') }}",
                    type: "GET",
                    data: { query: query },
                    success: function(data) {
                        $('#results').html('');
                        if (data.length > 0) {
                            data.forEach(function(customer) {
                                $('#results').append(`
                                    <div class="result-item" 
                                         onclick="selectCustomer('${customer.customer_code}', '${customer.customer_name}')">
                                        ${customer.customer_code} - ${customer.customer_name}
                                    </div>
                                `);
                            });
                        } else {
                            $('#results').html('<div class="result-item">No results found</div>');
                        }
                    }
                });
            } else {
                $('#results').html('');
            }
        });
    });

    function selectCustomer(code, name) {
        $('#customer_code_display').val(code);
        $('#customer_code').val(code);
        $('#customer_name_display').val(name);
        $('#customer_name').val(name);
        $('#results').html('');
    }
</script>

<script>
function checkPaymentMethod() {
    var paymentMethod = document.getElementById('payment_method').value;
    if(paymentMethod === 'MPESA') {
        var phone = prompt("Please enter your M-PESA phone number:");
        var amount = document.getElementById('payment_amount').value; // Ensure this element exists and holds the payment amount
        initiateMpesaPayment(phone, amount);
    }
}

function initiateMpesaPayment(phone, amount) {
    // Use AJAX to send a request to your STK Push initiation route
    $.post('/payments/initiate-mpesa', {phone_number: phone, amount: amount, _token: '{{ csrf_token() }}'}, function(data) {
        alert(data.message);
    });
}
</script>

@endsection
