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

    <!-- Show session messages (success / error / validation) -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Policy creation form -->
    <form id="policyForm" method="POST" action="{{ route('policies.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Client Details Section -->
        <div class="group-heading d-flex justify-content-between align-items-center">
            <span>Client Details</span>
        </div>
        <div class="row">
            <!-- Client Type Selection -->
            <div class="col-md-4 form-group">
                <label>Type</label><br>
                <label>
                    <input type="radio" name="client_type" value="lead" {{ old('client_type', 'customer') == 'lead' ? 'checked' : '' }} onclick="toggleClientFields()"> Lead
                </label>
                <label>
                    <input type="radio" name="client_type" value="customer" {{ old('client_type', 'customer') == 'customer' ? 'checked' : 'checked' }} onclick="toggleClientFields()"> Customer
                </label>
            </div>
        </div>

        <div class="row">
            <!-- Customer Search and Selection -->
            <div id="customerCodeField" class="col-md-3 form-group">
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
                    <option value="14" {{ old('policy_type_id') == 14 ? 'selected' : '' }}>Domestic Package</option>
                    <!-- Additional options -->
                    <option disabled>─────────────────</option>
                    @foreach($availablePolicyTypes as $policyTypeId => $policyTypeName)
                        <option value="{{ $policyTypeId }}" {{ old('policy_type_id') == $policyTypeId ? 'selected' : '' }}>{{ $policyTypeName }}</option>
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

            <div class="col-md-3  form-group">
    <label for="start_date">Policy Start Date <span class="text-danger">*</span></label>
    <input type="date" id="start_date" name="start_date" class="form-control" required
           {{-- Dynamically sets the minimum date allowed to Jan 1st of the current year --}}
           min="{{ now()->startOfYear()->format('Y-m-d') }}"
           value="{{ old('start_date', now()->format('Y-m-d')) }}">
</div>

            <div class="col-md-2 form-group">
                <label for="days">Days</label>
                <input type="number" id="days" name="days" class="form-control" value="{{ old('days', 364) }}">
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
                    <label for="reg_no">Reg No<span class="text-danger" >*</span></label>
                    <input type="text" id="reg_no" name="reg_no" class="form-control" value="{{ old('reg_no') }}" oninput="this.value = this.value.toUpperCase()">
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
                    <select id="yom" name="yom" class="form-control">
                        <option value="">Select</option>
                        @php
                            $currentYear = date('Y');
                            for ($year = $currentYear; $year >= $currentYear - 100; $year--) {
                                echo '<option value="' . $year . '" ' . (old('yom') == $year ? 'selected' : '') . '>' . $year . '</option>';
                            }
                        @endphp
                    </select>
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
		    <option value="TruckLorry" {{ old('body_type') == 'TruckLorry' ? 'selected' : '' }}>Truck/Lorry</option>
		    <option value="Tractor" {{ old('body_type') == 'Tractor' ? 'selected' : '' }}>Tractor</option>
		    <option value="Specialtypes" {{ old('body_type') == 'Specialtypes' ? 'selected' : '' }}>Special Types</option>
                </select> 
            </div>

            <div class="col-md-3 form-group">
                <label for="chassisno">Chassis No.</label>
    <input type="text" id="chassisno" name="chassisno" class="form-control @error('chassisno') is-invalid @enderror" pattern="[A-Za-z0-9-]*" maxlength="255" value="{{ old('chassisno') }}" title="Only letters, numbers, and hyphens are allowed"   oninput="this.value = this.value.toUpperCase()">
    @error('chassisno')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror 
</div>

<div class="col-md-3 form-group">
    <label for="engine_no">Engine No.</label>
    <input type="text" id="engine_no" name="engine_no" class="form-control @error('engine_no') is-invalid @enderror" pattern="[A-Za-z0-9-]*" maxlength="255" value="{{ old('engine_no') }}" title="Only letters, numbers, and hyphens are allowed"   oninput="this.value = this.value.toUpperCase()">
    @error('engine_no')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

            </div>
        </div>

        <!-- Description Section (Initially Hidden) -->
        <div id="descriptionSection" class="mt-3" style="display: none;">
            <div class="group-heading">Policy Description</div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" style="height: 200px; width: 100%;">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Financial Details Section -->
        <div class="group-heading mt-3">Financial Details</div>
        <div class="row mt-3">
            <div class="col-md-3 form-group">
                <label for="sum_insured_display">Sum Insured</label>
                <input type="text" id="sum_insured_display" class="form-control" value="{{ old('sum_insured', '0.00') }}" oninput="syncHiddenFields('sum_insured'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="sum_insured" name="sum_insured" value="{{ old('sum_insured', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="premium_type">Rate/Amount<span class="text-danger">*</span></label>
                <select id="premium_type" name="premium_type" class="form-control" onchange="togglePremiumFields()">
                    <option value="rate" {{ old('premium_type') == 'rate' ? 'selected' : '' }}>Rate</option>
                    <option value="premium" {{ old('premium_type') == 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
            </div>

            <div class="col-md-2 form-group">
                <label for="rate_display">Rate</label>
                <input type="text" id="rate_display" class="form-control" value="{{ old('rate', '0.00') }}" oninput="syncHiddenFields('rate'); calculatePremiumFromRate()" onfocusout="formatInput(this)">
                <input type="hidden" id="rate" name="rate" value="{{ old('rate', '0.00') }}">
            </div>

            <div class="col-md-3 form-group">
                <label for="premium_display">Premium <span class="text-danger">*</span></label>
                <input type="text" id="premium_display" class="form-control" value="{{ old('premium', '0.00') }}" oninput="syncHiddenFields('premium'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="premium" name="premium" value="{{ old('premium', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="c_rate_display">Commission Rate</label>
                <input type="text" id="c_rate_display" class="form-control" value="{{ old('c_rate', '10.00') }}" oninput="syncHiddenFields('c_rate'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="c_rate" name="c_rate" value="{{ old('c_rate', '10.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="commission_display">Commission</label>
                <input type="text" id="commission_display" class="form-control" value="{{ old('commission', '0.00') }}" readonly>
                <input type="hidden" id="commission" name="commission" value="{{ old('commission', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="wht_display">WHT</label>
                <input type="text" id="wht_display" class="form-control" value="{{ old('wht', '0.00') }}" readonly>
                <input type="hidden" id="wht" name="wht" value="{{ old('wht', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="t_levy_display">Training Levy</label>
                <input type="text" id="t_levy_display" class="form-control" value="{{ old('t_levy', '0.00') }}" readonly>
                <input type="hidden" id="t_levy" name="t_levy" value="{{ old('t_levy', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="pcf_levy_display">PCF Levy</label>
                <input type="text" id="pcf_levy_display" class="form-control" value="{{ old('pcf_levy', '0.00') }}" readonly>
                <input type="hidden" id="pcf_levy" name="pcf_levy" value="{{ old('pcf_levy', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="s_duty_display">Stamp Duty</label>
                <input type="text" id="s_duty_display" class="form-control" value="{{ old('s_duty', '0.00') }}" oninput="syncHiddenFields('s_duty'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="s_duty" name="s_duty" value="{{ old('s_duty', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="policy_charge_display">Policy Charge</label>
                <input type="text" id="policy_charge_display" class="form-control" value="{{ old('policy_charge', '0.00') }}" oninput="syncHiddenFields('policy_charge'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="policy_charge" name="policy_charge" value="{{ old('policy_charge', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="pvt_display">PVT</label>
                <input type="text" id="pvt_display" class="form-control" value="{{ old('pvt', '0.00') }}" oninput="syncHiddenFields('pvt'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="pvt" name="pvt" value="{{ old('pvt', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="excess_display">Excess</label>
                <input type="text" id="excess_display" class="form-control" value="{{ old('excess', '0.00') }}" oninput="syncHiddenFields('excess'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="excess" name="excess" value="{{ old('excess', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="other_charges_display">Other Charges</label>
                <input type="text" id="other_charges_display" class="form-control" value="{{ old('other_charges', '0.00') }}" oninput="syncHiddenFields('other_charges'); calculateFinancials()" onfocusout="formatInput(this)">
                <input type="hidden" id="other_charges" name="other_charges" value="{{ old('other_charges', '0.00') }}">
            </div>
            <div class="col-md-2 form-group">
                <label for="courtesy_car">Courtesy Car</label>
                <input type="text" id="courtesy_car_display" class="form-control" value="{{ old('courtesy_car', '0.00') }}" 
                    oninput="syncHiddenFields('courtesy_car'); calculateFinancials()" 
                    onfocusout="formatInput(this)">
                <input type="hidden" id="courtesy_car" name="courtesy_car" value="{{ old('courtesy_car', '0.00') }}">
            </div>

            <div class="col-md-2 form-group">
                <label for="ppl">PPL</label>
                <input type="text" id="ppl_display" class="form-control" value="{{ old('ppl', '0.00') }}" 
                    oninput="syncHiddenFields('ppl'); calculateFinancials()" 
                    onfocusout="formatInput(this)">
                <input type="hidden" id="ppl" name="ppl" value="{{ old('ppl', '0.00') }}">
            </div>
          
            <div class="col-md-2 form-group">
                <label for="road_rescue">Road Rescue</label>
                <input type="number" id="road_rescue_display" class="form-control" value="{{ old('road_rescue', 0.00) }}" 
                    oninput="syncHiddenFields('road_rescue'); calculateFinancials()" 
                    onfocusout="formatInput(this)">
                <input type="hidden" id="road_rescue" name="road_rescue" value="{{ old('road_rescue', 0.00) }}">
            </div>

        </div>

        <div class="row mt-3">
            <div class="col-md-2 form-group">
                <label for="gross_premium_display">Gross Premium</label>
                <input type="text" id="gross_premium_display" class="form-control" value="{{ old('gross_premium', '0.00') }}" readonly>
                <input type="hidden" id="gross_premium" name="gross_premium" value="{{ old('gross_premium', '0.00') }}">
            </div>
            <div class="col-md-2 form-group">
                <label for="net_premium_display">Net Premium</label>
                <input type="text" id="net_premium_display" class="form-control" value="{{ old('net_premium', '0.00') }}" readonly>
                <input type="hidden" id="net_premium" name="net_premium" value="{{ old('net_premium', '0.00') }}">
            </div>
        </div>

        <!-- Cover Details Section -->
        <div class="group-heading mt-3">Cover Details</div>
<div class="form-group">
    <label for="cover_details">Features</label>
    <textarea id="cover_details" name="cover_details" class="form-control" style="height: 200px; width: 100%;">{{ old('cover_details') }}</textarea>
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="documentRowTemplate" style="display: none;">
                            <td>1</td>
                            <td>
                                <input type="text" name="document_description[]" class="form-control" placeholder="Enter description">
                            </td>
                            <td>
                                <input type="file" name="upload_file[]" class="form-control">
                            </td>
                        </tr>
                        <!-- Additional rows will be added here -->
                        @if(old('document_description'))
                            @foreach(old('document_description') as $key => $description)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <input type="text" name="document_description[]" class="form-control" placeholder="Enter description" value="{{ $description }}">
                                    </td>
                                    <td>
                                        <input type="file" name="upload_file[]" class="form-control">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary" onclick="addDocumentRow()">Add Document</button>
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

    // Handle policy type section toggle and load cover details
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

        // Load cover details based on the selected policy type
        loadCoverDetails();
    }

    $(document).ready(function() {
        // Set default values
        let today = new Date().toISOString().split('T')[0];
        $('#start_date').val(today);
        $('#days').val(364);

        // Calculate end date
        $('#end_date').val(calculateEndDate(today, 364));

        // Update end date when start date or days change
        $('#start_date, #days').on('change', function() {
            let startDate = $('#start_date').val();
            let days = $('#days').val();
            $('#end_date').val(calculateEndDate(startDate, days));
        });

        // Toggle client fields based on client type selection
        toggleClientFields();

        // Hide/show sections based on policy type
        togglePolicyTypeFields();

        // Populate makes dropdown
        populateMakesDropdown();
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

    // Load vehicle models based on selected vehicle type
    const availableModels = @json($vehicleModels);

    // Sort the available models by make and then by model
    availableModels.sort((a, b) => {
        if (a.make === b.make) {
            return a.model.localeCompare(b.model);
        }
        return a.make.localeCompare(b.make);
    });

    function populateMakesDropdown() {
        const makeDropdown = document.getElementById('vehicle_type');
        makeDropdown.innerHTML = '<option value="">Select</option>';

        // Get unique makes
        const uniqueMakes = [...new Set(availableModels.map(model => model.make))];

        // Sort unique makes alphabetically
        uniqueMakes.sort((a, b) => a.localeCompare(b));

        uniqueMakes.forEach(make => {
            const option = document.createElement('option');
            option.value = make;
            option.text = make;
            makeDropdown.add(option);
        });
    }

    function loadModels() {
        const make = document.getElementById('vehicle_type').value;
        const modelDropdown = document.getElementById('Model');

        modelDropdown.innerHTML = '<option value="">Select</option>';
        if (make) {
            const filteredModels = availableModels.filter(model => model.make === make);
            
            // Since availableModels is already sorted, filteredModels will be sorted by model
            filteredModels.forEach(model => {
                const option = document.createElement('option');
                option.value = model.model;
                option.text = model.model;
                modelDropdown.add(option);
            });
        }
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

    // Calculate financials based on input fields
    function calculateFinancials() {
        const sumInsured = parseFloat(document.getElementById('sum_insured_display').value.replace(/,/g, '')) || 0;
        const rate = parseFloat(document.getElementById('rate_display').value.replace(/,/g, '')) || 0;
        const premium = parseFloat(document.getElementById('premium_display').value.replace(/,/g, '')) || 0;
        const c_rate = parseFloat(document.getElementById('c_rate_display').value.replace(/,/g, '')) || 0;
        const t_levy = parseFloat(document.getElementById('t_levy_display').value.replace(/,/g, '')) || 0;
        const pcf_levy = parseFloat(document.getElementById('pcf_levy_display').value.replace(/,/g, '')) || 0;
        const s_duty = parseFloat(document.getElementById('s_duty_display').value.replace(/,/g, '')) || 0;
        const policy_charge = parseFloat(document.getElementById('policy_charge_display').value.replace(/,/g, '')) || 0;
        const other_charges = parseFloat(document.getElementById('other_charges_display').value.replace(/,/g, '')) || 0;
        const courtesy_car = parseFloat(document.getElementById('courtesy_car_display').value.replace(/,/g, '')) || 0;
        const ppl = parseFloat(document.getElementById('ppl_display').value.replace(/,/g, '')) || 0;
        const road_rescue = parseFloat(document.getElementById('road_rescue_display').value.replace(/,/g, '')) || 0;
        const pvt = parseFloat(document.getElementById('pvt_display').value.replace(/,/g, '')) || 0;
        const excess = parseFloat(document.getElementById('excess_display').value.replace(/,/g, '')) || 0;

        // Calculate premium if rate is entered
        let calculatedPremium = premium;
        if (rate > 0) {
            calculatedPremium = (sumInsured * rate) / 100;
            document.getElementById('premium_display').value = formatNumberWithCommas(calculatedPremium.toFixed(2));
            document.getElementById('premium').value = calculatedPremium.toFixed(2);
        }

        // Calculate Commission (c_rate% of premium)
        const commission = (calculatedPremium * c_rate) / 100;
        document.getElementById('commission_display').value = formatNumberWithCommas(commission.toFixed(2));
        document.getElementById('commission').value = commission.toFixed(2);

        // Calculate WHT (10% of commission)
        const wht = (commission * 10) / 100;
        document.getElementById('wht_display').value = formatNumberWithCommas(wht.toFixed(2));
        document.getElementById('wht').value = wht.toFixed(2);

        // Calculate Training Levy (0.20% of premium)
        const training_levy = (calculatedPremium * 0.20) / 100;
        document.getElementById('t_levy_display').value = formatNumberWithCommas(training_levy.toFixed(2));
        document.getElementById('t_levy').value = training_levy.toFixed(2);

        // Calculate PCF Levy (0.25% of premium)
        const pcf = (calculatedPremium * 0.25) / 100;
        document.getElementById('pcf_levy_display').value = formatNumberWithCommas(pcf.toFixed(2));
        document.getElementById('pcf_levy').value = pcf.toFixed(2);

        // Calculate gross premium
        const gross_premium = calculatedPremium + training_levy + pcf + s_duty + policy_charge + other_charges + 
                            courtesy_car + ppl + road_rescue + pvt + excess;

        // Calculate net premium (premium - commission)
        const net_premium = calculatedPremium - commission + training_levy + pcf + s_duty - policy_charge + other_charges + 
        courtesy_car + ppl + road_rescue + pvt + excess;

        // Update display fields with comma formatting
        document.getElementById('gross_premium_display').value = formatNumberWithCommas(gross_premium.toFixed(2));
        document.getElementById('net_premium_display').value = formatNumberWithCommas(net_premium.toFixed(2));

        // Update hidden fields without formatting
        document.getElementById('gross_premium').value = gross_premium.toFixed(2);
        document.getElementById('net_premium').value = net_premium.toFixed(2);
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
        const numericInputs = document.querySelectorAll('input[type="text"]:not(#search):not(#policy_no):not(#chassisno):not(#policy_no):not(#engine_no):not(#reg_no)');
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

    // Simple client-side submit logger for debugging - non-blocking.
    // This helps confirm the browser actually attempts submission.
    // It does NOT prevent submission.
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('policyForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            try {
                console.log('Policy form submitting...', {
                    action: form.getAttribute('action'),
                    method: form.getAttribute('method'),
                    timestamp: new Date().toISOString()
                });
                // optional: show a quick UI feedback
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerText = 'Submitting...';
                }
            } catch (err) {
                console.error('Submit logger error:', err);
            }
            // Let the form submit normally
        });
    });

    // Simple client-side submit validator + logger
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('policyForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            var btn = form.querySelector('button[type="submit"]');

            try {
                // Read date values
                var startStr = document.getElementById('start_date').value;
                var endStr = document.getElementById('end_date').value;
                var daysVal = document.getElementById('days').value;

                var start = startStr ? new Date(startStr + 'T00:00:00') : null;
                var end = endStr ? new Date(endStr + 'T00:00:00') : null;
                var days = daysVal !== '' ? parseInt(daysVal, 10) : null;

                // Prepare Jan 1 current year
                var now = new Date();
                var jan1 = new Date(now.getFullYear(), 0, 1); // month 0 = January

                // Basic client-side checks
                if (!start) {
                    alert('Please provide a valid Start Date.');
                    if (btn) btn.disabled = false;
                    e.preventDefault();
                    return;
                }
                if (start < jan1) {
                    alert('Start date cannot be earlier than ' + jan1.toISOString().slice(0,10));
                    if (btn) btn.disabled = false;
                    e.preventDefault();
                    return;
                }
                if (!end) {
                    alert('Please provide a valid End Date.');
                    if (btn) btn.disabled = false;
                    e.preventDefault();
                    return;
                }
                if (end <= start) {
                    alert('End date must be later than the Start date.');
                    if (btn) btn.disabled = false;
                    e.preventDefault();
                    return;
                }

                // If days provided, verify difference matches days
                if (days !== null && !isNaN(days)) {
                    // compute difference in days (end - start)
                    var diffMs = end.getTime() - start.getTime();
                    var diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24)); // whole days
                    if (diffDays !== days) {
                        alert('The difference between Start and End date (' + diffDays + ' days) does not match the Days field (' + days + ' days).');
                        if (btn) btn.disabled = false;
                        e.preventDefault();
                        return;
                    }
                }

                // All good — show submitting UI
                console.log('Policy form submitting...', {
                    action: form.getAttribute('action'),
                    method: form.getAttribute('method'),
                    timestamp: new Date().toISOString()
                });
                if (btn) {
                    btn.disabled = true;
                    btn.innerText = 'Submitting...';
                }

                // allow submit to proceed
            } catch (err) {
                console.error('Submit validator error:', err);
                if (btn) btn.disabled = false;
                // Let it submit so server side catches errors, or prevent: here we prevent to avoid silent failure
                e.preventDefault();
                alert('An unexpected error occurred in the form. See console for details.');
            }
        });
    });
</script>

@push('scripts')
<script>
    // Function to calculate the end date based on start date and days
    function calculateEndDate() {
        const startDateInput = document.getElementById('start_date');
        const daysInput = document.getElementById('days');
        const endDateInput = document.getElementById('end_date');

        const startDateValue = startDateInput.value;
        const daysValue = parseInt(daysInput.value);

        if (startDateValue && !isNaN(daysValue) && daysValue >= 0) {
            // Use date manipulation for accurate calculation
            let date = new Date(startDateValue);
            
            // Add 'days' plus one to the start date to get the end date.
            // A 365-day policy (days=364) means the end date is 364 days AFTER the start date.
            // We use 364 days here (the value the user entered in the input).
            date.setDate(date.getDate() + daysValue); 

            // Format the calculated date as YYYY-MM-DD
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            endDateInput.value = `${year}-${month}-${day}`;
        } else {
            endDateInput.value = '';
        }
    }

    // Attach the function to input change events
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const daysInput = document.getElementById('days');
        
        if (startDateInput) startDateInput.addEventListener('change', calculateEndDate);
        if (daysInput) daysInput.addEventListener('input', calculateEndDate);
        
        // Run calculation once on page load to set the default end date
        calculateEndDate(); 
    });
</script>
@endpush

@endsection
