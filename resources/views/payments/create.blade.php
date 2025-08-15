@extends('layouts.appPages')

@section('content')

<style>
    /* General styles for the container */
    .container {
        max-width: 800px;
        margin: auto;
        padding: 20px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        margin-bottom: 30px;
        color: #343a40;
    }

    /* Styling for search results */
    .result-item {
        padding: 10px;
        cursor: pointer;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 5px;
    }
    .result-item:hover {
        background-color: #e9ecef;
        color: #0056b3;
    }

    /* Form styling for a modern look */
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
    
    .form-control, .btn {
        border-radius: 5px;
    }

    .btn-success {
        background-color: #007bff;
        border-color: #007bff;
    }

    .header-title {
        text-align: center;
        margin: 30px 0;
        font-size: 2.5rem;
        font-weight: 700;
        color: #343a40;
        position: relative;
        padding: 10px;
        background: #dc3545;
        color: #ffffff;
        border-radius: 5px;
        text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
    }

    .header-title i {
        margin-right: 10px;
    }

    /* MPESA Modal Styles */
    .mpesa-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .mpesa-modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 8px;
    }
</style>

<div class="container">
    <h1 class="header-title">
        <i class="fas fa-money-check-alt"></i> Add Payment
    </h1>

    <!-- Payment creation form -->
    <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
        @csrf

        <div class="row">
            <!-- Customer Search and Selection -->
            <div id="customerCodeField" class="col-md-4 form-group">
                <label for="search">Search Customer</label>
                <input type="text" id="search" class="form-control" placeholder="Search Customer" value="{{ old('search') }}">
                <div id="results" class="mt-2"></div>
            </div>
       
            <!-- Read-only Customer Details -->
            <div class="col-md-2 form-group">
                <label for="customer_code_display">Code</label>
                <input type="text" id="customer_code_display" class="form-control" readonly value="{{ old('customer_code') }}">
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

        <div class="row">
        <div class="col-md-4 form-group">
            <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
            <input type="date" name="payment_date" id="payment_date" 
                class="form-control @error('payment_date') is-invalid @enderror" 
                value="{{ old('payment_date', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
            @error('payment_date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>



        <div class="col-md-4 form-group">
    <label for="payment_amount">Payment Amount <span class="text-danger">*</span></label>
    <input type="text" id="payment_amount" 
           class="form-control @error('payment_amount') is-invalid @enderror" 
           value="{{ old('payment_amount') }}" required 
           oninput="formatAmount(this, 'payment_amount_hidden')">
    
    <input type="hidden" name="payment_amount" id="payment_amount_hidden" value="{{ old('payment_amount') }}" required>
    @error('payment_amount')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
 
            <div class="col-md-4 form-group">
                <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required onchange="checkPaymentMethod()">
                    <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="MPESA" {{ old('payment_method') == 'MPESA' ? 'selected' : '' }}>MPESA</option>
                    <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                     
                </select>
                @error('payment_method')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="payment_reference">Payment Reference <span class="text-danger">*</span></label>
                <input type="text" name="payment_reference" id="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" value="{{ old('payment_reference') }}" required>
                @error('payment_reference')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-2 form-group">
                <button type="submit" class="btn btn-success btn-block">Submit</button>
            </div>
        </div>
    </form>
</div>


<!-- MPESA Modal -->
<div id="mpesaModal" class="mpesa-modal">
    <div class="mpesa-modal-content">
        <h4>MPESA Payment</h4>
        <div class="form-group">
            <label for="mpesa_phone">Phone Number (Format: 254XXXXXXXXX)</label>
            <input type="text" id="mpesa_phone" class="form-control" placeholder="254712345678">
        </div>
        <div class="mt-3">
            <button onclick="initiateSTKPush()" class="btn btn-primary">Pay with MPESA</button>
            <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
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

    function checkPaymentMethod() {
        var paymentMethod = document.getElementById('payment_method').value;
        if(paymentMethod === 'MPESA') {
            document.getElementById('mpesaModal').style.display = 'block';
        }
    }

    function closeModal() {
        document.getElementById('mpesaModal').style.display = 'none';
        document.getElementById('payment_method').value = '';
    }

    function initiateSTKPush() {
        const phone = document.getElementById('mpesa_phone').value;
        const amount = document.getElementById('payment_amount').value;
        const payment_date = document.getElementById('payment_date').value;
        const reference = document.getElementById('customer_code').value || 'PAYMENT';

        if (!phone || !amount) {
            alert('Please enter both phone number and amount');
            return;
        }

        $.ajax({
            url: '/api/mpesa/initiate',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                phone: phone,
                amount: amount,
                reference: reference,
                payment_date: payment_date,
            }),
            success: function(response) {
                if (response.status === 'success') {
                    alert('Please check your phone for the MPESA prompt');
                    document.getElementById('mpesaModal').style.display = 'none';
                    document.getElementById('payment_reference').value = 'MPESA Pending - ' + reference;
                } else {

                    console.log(response);
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error initiating MPESA payment. Please try again.');
                console.error(xhr);
            }
        });
    }
</script>
<script>
    function formatAmount(input, hidden_input_id='') {
        if(hidden_input_id!=''){
            hidden_value = input.value;
            document.getElementById(hidden_input_id).value = hidden_value.replace(',','');
        }
        // Remove any non-numeric character (except for decimal point)
        let value = input.value.replace(/[^\d.]/g, '');

        // Convert to float for proper handling
        let floatValue = parseFloat(value);
        
        // Format to include commas
        if (!isNaN(floatValue)) {
            input.value = floatValue.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        } else {
            input.value = '';
        }
    }
</script>
@endsection