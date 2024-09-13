@extends('layouts.appPages')

@section('content')
<style>
    .result-item {
        padding: 5px;
        border-bottom: 1px solid #ccc;
        cursor: pointer;
    }

    .result-item:hover {
        background-color: #f0f0f0;
    }

    .form-label.required::after {
        content: " *";
        color: red;
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
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>New Claim</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Search Policy -->
                <div class="mb-4">
                    <input type="text" id="search" class="form-control" placeholder="Search by File No, Policy Type, or Reg No" value="{{ old('search') }}">
                    <div id="results" class="mt-2"></div>
                </div>

                <!-- Policy Details Section -->
                <div class="group-heading mt-3">Policy Details</div>
                <div id="policy-details" class="mb-4">
                    <!-- Fetched data will be populated here -->
                </div>

                <!-- Hidden fields to store fetched data -->
                <input type="hidden" id="fileno" name="fileno" value="{{ old('fileno') }}">
                <input type="hidden" id="customer_code" name="customer_code" value="{{ old('customer_code') }}">
                <input type="hidden" id="customer_name" name="customer_name" value="{{ old('customer_name') }}">
                <input type="hidden" id="policy_type" name="policy_type" value="{{ old('policy_type') }}">
                <input type="hidden" id="reg_no" name="reg_no" value="{{ old('reg_no') }}">
                <input type="hidden" id="make" name="make" value="{{ old('make') }}">
                <input type="hidden" id="model" name="model" value="{{ old('model') }}">
                <input type="hidden" id="description" name="description" value="{{ old('description') }}">
                <input type="hidden" id="start_date" name="start_date" value="{{ old('start_date') }}">
                <input type="hidden" id="end_date" name="end_date" value="{{ old('end_date') }}">
                <input type="hidden" id="insurer" name="insurer" value="{{ old('insurer') }}">
                <input type="hidden" id="sum_insured" name="sum_insured" value="{{ old('sum_insured') }}">
                <input type="hidden" id="gross_premium" name="gross_premium" value="{{ old('gross_premium') }}">
                <input type="hidden" id="paid_amount" name="paid_amount" value="{{ old('paid_amount') }}">
                <input type="hidden" id="due_amount" name="due_amount" value="{{ old('due_amount') }}">

                <!-- Claim Details Section -->
                <div class="group-heading mt-3">Claims Details</div>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="claim_number" class="form-label">Claim No</label>
                            <input type="text" name="claim_number" id="claim_number" class="form-control" value="{{ $newClaimNumber }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="reported_date" class="form-label required">Reported Date</label>
                            <input type="date" name="reported_date" id="reported_date" class="form-control" value="{{ old('reported_date') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="type_of_loss" class="form-label required">Type of Loss</label>
                            <input type="text" name="type_of_loss" id="type_of_loss" class="form-control" value="{{ old('type_of_loss') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="loss_date" class="form-label required">Loss Date</label>
                            <input type="date" name="loss_date" id="loss_date" class="form-control" value="{{ old('loss_date') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="loss_details" class="form-label required">Loss Details</label>
                            <textarea name="loss_details" id="loss_details" class="form-control" required>{{ old('loss_details') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="followup_date" class="form-label">Follow-up Date</label>
                            <input type="date" name="followup_date" id="followup_date" class="form-control" value="{{ old('followup_date') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="claimant_name" class="form-label required">Claimant Name</label>
                            <input type="text" name="claimant_name" id="claimant_name" class="form-control" value="{{ old('claimant_name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="amount_claimed" class="form-label required">Amount Claimed</label>
                            <input type="number" step="0.01" name="amount_claimed" id="amount_claimed" class="form-control" value="{{ old('amount_claimed') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="amount_paid" class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" value="{{ old('amount_paid') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="Open" {{ old('status') == 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="Closed" {{ old('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Document Upload Section -->
                <div class="row mt-4">
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label for="Documents" class="form-label">Documents</label>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th>Document</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <input type="text" id="document_description" name="document_description" class="form-control" placeholder="Enter description">
                                        </td>
                                        <td>
                                            <input type="file" id="upload_file" name="upload_file" class="form-control">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Submit Claim</button>
            </form>
        </div>
    </div>
</div>

<script>
console.log("JavaScript is running");

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('search').addEventListener('input', function() {
        const searchQuery = this.value;
        const resultsDiv = document.getElementById('results');

        if (searchQuery.length > 2) { // Trigger search after 3 characters
            fetch(`{{ url('/api/search-policies') }}?search=${searchQuery}`)
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML = ''; // Clear previous results

                    if (data.length > 0) {
                        data.forEach(policy => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'result-item';
                            resultItem.innerHTML = `
                                ${policy.fileno} - ${policy.customer_name}, ${policy.policy_type}, ${policy.reg_no}
                            `;
                            resultItem.addEventListener('click', function() {
                                // When a fileno is clicked, fetch the full details
                                fetchPolicyDetails(policy.fileno);
                            });
                            resultsDiv.appendChild(resultItem);
                        });
                    } else {
                        resultsDiv.innerHTML = `<p>No policies found matching "${searchQuery}"</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching policies:', error);
                    resultsDiv.innerHTML = '<p class="text-danger">Error fetching policies. Please try again later.</p>';
                });
        } else {
            resultsDiv.innerHTML = ''; // Clear results if the search query is too short
        }
    });

    function fetchPolicyDetails(fileno) {
        fetch(`{{ url('/api/get-policy-details') }}?fileno=${fileno}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // Populate hidden fields
                    document.getElementById('fileno').value = data.fileno;
                    document.getElementById('customer_code').value = data.customer_code;
                    document.getElementById('customer_name').value = data.customer_name;
                    document.getElementById('policy_type').value = data.policy_type;
                    document.getElementById('reg_no').value = data.reg_no;
                    document.getElementById('make').value = data.make;
                    document.getElementById('model').value = data.model;
                    document.getElementById('description').value = data.description;
                    document.getElementById('start_date').value = data.start_date;
                    document.getElementById('end_date').value = data.end_date;
                    document.getElementById('insurer').value = data.insurer;
                    document.getElementById('sum_insured').value = parseFloat(data.sum_insured).toLocaleString();
                    document.getElementById('gross_premium').value = parseFloat(data.gross_premium).toLocaleString();
                    document.getElementById('paid_amount').value = parseFloat(data.paid_amount).toLocaleString();
                    document.getElementById('due_amount').value = parseFloat(data.due_amount).toLocaleString();

                    // Populate the policy details section
                    document.getElementById('policy-details').innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${data.customer_name} - ${data.phone} - ${data.email}</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Policy Type:</strong> ${data.policy_type}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>File No:</strong> ${data.fileno}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Reg No:</strong> ${data.reg_no}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Make:</strong> ${data.make}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Model:</strong> ${data.model}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Description:</strong> ${data.description}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Start Date:</strong> ${data.start_date}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>End Date:</strong> ${data.end_date}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Insurer:</strong> ${data.insurer}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Sum Insured:</strong> ${parseFloat(data.sum_insured).toLocaleString()}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Gross Premium:</strong> ${parseFloat(data.gross_premium).toLocaleString()}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Paid Amount:</strong> ${parseFloat(data.paid_amount).toLocaleString()}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text"><strong>Due Amount:</strong> ${parseFloat(data.due_amount).toLocaleString()}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Hide the results div after selection
                    document.getElementById('results').innerHTML = '';
                } else {
                    document.getElementById('policy-details').innerHTML = `<p>No policy found with File No: ${fileno}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching policy details:', error);
                document.getElementById('policy-details').innerHTML = '<p class="text-danger">Error fetching policy details. Please try again later.</p>';
            });
    }
});
</script>
@endsection
