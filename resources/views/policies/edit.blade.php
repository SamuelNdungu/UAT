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
    <h1 class="my-4">Edit Policy</h1>

    <!-- Policy edit form -->
    <form method="POST" action="{{ route('policies.update', $policy->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- Client Details Section -->
    <div class="group-heading">Client Details</div>
    <div class="row">
        <div class="col-md-2 form-group">
            <label for="fileno">File No</label>
            <input type="text" id="fileno" name="fileno" class="form-control" readonly value="{{ old('fileno', $policy->fileno) }}">
        </div>
        <!-- Read-only Customer Details -->
        <div class="col-md-2 form-group">
            <label for="customer_code_display">Code</label>
            <input type="text" id="customer_code_display" class="form-control" readonly value="{{ old('customer_code', $policy->customer_code) }}">
            <input type="hidden" id="customer_code" name="customer_code" value="{{ old('customer_code', $policy->customer_code) }}">
        </div>

        <div class="col-md-4 form-group">
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
                <option value="14" {{ $policy->policy_type_id == 14 ? 'selected' : '' }}>Domestic Package</option>
                <!-- Additional options -->
                <option disabled>─────────────────</option>
                @foreach($availablePolicyTypes as $policyTypeId => $policyTypeName)
                    <option value="{{ $policyTypeId }}" {{ $policy->policy_type_id == $policyTypeId ? 'selected' : '' }}>{{ $policyTypeName }}</option>
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
            <input type="date" id="start_date" name="start_date" class="form-control " value="{{ old('start_date', $policy->start_date->format('Y-m-d')) }}">
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
                <label for="reg_no">Reg No<span class="text-danger">*</span></label>
                <input type="text" id="reg_no" name="reg_no" class="form-control" value="{{ old('reg_no', $policy->reg_no) }}">
            </div>
            <div class="col-md-3 form-group">
                <label for="vehicle_type">Make<span class="text-danger">*</span></label>
                <select id="vehicle_type" name="make" class="form-control" onchange="loadModels()">
                    <option value="">Select</option>
                    @foreach($availableVehicleTypes as $vehicleType)
                        <option value="{{ $vehicleType }}" {{ old('make', $policy->make) == $vehicleType ? 'selected' : '' }}>{{ $vehicleType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 form-group">
                <label for="Model">Model<span class="text-danger">*</span></label>
                <select id="Model" name="model" class="form-control">
                    <option value="">Select</option>
                    @if($policy->make)
                        @foreach($vehicleModels as $model)
                            @if($model->make === $policy->make)
                                <option value="{{ $model->model }}" {{ old('model', $policy->model) == $model->model ? 'selected' : '' }}>{{ $model->model }}</option>
                            @endif
                        @endforeach

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
            <input type="text" id="sum_insured_display" class="form-control" value="{{ old('sum_insured', $policy->sum_insured) }}" 
                oninput="syncHiddenFields('sum_insured'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="sum_insured" name="sum_insured" value="{{ old('sum_insured', $policy->sum_insured) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="premium_type">Premium Type</label>
            <select id="premium_type" name="premium_type" class="form-control" onchange="togglePremiumFields()">
                <option value="rate">Rate</option>
                <option value="premium">Premium</option>
            </select>
        </div>

        <div class="col-md-2 form-group">
            <label for="rate_display">Rate</label>
            <input type="text" id="rate_display" class="form-control" value="{{ old('rate', $policy->rate) }}" 
                oninput="syncHiddenFields('rate'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="rate" name="rate" value="{{ old('rate', $policy->rate) }}">
        </div>

        <div class="col-md-3 form-group">
            <label for="premium_display">Premium <span class="text-danger">*</span></label>
            <input type="text" id="premium_display" class="form-control" value="{{ old('premium', $policy->premium) }}" 
                oninput="syncHiddenFields('premium'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="premium" name="premium" value="{{ old('premium', $policy->premium) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="c_rate_display">Commission Rate</label>
            <input type="text" id="c_rate_display" class="form-control" value="{{ old('c_rate', $policy->c_rate) }}" 
                oninput="syncHiddenFields('c_rate'); calculateFinancials()" 
                onfocusout="formatInput(this)">
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
            <label for="t_levy_display">Training Levy</label>
            <input type="text" id="t_levy_display" class="form-control" value="{{ old('t_levy', $policy->t_levy) }}" readonly>
            <input type="hidden" id="t_levy" name="t_levy" value="{{ old('t_levy', $policy->t_levy) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="pcf_levy_display">PCF Levy</label>
            <input type="text" id="pcf_levy_display" class="form-control" value="{{ old('pcf_levy', $policy->pcf_levy) }}" readonly>
            <input type="hidden" id="pcf_levy" name="pcf_levy" value="{{ old('pcf_levy', $policy->pcf_levy) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="s_duty_display">Stamp Duty</label>
            <input type="text" id="s_duty_display" class="form-control" value="{{ old('s_duty', $policy->s_duty) }}" 
                oninput="syncHiddenFields('s_duty'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="s_duty" name="s_duty" value="{{ old('s_duty', $policy->s_duty) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="policy_charge_display">Policy Charge</label>
            <input type="text" id="policy_charge_display" class="form-control" value="{{ old('policy_charge', $policy->policy_charge) }}" 
                oninput="syncHiddenFields('policy_charge'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="policy_charge" name="policy_charge" value="{{ old('policy_charge', $policy->policy_charge) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="pvt_display">PVT</label>
            <input type="text" id="pvt_display" class="form-control" value="{{ old('pvt', $policy->pvt) }}" 
                oninput="syncHiddenFields('pvt'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="pvt" name="pvt" value="{{ old('pvt', $policy->pvt) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="excess_display">Excess</label>
            <input type="text" id="excess_display" class="form-control" value="{{ old('excess', $policy->excess) }}" 
                oninput="syncHiddenFields('excess'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="excess" name="excess" value="{{ old('excess', $policy->excess) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="other_charges_display">Other Charges</label>
            <input type="text" id="other_charges_display" class="form-control" value="{{ old('other_charges', $policy->other_charges) }}" 
                oninput="syncHiddenFields('other_charges'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="other_charges" name="other_charges" value="{{ old('other_charges', $policy->other_charges) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="gross_premium_display">Gross Premium</label>
            <input type="text" id="gross_premium_display" class="form-control" value="{{ old('gross_premium', $policy->gross_premium) }}" readonly>
            <input type="hidden" id="gross_premium" name="gross_premium" value="{{ old('gross_premium', $policy->gross_premium) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="net_premium_display">Net Premium</label>
            <input type="text" id="net_premium_display" class="form-control" value="{{ old('net_premium', $policy->net_premium) }}" readonly>
            <input type="hidden" id="net_premium" name="net_premium" value="{{ old('net_premium', $policy->net_premium) }}">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-2 form-group">
            <label for="courtesy_car_display">Courtesy Car</label>
            <input type="text" id="courtesy_car_display" class="form-control" value="{{ old('courtesy_car', $policy->courtesy_car) }}" 
                oninput="syncHiddenFields('courtesy_car'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="courtesy_car" name="courtesy_car" value="{{ old('courtesy_car', $policy->courtesy_car) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="ppl_display">PPL</label>
            <input type="text" id="ppl_display" class="form-control" value="{{ old('ppl', $policy->ppl) }}" 
                oninput="syncHiddenFields('ppl'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="ppl" name="ppl" value="{{ old('ppl', $policy->ppl) }}">
        </div>

        <div class="col-md-2 form-group">
            <label for="road_rescue_display">Road Rescue</label>
            <input type="text" id="road_rescue_display" class="form-control" value="{{ old('road_rescue', $policy->road_rescue) }}" 
                oninput="syncHiddenFields('road_rescue'); calculateFinancials()" 
                onfocusout="formatInput(this)">
            <input type="hidden" id="road_rescue" name="road_rescue" value="{{ old('road_rescue', $policy->road_rescue) }}">
        </div>
    </div>

    <!-- Cover Details Section -->
    <div class="group-heading mt-3">Cover Details</div>
    <div class="form-group">
        <label for="cover_details">Features</label>
        <textarea id="cover_details" name="cover_details" class="form-control" style="height: 200px; width: 100%;">{{ old('cover_details', $policy->cover_details) }}</textarea>
    </div>

    <!-- Documents Section -->
<div class="group-heading mt-3">Documents</div>
<div class="row mt-4">
    <div class="col-12">
        <table class="table table-bordered" id="documentsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Document Description</th>
                    <th>Document</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($documents)
                    @foreach($documents as $key => $document)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <input type="text" name="document_description[]" class="form-control" placeholder="Enter description" value="{{ $document['description'] }}">
                            </td>
                            <td>
                                <input type="file" name="upload_file[]" class="form-control">
                                @if($document['path'])
                                    <a href="{{ asset('storage/' . $document['path']) }}" target="_blank">View Document</a>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger" onclick="removeDocumentRow(this)">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                @endif
                <tr id="documentRowTemplate" style="display: none;">
                    <td>1</td>
                    <td>
                        <input type="text" name="document_description[]" class="form-control" placeholder="Enter description">
                    </td>
                    <td>
                        <input type="file" name="upload_file[]" class="form-control">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger" onclick="removeDocumentRow(this)">Remove</button>
                    </td>
                </tr>
                <!-- Additional rows will be added here -->
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary" onclick="addDocumentRow()">Add Document</button>
    </div>
</div>


    <!-- Submit Button -->
    <div class="form-group mt-4">
        <button type="submit" class="btn btn-primary">Update Policy</button>
    </div>
    </form>
</div>

<!-- JS Scripts -->
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

    // Format number with commas
    function formatNumberWithCommas(number) {
        return number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Format input field on blur
function formatInput(input) {
    // Remove any non-numeric characters except decimal and remove commas
    let value = input.value.replace(/,/g, '').replace(/[^\d.]/g, '');
    if (value) {
        let number = parseFloat(value);
        if (!isNaN(number)) {
            let formattedValue = formatNumberWithCommas(number);
            input.value = formattedValue;
        } else {
            input.value = '0.00';
        }
    } else {
        input.value = '0.00';
    }
}
    

    // Allow only numbers and decimal point
    function validateNumericInput(event) {
        // Allow: backspace, delete, tab, escape, enter, decimal point
        if (event.key === '.' && event.target.value.includes('.')) {
            event.preventDefault();
            return;
        }
        
        if (event.key === '.' || event.key === 'Backspace' || event.key === 'Delete' || 
            event.key === 'Tab' || event.key === 'Escape' || event.key === 'Enter' ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (event.key === 'a' && event.ctrlKey === true) ||
            (event.key === 'c' && event.ctrlKey === true) ||
            (event.key === 'v' && event.ctrlKey === true) ||
            (event.key === 'x' && event.ctrlKey === true)) {
            return;
        }

        // Ensure that it is a number and stop the keypress if not
        if ((event.key < '0' || event.key > '9') && event.key !== '.') {
            event.preventDefault();
        }
    }

    // Handle paste event to only allow numbers
    function handlePaste(event) {
        const clipboardData = event.clipboardData || window.clipboardData;
        const pastedData = clipboardData.getData('Text');
        
        if (!/^\d*\.?\d*$/.test(pastedData)) {
            event.preventDefault();
        }
    }

    // Add event listeners to all numeric input fields
    document.addEventListener('DOMContentLoaded', function() {
        const numericFields = [
            'sum_insured_display',
            'rate_display',
            'premium_display',
            'c_rate_display',
            'policy_charge_display',
            'pvt_display',
            'excess_display',
            'other_charges_display',
            'courtesy_car_display',
            'ppl_display',
            'road_rescue_display',
            's_duty_display'
        ];

        numericFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('keypress', validateNumericInput);
                field.addEventListener('paste', handlePaste);
                // Prevent dropping of text
                field.addEventListener('drop', e => e.preventDefault());
            }
        });
    });

    // Sync hidden fields with display fields
function syncHiddenFields(field) {
    let displayField = document.getElementById(`${field}_display`);
    let hiddenField = document.getElementById(field);
    let value = parseFloat(displayField.value.replace(/,/g, ''));
    if (isNaN(value)) value = 0.00;
    hiddenField.value = value.toFixed(2);
    calculateFinancials();
}

    // Calculate financials based on input fields
    function calculateFinancials() {
        const sumInsured = parseFloat(document.getElementById('sum_insured_display').value.replace(/,/g, '')) || 0.00;
        const rate = parseFloat(document.getElementById('rate_display').value.replace(/,/g, '')) || 0;
        const premiumType = document.getElementById('premium_type').value;
        let premium;

        // Calculate premium based on premium type
        if (premiumType === 'rate') {
            premium = sumInsured * (rate / 100);
            document.getElementById('premium_display').value = formatNumberWithCommas(premium);
            document.getElementById('premium').value = premium.toFixed(2);
        } else {
            premium = parseFloat(document.getElementById('premium_display').value.replace(/,/g, '')) || 0.00;
        }

        const c_rate = parseFloat(document.getElementById('c_rate_display').value.replace(/,/g, '')) || 10;
        const otherCharges = parseFloat(document.getElementById('other_charges_display').value.replace(/,/g, '')) || 0.00;
        const policyCharge = parseFloat(document.getElementById('policy_charge_display').value.replace(/,/g, '')) || 0.00;
        const stampDuty = parseFloat(document.getElementById('s_duty_display').value.replace(/,/g, '')) || 0.00;
        const pvt = parseFloat(document.getElementById('pvt_display').value.replace(/,/g, '')) || 0.00;
        const excess = parseFloat(document.getElementById('excess_display').value.replace(/,/g, '')) || 0.00;
        const courtesyCar = parseFloat(document.getElementById('courtesy_car_display').value.replace(/,/g, '')) || 0.00;
        const ppl = parseFloat(document.getElementById('ppl_display').value.replace(/,/g, '')) || 0.00;
        const roadRescue = parseFloat(document.getElementById('road_rescue_display').value.replace(/,/g, '')) || 0.00;

        const c_rateDecimal = c_rate / 100;

        const commission = premium * c_rateDecimal;
        const wht = commission * 10.00 / 100;
        const tLevy = premium * 0.20 / 100;
        const pcfLevy = premium * 0.25 / 100;
        const grossPremium = premium + pvt + excess + pcfLevy + tLevy + policyCharge + otherCharges + stampDuty + courtesyCar + ppl + roadRescue;
        const netPremium = premium - commission + pvt + excess + pcfLevy + tLevy - policyCharge + otherCharges + stampDuty + courtesyCar + ppl + roadRescue;

        // Display formatted values with commas in the display fields
        document.getElementById('commission_display').value = formatNumberWithCommas(commission);
        document.getElementById('wht_display').value = formatNumberWithCommas(wht);
        document.getElementById('t_levy_display').value = formatNumberWithCommas(tLevy);
        document.getElementById('pcf_levy_display').value = formatNumberWithCommas(pcfLevy);
        document.getElementById('gross_premium_display').value = formatNumberWithCommas(grossPremium);
        document.getElementById('net_premium_display').value = formatNumberWithCommas(netPremium);

        // Set unformatted values in hidden fields for submission
        document.getElementById('premium').value = premium.toFixed(2);
        document.getElementById('commission').value = commission.toFixed(2);
        document.getElementById('wht').value = wht.toFixed(2);
        document.getElementById('t_levy').value = tLevy.toFixed(2);
        document.getElementById('pcf_levy').value = pcfLevy.toFixed(2);
        document.getElementById('gross_premium').value = grossPremium.toFixed(2);
        document.getElementById('net_premium').value = netPremium.toFixed(2);
    }

    // Toggle premium fields based on selected premium type
    function togglePremiumFields() {
        const premiumType = document.getElementById('premium_type').value;
        const rateField = document.getElementById('rate_display');
        const premiumField = document.getElementById('premium_display');

        if (premiumType === 'rate') {
            rateField.disabled = false;
            premiumField.readOnly = true;
            calculatePremiumFromRate();
        } else {
            rateField.disabled = true;
            premiumField.readOnly = false;
            calculateRateFromPremium();
        }
    }

    // Calculate premium from rate
    function calculatePremiumFromRate() {
        const sumInsured = parseFloat(document.getElementById('sum_insured_display').value.replace(/,/g, '')) || 0.00;
        const rate = parseFloat(document.getElementById('rate_display').value.replace(/,/g, '')) || 0;
        const premium = sumInsured * (rate / 100);

        document.getElementById('premium_display').value = formatNumberWithCommas(premium);
        document.getElementById('premium').value = premium.toFixed(2);

        calculateFinancials();
    }

    // Calculate rate from premium
function calculateRateFromPremium() {
    const sumInsured = parseFloat(document.getElementById('sum_insured_display').value.replace(/,/g, '')) || 0.00;
    const premium = parseFloat(document.getElementById('premium_display').value.replace(/,/g, '')) || 0.00;
    let rate = 0.00;
    if (sumInsured > 0) {
        rate = (premium / sumInsured) * 100;
    }
    document.getElementById('rate_display').value = rate.toFixed(2);
    document.getElementById('rate').value = rate.toFixed(2);

    calculateFinancials();
}

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        togglePremiumFields();
        calculateFinancials();

        // Add event listeners for rate and sum insured changes
        document.getElementById('rate_display').addEventListener('input', calculatePremiumFromRate);
        document.getElementById('sum_insured_display').addEventListener('input', calculatePremiumFromRate);
    });

    // Load cover details based on selected policy type
    function loadCoverDetails() {
        const policyTypeId = $('#policy_type_id').val();
        let fileName;

        switch (policyTypeId) {
            case '1':
                fileName = 'agribusiness_features.txt';
                break;
            case '2':
                fileName = 'all_risks-insurance_features.txt';
                break;
            case '3':
                fileName = 'all_risks-insurance_features.txt';
                break;
            case '4':
                fileName = 'burglary_insurance_features.txt';
                break;
            case '5':
                fileName = 'business_combined_features.txt';
                break;
            case '6':
                fileName = 'carriers_legal_liability_features.txt';
                break;
            case '7':
                fileName = 'combined_general_liability_features.txt';
                break;
            case '8':
                fileName = 'contaminated_products_features.txt';
                break;
            case '9':
                fileName = 'contractor_plant_machinery_features.txt';
                break;
            case '10':
                fileName = 'contractors_all_risks_features.txt';
                break;
            case '11':
                fileName = 'contractual_liability_features.txt';
                break;
            case '12':
                fileName = 'custom_bond_features.txt';
                break;
            case '13':
                fileName = 'directors_and_officers_liability_features.txt';
                break;
            case '14':
                fileName = 'domestic_package_features.txt';
                break;
            case '15':
                fileName = 'electronic_equipment_insurance_features.txt';
                break;
            case '16':
                fileName = 'employers_liability_features.txt';
                break;
            case '17':
                fileName = 'engineering_features.txt';
                break;
            case '18':
                fileName = 'erection_all_risks_features.txt';
                break;
            case '19':
                fileName = 'evacuation_and_repatriation_features.txt';
                break;
            case '20':
                fileName = 'fidelity_guarantee_features.txt';
                break;
            case '21':
                fileName = 'fire_special_perils_features.txt';
                break;
            case '22':
                fileName = 'golfers_features.txt';
                break;
            case '23':
                fileName = 'goods_in_transit_insurance_features.txt';
                break;
            case '24':
                fileName = 'group_life_features.txt';
                break;
            case '25':
                fileName = 'group_personal_accident_features.txt';
                break;
            case '26':
                fileName = 'immigration_bond_features.txt';
                break;
            case '27':
                fileName = 'individual_life_features.txt';
                break;
            case '28':
                fileName = 'industrial_all_risks_features.txt';
                break;
            case '29':
                fileName = 'kidnap_features.txt';
                break;
            case '30':
                fileName = 'last_expense_features.txt';
                break;
            case '31':
                fileName = 'machinery_breakdown_features.txt';
                break;
            case '32':
                fileName = 'marine_cargo_features.txt';
                break;
            case '33':
                fileName = 'marine_hull_features.txt';
                break;
            case '34':
                fileName = 'medical_features.txt';
                break;
            case '35':
                fileName = 'motor_private_features.txt';
                break;
            case '36':
                fileName = 'motor_commercial_features.txt';
                break;
            case '37':
                fileName = 'motorcycle_features.txt';
                break;
            case '38':
                fileName = 'office_combined_features.txt';
                break;
            case '39':
                fileName = 'performance_bond_features.txt';
                break;
            case '40':
                fileName = 'personal_accident_features.txt';
                break;
            case '41':
                fileName = 'political_risks_features.txt';
                break;
            case '42':
                fileName = 'products_liability_features.txt';
                break;
            case '43':
                fileName = 'professional_indemnity_features.txt';
                break;
            case '44':
                fileName = 'public_liability_features.txt';
                break;
            case '45':
                fileName = 'surety_bond_features.txt';
                break;
            case '46':
                fileName = 'tender_bond_features.txt';
                break;
            case '47':
                fileName = 'term_assurance_features.txt';
                break;
            case '48':
                fileName = 'travel_features.txt';
                break;
            case '49':
                fileName = 'warehousing_features.txt';
                break;
            case '50':
                fileName = 'warehousing_legal_liability_features.txt';
                break;
            case '51':
                fileName = 'WIBA_features.txt';
                break;
            case '52':
                fileName = 'group_personal_WIBA_features.txt';
                break;
            case '53':
                fileName = 'contractors_all_risks_features.txt'; // Assuming this is the correct file name
                break;
            case '54':
                fileName = 'all_risks_insurance_features.txt'; // Assuming this is the correct file name
                break;
            case '55':
                fileName = 'group_personal_accident_(GPA)_features.txt'; // Assuming this is the correct file name
                break;
            case '56':
                fileName = 'aviation_hull_features.txt';
                break;
            case '57':
                fileName = 'aviation_premises_features.txt';
                break;
            case '58':
                fileName = 'business_interruption_insurance_features.txt';
                break;
            case '59':
                fileName = 'group_personal_accident_aviation_features.txt'; // Assuming this is the correct file name
                break;
            case '60':
                fileName = 'group_personal_accident_fixed_benefits_features.txt';
                break;
            case '61':
                fileName = 'machinery_breakdown_consequential_loss_features.txt';
                break;
            case '62':
                fileName = 'money_insurance_features.txt';
                break;
            case '63':
                fileName = 'motor_contingent_legal_liability_features.txt';
                break;
            case '64':
                fileName = 'motor_trade_road_risks_features.txt';
                break;
            case '65':
                fileName = 'plant_all_risks_features.txt';
                break;
            case '66':
                fileName = 'plate_glass_insurance_features.txt';
                break;
            case '67':
                fileName = 'stock_floater_insurance_features.txt';
                break;
            case '68':
                fileName = 'trustees_liability_features.txt';
                break;
            default:
                fileName = ''; // No file for this policy type
                break;
        }

        if (fileName) {
            $.ajax({
                url: `/text_files/${fileName}`,
                type: "GET",
                success: function(data) {
                    $('#cover_details').val(data);
                },
                error: function() {
                    $('#cover_details').val(''); // Clear cover details if there's an error
                }
            });
        } else {
            $('#cover_details').val(''); // Clear cover details if no file is available
        }
    }
</script>
<script>
    // Function to add a new document row
    function addDocumentRow() {
        const table = document.getElementById('documentsTable');
        const templateRow = document.getElementById('documentRowTemplate');
        const newRowIndex = table.tBodies[0].childElementCount + 1;

        // Clone the template row
        const newRow = templateRow.cloneNode(true);
        newRow.style.display = '';

        // Update the row index
        newRow.children[0].textContent = newRowIndex;

        // Add the new row to the table
        table.tBodies[0].appendChild(newRow);
    }

    // Function to remove a document row
    function removeDocumentRow(button) {
        const row = button.parentNode.parentNode;
        row.remove();
    }
</script>

@endsection
