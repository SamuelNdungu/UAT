@extends('layouts.appPages')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<style>
    /* Body styling */
    body {
        background-color: #f4f7fa; /* Soft background color */
        font-family: 'Arial', sans-serif;
    }

    /* Container styling */
    .container {
        border-radius: 8px;
        background-color: #ffffff; /* Background color for the form */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        padding: 20px;
        margin-top: 30px;
    }

    /* Styling for search results */
    .result-item {
        padding: 10px;
        cursor: pointer;
        background-color: #f8f9fa;
        border-bottom: 1px solid #ddd;
        transition: background-color 0.2s, color 0.2s; /* Smooth transition */
    }
    .result-item:hover {
        background-color: #007bff; /* Change background on hover */
        color: #ffffff; /* Change text color on hover */
    }

    /* Form group styling */
    .form-group {
        margin-bottom: 1.5rem; /* Increased space between form groups */
    }

    .form-group label {
        font-weight: bold;
        font-size: 1.1rem; /* Slight enlarging for better readability */
        color: #333; /* Darker label color */
    }

    /* Input styling */
    .form-control {
        border-radius: 5px; /* Rounded corners */
        border: 1px solid #ced4da;
        transition: border-color 0.2s; /* Smooth transition for border */
    }

    .form-control:focus {
        border-color: #007bff; /* Border color on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Shadow for focus effect */
    }

    /* Group headings */
    .group-heading {
        margin-top: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
        color: #007bff;
        font-size: 1.5rem; /* Slightly larger font size */
    }

    /* Button styling */
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        border-radius: 5px; /* Rounded button */
        padding: 10px 20px; /* Padding for better touch area */
        transition: background-color 0.3s, border-color 0.3s; /* Smooth transition */
    }

    .btn-primary:hover {
        background-color: #0056b3; /* Darkens on hover */
        border-color: #0056b3; /* Consistency with button color */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-group {
            margin-bottom: 1rem; /* Adjusting margin for small screens */
        }

        .result-item {
            padding: 12px; /* Increased padding for touch devices */
        }
    }
</style>

<div class="container">
    <h1 class="my-4">Renew Policy</h1>

    <!-- Policy edit form -->
    <form method="POST" action="{{ route('renewals.update', $policy->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Client Details Section -->
        <div class="group-heading">Client Details</div>
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Type</label><br>
                <label><input type="radio" name="client_type" value="lead" {{ $policy->client_type == 'lead' ? 'checked' : '' }} onclick="toggleClientFields()"> Lead</label>
                <label><input type="radio" name="client_type" value="customer" {{ $policy->client_type == 'customer' ? 'checked' : '' }} onclick="toggleClientFields()"> Customer</label>
            </div>
        </div>

        <div class="row">
            <div id="customerCodeField" class="col-md-3 form-group" style="display: {{ $policy->client_type == 'customer' ? 'block' : 'none' }};">
                <label for="search">Search Customer</label>
                <input type="text" id="search" class="form-control" placeholder="Search Customer" value="{{ old('search', $policy->customer_name) }}">
                <div id="results" class="mt-2"></div>
            </div>

            <div class="col-md-2 form-group">
                <label for="fileno">File No:</label>
                <input type="text" id="fileno" class="form-control" readonly value="{{ old('fileno', $policy->fileno) }}">
            </div>
            <div class="col-md-2 form-group">
                <label for="customer_code_display">Code</label>
                <input type="text" id="customer_code_display" class="form-control" readonly value="{{ old('customer_code', $policy->customer_code) }}">
                <input type="hidden" id="customer_code" name="customer_code" value="{{ old('customer_code', $policy->customer_code) }}">
            </div>

            <div class="col-md-6 form-group">
                <label for="customer_name_display">Customer Name <span class="text-danger">*</span></label>
                <input type="text" id="customer_name_display" class="form-control @error('customer_name') is-invalid @enderror" readonly value="{{ old('customer_name', $policy->customer_name) }}">
                <input type="hidden" id="customer_name" name="customer_name" value="{{ old('customer_name', $policy->customer_name) }}">
                @error('customer_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Policy Details Section -->
        <div class="group-heading mt-3">Policy Details</div>
        <div class="row mt-3">
            <div class="col-md-4 form-group">
                <label for="policy_type_id">Policy Type <span class="text-danger">*</span></label>
                <select id="policy_type_id" name="policy_type_id" class="form-control @error('policy_type_id') is-invalid @enderror" onchange="togglePolicyTypeFields()">
                    <option value="">Select</option>
                    <option value="35" {{ $policy->policy_type_id == 35 ? 'selected' : '' }}>Motor Private</option>
                    <option value="36" {{ $policy->policy_type_id == 36 ? 'selected' : '' }}>Motor Commercial</option>
                    <option value="37" {{ $policy->policy_type_id == 37 ? 'selected' : '' }}>MotorCycle</option>
                    <option value="4" {{ $policy->policy_type_id == 14 ? 'selected' : '' }}>Domestic Package</option>
                    <!-- Additional options -->
                    <option disabled>─────────────────</option>
                    @foreach($availablePolicyTypes as $policyTypeId => $policyTypeName)
                        <option value="{{ $policyTypeId }}" {{ $policy->policy_type_id == $policyTypeId ? 'selected' : '' }}>{{ $policyTypeName }}</option>
                    @endforeach
                </select>
                @error('policy_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="coverage">Coverage</label>
                <select id="coverage" name="coverage" class="form-control">
                    <option value="">Select</option>
                    <option value="Comprehensive" {{ $policy->coverage == 'Comprehensive' ? 'selected' : '' }}>Comprehensive</option>
                    <option value="TPO" {{ $policy->coverage == 'TPO' ? 'selected' : '' }}>TPO</option>
                </select>
            </div>

            <div class="col-md-4 form-group">
                <label for="insurer_id">Insurer <span class="text-danger">*</span></label>
                <select id="insurer_id" name="insurer_id" class="form-control @error('insurer_id') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach($insurers as $id => $name)
                        <option value="{{ $id }}" {{ $policy->insurer_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                    @error('insurer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-3 form-group">
                <label for="policy_no">Policy No</label>
                <input type="text" id="policy_no" name="policy_no" class="form-control" value="{{ old('policy_no', $policy->policy_no) }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', $policy->start_date->format('Y-m-d')) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="days">Days</label>
                <input type="number" id="days" name="days" class="form-control" value="{{ old('days', $policy->days) }}">
            </div>
            <div class="col-md-3 form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date', $policy->end_date ? $policy->end_date->format('Y-m-d') : '') }}">
            </div>
        </div>

        <!-- Vehicle Details Section (Initially Hidden) -->
        <div id="vehicleDetailsSection" class="mt-3" style="display: {{ in_array($policy->policy_type_id, [35, 36, 37]) ? 'block' : 'none' }};">
            <div class="group-heading">Vehicle Details</div>
            <div class="row mt-2">
                <div class="col-md-3 form-group">
                    <label for="reg_no">Reg No <span class="text-danger">*</span></label>
                    <input type="text" id="reg_no" name="reg_no" class="form-control" value="{{ old('reg_no', $policy->reg_no) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label for="vehicle_type">Make <span class="text-danger">*</span></label>
                    <select id="vehicle_type" name="make" class="form-control" onchange="loadModels()">
                        <option value="">Select</option>
                        @foreach($availableVehicleTypes as $vehicleType)
                            <option value="{{ $vehicleType }}" {{ old('make', $policy->make) == $vehicleType ? 'selected' : '' }}>{{ $vehicleType }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label for="Model">Model <span class="text-danger">*</span></label>
                    <select id="Model" name="model" class="form-control">
                        <option value="">Select</option>
                        @if($policy->make)
                            @foreach($vehicleModels as $model)
                                @if($model->make === $policy->make)
                                    <option value="{{ $model->model }}" {{ old('model', $policy->model) == $model->model ? 'selected' : '' }}>
                                        {{ $model->model }}
                                    </option>
                                @endif
                            @endforeach

                            @if($vehicleModels->isEmpty())
                                <option value="" disabled>No models available for this make</option>
                            @endif
                        @endif
                    </select>
                </div>

                <div class="col-md-2 form-group">
                    <label for="yom">Y.O.M</label>
                    <input type="number" id="yom" name="yom" class="form-control" value="{{ old('yom', $policy->yom) }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-2 form-group">
                    <label for="cc">CC</label>
                    <input type="number" id="cc" name="cc" class="form-control" value="{{ old('cc', $policy->cc) }}">
                </div>

                <div class="col-md-3 form-group">
                    <label for="body_type">Body Type</label>
                    <select id="body_type" name="body_type" class="form-control">
                        <option value="">Select</option>
                        <option value="Sedan" {{ old('body_type', $policy->body_type) == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                        <option value="Hatchback" {{ old('body_type', $policy->body_type) == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                        <option value="Wagon" {{ old('body_type', $policy->body_type) == 'Wagon' ? 'selected' : '' }}>Wagon</option>
                        <option value="SUV" {{ old('body_type', $policy->body_type) == 'SUV' ? 'selected' : '' }}>SUV</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label for="chassisno">Chassis No.</label>
                    <input type="text" id="chassisno" name="chassisno" class="form-control @error('chassisno') is-invalid @enderror" value="{{ old('chassisno', $policy->chassisno) }}" oninput="this.value = this.value.toUpperCase()">
                </div>

                <div class="col-md-3 form-group">
                    <label for="engine_no">Engine No.</label>
                    <input type="text" id="engine_no" name="engine_no" class="form-control" value="{{ old('engine_no', $policy->engine_no) }}" oninput="this.value = this.value.toUpperCase()">
                </div>
            </div>
        </div>

        <!-- Description Section (Initially Hidden) -->
        <div id="descriptionSection" class="mt-3" style="display: {{ !in_array($policy->policy_type_id, [35, 36, 37]) ? 'block' : 'none' }};">
            <div class="group-heading">Policy Description</div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control">{{ old('description', $policy->description) }}</textarea>
            </div>
        </div>

        <!-- Financial Details Section -->
        <div class="group-heading mt-3">Financial Details</div>
        <div class="row mt-3">
            <div class="col-md-3 form-group">
                <label for="sum_insured_display">Sum Insured</label>
                <input type="text" id="sum_insured_display" class="form-control" value="{{ old('sum_insured', $policy->sum_insured) }}" oninput="syncHiddenFields('sum_insured')">
                <input type="hidden" id="sum_insured" name="sum_insured" value="{{ old('sum_insured', $policy->sum_insured) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="rate_display">Rate</label>
                <input type="number" id="rate_display" class="form-control" value="{{ old('rate', $policy->rate) }}" oninput="syncHiddenFields('rate')">
                <input type="hidden" id="rate" name="rate" value="{{ old('rate', $policy->rate) }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="premium_display">Premium <span class="text-danger">*</span></label>
                <input type="text" id="premium_display" class="form-control" value="{{ old('premium', $policy->premium) }}" readonly>
                <input type="hidden" id="premium" name="premium" value="{{ old('premium', $policy->premium) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="c_rate_display">C. Rate</label>
                <input type="number" id="c_rate_display" class="form-control" value="{{ old('c_rate', $policy->c_rate) }}" oninput="syncHiddenFields('c_rate')">
                <input type="hidden" id="c_rate" name="c_rate" value="{{ old('c_rate', $policy->c_rate) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="commission_display">Commission</label>
                <input type="text" id="commission_display" class="form-control" value="{{ old('commission', $policy->commission) }}" readonly>
                <input type="hidden" id="commission" name="commission" value="{{ old('commission', $policy->commission) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="wht_display">WHT</label>
                <input type="text" id="wht_display" class="form-control" value="{{ old('wht', $policy->wht) }}" readonly>
                <input type="hidden" id="wht" name="wht" value="{{ old('wht', $policy->wht) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="s_duty_display">S. Duty</label>
                <input type="text" id="s_duty_display" class="form-control" value="{{ old('s_duty', $policy->s_duty) }}" readonly>
                <input type="hidden" id="s_duty" name="s_duty" value="{{ old('s_duty', $policy->s_duty) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="t_levy_display">T. Levy</label>
                <input type="text" id="t_levy_display" class="form-control" value="{{ old('t_levy', $policy->t_levy) }}" readonly>
                <input type="hidden" id="t_levy" name="t_levy" value="{{ old('t_levy', $policy->t_levy) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="pcf_levy_display">PCF Levy</label>
                <input type="text" id="pcf_levy_display" class="form-control" value="{{ old('pcf_levy', $policy->pcf_levy) }}" readonly>
                <input type="hidden" id="pcf_levy" name="pcf_levy" value="{{ old('pcf_levy', $policy->pcf_levy) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="policy_charge_display">Policy Charge</label>
                <input type="text" id="policy_charge_display" class="form-control" value="{{ old('policy_charge', $policy->policy_charge) }}" readonly>
                <input type="hidden" id="policy_charge" name="policy_charge" value="{{ old('policy_charge', $policy->policy_charge) }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="other_charges_display">Other Charges</label>
                <input type="text" id="other_charges_display" class="form-control" value="{{ old('other_charges', $policy->other_charges) }}" readonly>
                <input type="hidden" id="other_charges" name="other_charges" value="{{ old('other_charges', $policy->other_charges) }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="gross_premium_display">Gross Premium</label>
                <input type="text" id="gross_premium_display" class="form-control" value="{{ old('gross_premium', $policy->gross_premium) }}" readonly>
                <input type="hidden" id="gross_premium" name="gross_premium" value="{{ old('gross_premium', $policy->gross_premium) }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="net_premium_display">Net Premium</label>
                <input type="text" id="net_premium_display" class="form-control" value="{{ old('net_premium', $policy->net_premium) }}" readonly>
                <input type="hidden" id="net_premium" name="net_premium" value="{{ old('net_premium', $policy->net_premium) }}">
            </div>
        </div>

        <!-- Cover Details Section -->
        <div class="group-heading mt-3">Cover Details</div>
        <div class="form-group">
            <label for="cover_details">Features</label>
            <textarea id="cover_details" name="cover_details" class="form-control">{{ old('cover_details', $policy->cover_details) }}</textarea>
        </div>

        <!-- Document Section -->
        <div class="group-heading mt-3">Documents</div>
        <div class="row">
            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Document Description</th>
                            <th>Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <input type="text" id="document_description" name="document_description" class="form-control" placeholder="Enter description" value="{{ old('document_description', $policy->document_description) }}">
                            </td>
                            <td>
                                <input type="file" id="upload_file" name="upload_file" class="form-control">
                                @if($policy->documents)
                                    <small>Current file: <a href="{{ asset('storage/uploads/' . $policy->documents) }}" download>{{ $policy->documents }}</a></small>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">Renew</button>
        </div>
    </form>
</div>

<!-- JavaScript Handlers -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Set default values
        let startDate = $('#start_date').val();
        let days = $('#days').val();

        // Calculate end date
        $('#end_date').val(calculateEndDate(startDate, days));

        // Update end date when start date or days change
        $('#start_date, #days').on('change', function() {
            let startDate = $('#start_date').val();
            let days = $('#days').val();
            $('#end_date').val(calculateEndDate(startDate, days));
        });

        // Hide/show sections based on policy type
        togglePolicyTypeFields();
    });

    function calculateEndDate(startDate, days) {
        let date = new Date(startDate);
        date.setDate(date.getDate() + parseInt(days));
        return date.toISOString().split('T')[0];
    }

    // Toggle customer fields based on client type selection
function toggleClientFields() {
    const clientType = document.querySelector('input[name="client_type"]:checked')?.value; // Optional chaining in case no radio buttons are selected
    const customerCodeField = document.getElementById('customerCodeField');
    customerCodeField.style.display = clientType === 'customer' ? 'block' : 'none'; // Show or hide the field based on selection
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
                    $('#results').empty(); // Clear previous results
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
                        $('#results').html('<div class="result-item">No results found</div>'); // Handle no results found
                    }
                },
                error: function() {
                    $('#results').html('<div class="result-item text-danger">Error fetching data. Please try again.</div>');
                }
            });
        } else {
            $('#results').empty(); // Clear results when query length is less than 3
        }
    });
});

// Function to select a customer and populate fields
function selectCustomer(code, name) {
    $('#customer_code_display').val(code);
    $('#customer_code').val(code);
    $('#customer_name_display').val(name);
    $('#customer_name').val(name);
    $('#results').empty(); // Clear results after selection
}


    function togglePolicyTypeFields() {
        const policyTypeId = $('#policy_type_id').val();
        const vehicleDetailsSection = document.getElementById('vehicleDetailsSection');
        const descriptionSection = document.getElementById('descriptionSection');

        if (policyTypeId == '35' || policyTypeId == '36' || policyTypeId == '37') {
            vehicleDetailsSection.style.display = 'block';
            descriptionSection.style.display = 'none';
        } else {
            vehicleDetailsSection.style.display = 'none';
            descriptionSection.style.display = 'block';
        }
    }

    // Load vehicle models based on selected vehicle type
    const availableModels = @json($vehicleModels);
    function loadModels() {
        const make = document.getElementById('vehicle_type').value;
        const modelDropdown = document.getElementById('Model');

        modelDropdown.innerHTML = '<option value="">Select</option>';
        if (make) {
            const filteredModels = availableModels.filter(model => model.make === make);
            filteredModels.forEach(model => {
                const option = document.createElement('option');
                option.value = model.model;
                option.text = model.model;
                modelDropdown.add(option);
            });
        }
    }

    // Synchronize hidden fields with their display counterparts
    function syncHiddenFields(field) {
        let displayField = document.getElementById(`${field}_display`);
        let hiddenField = document.getElementById(field);

        hiddenField.value = parseFloat(displayField.value.replace(/,/g, '')).toFixed(2);

        // Optionally, you could also call other functions if needed for recalculation
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@endsection
