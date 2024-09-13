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
    <h1 class="my-4">Create Policy</h1>

      <!-- Policy creation form -->
    <form method="POST" action="{{ route('policies.store') }}" enctype="multipart/form-data">
    @csrf

    <!-- Client Details Section -->
    <div class="group-heading">Client Details</div>
    <div class="row">
        <!-- Client Type Selection -->
        <div class="col-md-4 form-group">
            <label>Type</label><br>
            <label><input type="radio" name="client_type" value="lead" {{ old('client_type') == 'lead' ? 'checked' : '' }} onclick="toggleClientFields()"> Lead</label>
            <label><input type="radio" name="client_type" value="customer" {{ old('client_type') == 'customer' ? 'checked' : '' }} onclick="toggleClientFields()"> Customer</label>
        </div>
    </div>
    <div class="row">
        <!-- Customer Search and Selection -->
        <div id="customerCodeField" class="col-md-3 form-group" style="display: none;">
            <label for="search"> </label>
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

    <!-- Policy Details Section -->
    <div class="group-heading mt-3">Policy Details</div>
    <div class="row mt-3">
        <div class="col-md-4 form-group">
            <label for="policy_type_id">Policy Type <span class="text-danger">*</span></label>
            <select id="policy_type_id" name="policy_type_id" class="form-control @error('policy_type_id') is-invalid @enderror" onchange="togglePolicyTypeFields()">
                <option value="">Select</option>
                <option value="35" {{ old('policy_type_id') == 35 ? 'selected' : '' }}>Motor Private</option>
                <option value="36" {{ old('policy_type_id') == 36 ? 'selected' : '' }}>Motor Commercial</option>
                <option value="37" {{ old('policy_type_id') == 37 ? 'selected' : '' }}>MotorCycle</option>
                <option value="4" {{ old('policy_type_id') == 14 ? 'selected' : '' }}>Domestic Package</option>
                <!-- Additional options -->
                <option disabled>─────────────────</option>
                @foreach($availablePolicyTypes as $policyTypeId => $policyTypeName)
                    <option value="{{ $policyTypeId }}" {{ old('policy_type_id') == $policyTypeId ? 'selected' : '' }}>{{ $policyTypeName }}</option>
                @endforeach
            </select>
            @error('policy_type_id')
                <div class="invalid-feedback"> </div>
            @enderror
        </div>

        <div class="col-md-4 form-group">
            <label for="coverage">Coverage</label>
            <select id="coverage" name="coverage" class="form-control">
                <option value="">Select</option>
                <option value="Comprehensive" {{ old('coverage') == 'Comprehensive' ? 'selected' : '' }}>Comprehensive</option>
                <option value="TPO" {{ old('coverage') == 'TPO' ? 'selected' : '' }}>TPO</option>
            </select>
 
        </div>

        <div class="col-md-4 form-group">
            <label for="insurer_id">Insurer <span class="text-danger">*</span></label>
            <select id="insurer_id" name="insurer_id" class="form-control @error('insurer_id') is-invalid @enderror">
                <option value="">Select</option>
                @foreach($insurers as $id => $name)
                    <option value="{{ $id }}" {{ old('insurer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('insurer_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3 form-group">
            <label for="policy_no">Policy No</label>
            <input type="text" id="policy_no" name="policy_no" class="form-control" value="{{ old('policy_no') }}">
         </div>

        <div class="col-md-3 form-group">
            <label for="start_date">Start Date <span class="text-danger">*</span></label>
            <input type="date" id="start_date" name="start_date" class="form-control " value="{{ old('start_date', now()->format('Y-m-d')) }}">
         </div>

        <div class="col-md-2 form-group">
            <label for="days">Days</label>
            <input type="number" id="days" name="days" class="form-control" value="{{ old('days', 365) }}">
        </div>
        <div class="col-md-3 form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}">
        </div>
    </div>

    <!-- Vehicle Details Section (Initially Hidden) -->
    <div id="vehicleDetailsSection" class="mt-3" style="display: none;">
        <div class="group-heading">Vehicle Details</div>
        <div class="row mt-2">
            <div class="col-md-3 form-group">
                <label for="reg_no">Reg No<span class="text-danger">*</span></label>
                <input type="text" id="reg_no" name="reg_no" class="form-control" value="{{ old('reg_no') }}">
 
            </div>
            <div class="col-md-3 form-group">
                <label for="vehicle_type">Make<span class="text-danger">*</span></label>
                <select id="vehicle_type" name="make" class="form-control" onchange="loadModels()">
                    <option value="">Select</option>
                    @foreach($availableVehicleTypes as $vehicleType)
                        <option value="{{ $vehicleType }}" {{ old('make') == $vehicleType ? 'selected' : '' }}>{{ $vehicleType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 form-group">
                <label for="Model">Model<span class="text-danger">*</span></label>
                <select id="Model" name="model" class="form-control">
                    <option value="">Select</option>
                </select>
             </div>

            <div class="col-md-2 form-group">
                <label for="yom">Y.O.M</label>
                <input type="number" id="yom" name="yom" class="form-control" value="{{ old('yom') }}">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-2 form-group">
                <label for="cc">CC</label>
                <input type="number" id="cc" name="cc" class="form-control" value="{{ old('cc') }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="body_type">Body Type</label>
                <select id="body_type" name="body_type" class="form-control">
                    <option value="">Select</option>
                    <option value="Sedan" {{ old('body_type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                    <option value="Hatchback" {{ old('body_type') == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                    <option value="Wagon" {{ old('body_type') == 'Wagon' ? 'selected' : '' }}>Wagon</option>
                    <option value="SUV" {{ old('body_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                </select> 
            </div>

            <div class="col-md-3 form-group">
                <label for="chassisno">Chassis No.</label>
                <input type="text" id="chassisno" name="chassisno" class="form-control @error('chassisno') is-invalid @enderror" value="{{ old('chassisno') }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="engine_no">Engine No.</label>
                <input type="text" id="engine_no" name="engine_no" class="form-control" value="{{ old('engine_no') }}">
 
            </div>
        </div>
    </div>

    <!-- Description Section (Initially Hidden) -->
    <div id="descriptionSection" class="mt-3" style="display: none;">
        <div class="group-heading">Policy Description</div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
        </div>
    </div>

    <!-- Financial Details Section -->
    <div class="group-heading mt-3">Financial Details</div>
            <div class="row mt-3">
            <div class="col-md-3 form-group">
                <label for="sum_insured_display">Sum Insured</label>
                <input type="text" id="sum_insured_display" class="form-control" value="{{ old('sum_insured', '0.00') }}" oninput="syncHiddenFields('sum_insured')">
                <input type="hidden" id="sum_insured" name="sum_insured" value="{{ old('sum_insured', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="rate_display">Rate</label>
                <input type="number" id="rate_display" class="form-control" value="{{ old('rate', '0') }}" oninput="syncHiddenFields('rate')">
                <input type="hidden" id="rate" name="rate" value="{{ old('rate', '0') }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="premium_display">Premium <span class="text-danger">*</span></label>
                <input type="text" id="premium_display" class="form-control" value="{{ old('premium') }}" readonly>
                <input type="hidden" id="premium" name="premium" value="{{ old('premium') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="c_rate_display">C. Rate</label>
                <input type="number" id="c_rate_display" class="form-control" value="{{ old('c_rate', '10') }}" oninput="syncHiddenFields('c_rate')">
                <input type="hidden" id="c_rate" name="c_rate" value="{{ old('c_rate', '10') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="commission_display">Commission</label>
                <input type="text" id="commission_display" class="form-control" value="{{ old('commission') }}" readonly>
                <input type="hidden" id="commission" name="commission" value="{{ old('commission') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="wht_display">WHT</label>
                <input type="text" id="wht_display" class="form-control" value="{{ old('wht') }}" readonly>
                <input type="hidden" id="wht" name="wht" value="{{ old('wht') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="s_duty_display">S. Duty</label>
                <input type="text" id="s_duty_display" class="form-control" value="{{ old('s_duty', '40.00') }}" readonly>
                <input type="hidden" id="s_duty" name="s_duty" value="{{ old('s_duty', '40.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="t_levy_display">T. Levy</label>
                <input type="text" id="t_levy_display" class="form-control" value="{{ old('t_levy') }}" readonly>
                <input type="hidden" id="t_levy" name="t_levy" value="{{ old('t_levy') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="pcf_levy_display">PCF Levy</label>
                <input type="text" id="pcf_levy_display" class="form-control" value="{{ old('pcf_levy') }}" readonly>
                <input type="hidden" id="pcf_levy" name="pcf_levy" value="{{ old('pcf_levy') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="policy_charge_display">Policy Charge</label>
                <input type="text" id="policy_charge_display" class="form-control" value="{{ old('policy_charge', '500.00') }}" readonly>
                <input type="hidden" id="policy_charge" name="policy_charge" value="{{ old('policy_charge', '500.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="other_charges_display">Other Charges</label>
                <input type="text" id="other_charges_display" class="form-control" value="{{ old('other_charges') }}" readonly>
                <input type="hidden" id="other_charges" name="other_charges" value="{{ old('other_charges') }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="gross_premium_display">Gross Premium</label>
                <input type="text" id="gross_premium_display" class="form-control" value="{{ old('gross_premium') }}" readonly>
                <input type="hidden" id="gross_premium" name="gross_premium" value="{{ old('gross_premium') }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="net_premium_display">Net Premium</label>
                <input type="text" id="net_premium_display" class="form-control" value="{{ old('net_premium') }}" readonly>
                <input type="hidden" id="net_premium" name="net_premium" value="{{ old('net_premium') }}">
            </div>



<!-- Cover Details Section -->
<div class="group-heading mt-3">Cover Details</div>
<div class="form-group">
    <label for="cover_details">Features</label>
    <textarea id="cover_details" name="cover_details" class="form-control">{{ old('cover_details') }}</textarea>
</div>

    <div class="row mt-4">    
        <label for="Documents">Documents</label>
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


    <!-- Submit Button -->
    <div class="form-group mt-4">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>

</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Set default values
        let today = new Date().toISOString().split('T')[0];
        $('#start_date').val(today);
        $('#days').val(365);

        // Calculate end date
        $('#end_date').val(calculateEndDate(today, 365));

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

// Calculate financial details based on inputs
function calculateFinancials() {
    let sumInsured = parseFloat(document.getElementById('sum_insured_display').value.replace(/,/g, '')) || 0.00;
    let rate = parseFloat(document.getElementById('rate_display').value) || 0;
    let c_rate = parseFloat(document.getElementById('c_rate_display').value) || 10;

    let rateDecimal = rate / 100;
    let c_rateDecimal = c_rate / 100;

    let premium = sumInsured * rateDecimal;
    let commission = premium * c_rateDecimal;
    let wht = commission * 0.05;
    let tLevy = premium * 0.002;
    let pcfLevy = premium * 0.0025;
    let policyCharge = 500.00;
    let stampDuty = 40.00;
    let grossPremium = premium + pcfLevy + tLevy + policyCharge + stampDuty;
    let netPremium = grossPremium - (commission + policyCharge) - wht;

    // Display formatted values with commas in the display fields
    document.getElementById('premium_display').value = premium.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('commission_display').value = commission.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('wht_display').value = wht.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('t_levy_display').value = tLevy.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('pcf_levy_display').value = pcfLevy.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('gross_premium_display').value = grossPremium.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('net_premium_display').value = netPremium.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    // Set unformatted values in hidden fields for submission
    document.getElementById('premium').value = premium.toFixed(2);
    document.getElementById('commission').value = commission.toFixed(2);
    document.getElementById('wht').value = wht.toFixed(2);
    document.getElementById('t_levy').value = tLevy.toFixed(2);
    document.getElementById('pcf_levy').value = pcfLevy.toFixed(2);
    document.getElementById('gross_premium').value = grossPremium.toFixed(2);
    document.getElementById('net_premium').value = netPremium.toFixed(2);
}

function syncHiddenFields(field) {
    let displayField = document.getElementById(`${field}_display`);
    let hiddenField = document.getElementById(field);

    // Remove commas and sync the hidden field
    hiddenField.value = parseFloat(displayField.value.replace(/,/g, '')).toFixed(2);
    
    // Recalculate financials
    calculateFinancials();
}


    // Format other charges on blur
    function formatOtherCharges() {
        // Implement formatting logic here...
    }
</script>


@endsection
