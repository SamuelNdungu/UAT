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

    /* Group headings */
    .group-heading {
        margin-top: 20px;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
        color: #007bff;
        font-size: 1.25rem;
    }
</style>

<div class="container">
    <h1 class="my-4">Create Invoice</h1>

    <!-- Policy creation form -->
    <form method="POST" action="{{ route('fees.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Client Details Section -->
        <div class="group-heading d-flex justify-content-between align-items-center">
            <span>Client Details</span>
        </div>
       

        <div class="row">
            <!-- Customer Search and Selection -->
            <div id="customerCodeField" class="col-md-4 form-group">
                <label for="search"> </label>
                <input type="text" id="search" class="form-control" placeholder="Search Customer" value="{{ old('search') }}">
                <div id="results" class="mt-2"></div>
            </div>

            <!-- Read-only Customer Details -->
            <div class="col-md-3 form-group">
                <label for="customer_code_display">Code</label>
                <input type="text" id="customer_code_display" class="form-control" readonly value="{{ old('customer_code') }}">
                <input type="hidden" id="customer_code" name="customer_code" value="{{ old('customer_code') }}">
            </div>

            <div class="col-md-5 form-group">
                <label for="customer_name_display">Customer Name <span class="text-danger">*</span></label>
                <input type="text" id="customer_name_display" class="form-control @error('customer_name') is-invalid @enderror" readonly value="{{ old('customer_name') }}">
                <input type="hidden" id="customer_name" name="customer_name" value="{{ old('customer_name') }}">
                @error('customer_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

         <!-- Invoice Details -->
         <div class="row">
         <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="invoice_number">Invoice Number</label>
                                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                                id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $invoice_number ?? '') }}" readonly>
                                            @error('invoice_number')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="date">Date</label>
                                            <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                                id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required onchange="updateDueDate()">
                                            @error('date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="due_date">Due Date</label>
                                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                                id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                                            @error('due_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="amount">Amount (KES)</label>
                                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                                id="amount" name="amount" value="{{ old('amount') }}" required>
                                            @error('amount')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                            @error('description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>


        <!-- Submit Button -->
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script> 

    // Toggle customer fields based on client type selection
    function toggleClientFields() {
        const clientType = document.querySelector('input[name="client_type"]:checked').value;
        document.getElementById('customerCodeField').style.display = clientType === 'customer' ? 'block' : 'none';
    }

    // Handle customer search and selection
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
                        
                        // Always add the "Add" button at the top
                        $('#results').append(`
                            <div class="result-item">
                                <a href="{{ route('customers.create') }}" class="btn btn-primary" style="margin-left: 10px; padding: 5px 10px; font-size: 0.9rem;">
                                    <i class="fas fa-plus" style="font-size: 0.65rem;"></i> Create Customer
                                </a>
                            </div>
                        `);

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
                            $('#results').append('<div class="result-item">No results found</div>');
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

 

     
    // Sync hidden fields with display fields
    function syncHiddenFields(fieldName) {
        const displayField = document.getElementById(fieldName + '_display');
        const hiddenField = document.getElementById(fieldName);
        
        // Remove commas before storing in hidden field
        const value = displayField.value.replace(/,/g, '');
        hiddenField.value = parseFloat(value).toFixed(2);
        
        // Recalculate financials
        calculateFinancials();
    }

    // Format number with commas
    function formatNumberWithCommas(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Format input field on blur
    function formatInput(input) {
        // Get the current value and cursor position
        let value = input.value.replace(/,/g, '');
        let cursorPosition = input.selectionStart;

        // Format the value with commas
        let formattedValue = formatNumberWithCommas(value);

        // Set the formatted value back to the input field
        input.value = formattedValue;

        // Restore the cursor position
        setCursor(input, cursorPosition);
    }

    // Set cursor position
    function setCursor(input, position) {
        setTimeout(() => {
            input.selectionStart = position;
            input.selectionEnd = position;
        }, 0);
    }

     

    function formatInput(input) {
        // Remove any non-numeric characters except decimal point and minus
        let value = input.value.replace(/[^\d.-]/g, '');
        value = parseFloat(value) || 0;
        // Format with commas and 2 decimal places
        input.value = formatNumberWithCommas(value.toFixed(2));
    }

    function syncHiddenFields(fieldName) {
        const displayField = document.getElementById(fieldName + '_display');
        const hiddenField = document.getElementById(fieldName);
        
        // Copy value from display to hidden field
        hiddenField.value = displayField.value;
        
        // Recalculate financials
        calculateFinancials();
    }

    // Add input event listeners to enforce numeric input
    document.addEventListener('DOMContentLoaded', function() {
        const numericInputs = document.querySelectorAll('input[type="text"]:not(#search):not(#policy_no):not(#reg_no)');
        numericInputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                // Allow only numbers, decimal point, and control keys
                if (!/[\d.]/.test(e.key) && !e.ctrlKey) {
                    e.preventDefault();
                }
                // Prevent multiple decimal points
                if (e.key === '.' && this.value.includes('.')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>

<script>
function updateDueDate() {
    const dateInput = document.getElementById('date');
    const dueDate = document.getElementById('due_date');
    if (dateInput.value) {
        const date = new Date(dateInput.value);
        date.setDate(date.getDate() + 30);
        dueDate.value = date.toISOString().split('T')[0];
    }
}
</script>

@endsection
