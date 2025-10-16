@extends('layouts.appPages')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Renew Policy - File: {{ $policy->fileno }}  </h2>

    <form action="{{ route('renewals.store', $policy->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- Hidden fields to carry essential context --}}
        <input type="hidden" name="original_policy_id" value="{{ $policy->id }}">

        {{-- Original Policy Read-only Info Card (Contextual Header) --}}
        <div class="card bg-light border-start border-primary border-4 mb-4 shadow-sm">
            <div class="card-body py-2">
                <div class="row small">
                       <div class="col-md-2"><strong>Code:</strong> {{ $policy->customer_code }}</div> 
                    <div class="col-md-2"><strong>Name:</strong> {{ $policy->customer_name ?? ($policy->customer->customer_name ?? '') }}</div>
                 
                    <div class="col-md-4"><strong>Policy:</strong> {{ $policy->policy_type_name }} : {{ $policy->reg_no }}</div>
                     
                    <div class="col-md-4"><strong>End Date:</strong> {{ $policy->end_date }}</div>
                </div>
            </div>
        </div>

        {{-- Bootstrap Tabs Navigation --}}
        <ul class="nav nav-tabs" id="renewTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">1. Policy & Coverage</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab" aria-controls="financial" aria-selected="false">2. Financial Details</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="attachments-tab" data-bs-toggle="tab" data-bs-target="#attachments" type="button" role="tab" aria-controls="attachments" aria-selected="false">3. Documents</button>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content p-4 border border-top-0 bg-white shadow-sm" id="renewTabsContent">

            {{-- Tab 1: Policy & Coverage --}}
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <h5 class="mb-3 text-primary">New Policy & Duration</h5>
                <div class="row g-3">
                    
                    {{-- Policy No (pre-filled with old policy number but editable) --}}
                    <div class="col-md-3 form-group">
                        <label for="policy_no" class="form-label"> Policy No <span class="text-danger">*</span></label>
                        <input type="text" id="policy_no" name="policy_no" class="form-control" value="{{ old('policy_no', $policy->policy_no) }}" required>
                        @error('policy_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    {{-- Start Date (with dynamic calculation hook) --}}
                    <div class="col-md-3 form-group">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" 
                            value="{{ old('start_date', $startDate) }}" 
                            onchange="calculateEndDate()">
                    </div>

                    {{-- Days (moved here, with dynamic calculation hook) --}}
                    <div class="col-md-2 form-group">
                        <label for="days" class="form-label">Days</label>
                        <input type="number" id="days" name="days" class="form-control" 
                            value="{{ old('days', $policy->days ?? 365) }}"
                            oninput="calculateEndDate()"> 
                    </div>

                    {{-- End Date (set to readonly, calculated by script) --}}
                    <div class="col-md-4 form-group">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" 
                               value="{{ old('end_date', $endDate) }}" 
                               readonly>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4 form-group">
                        <label for="insurer_id" class="form-label">Insurer <span class="text-danger">*</span></label>
                        <select id="insurer_id" name="insurer_id" class="form-select @error('insurer_id') is-invalid @enderror">
                            <option value="">Select Insurer</option>
                            @foreach($insurers as $id => $name)
                                <option value="{{ $id }}" {{ old('insurer_id', $policy->insurer_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('insurer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="coverage" class="form-label">Coverage Type</label>
                        <select id="coverage" name="coverage" class="form-select">
                            <option value="">Select Coverage</option>
                            <option value="Comprehensive" {{ old('coverage', $policy->coverage) == 'Comprehensive' ? 'selected' : '' }}>Comprehensive</option>
                            <option value="TPO" {{ old('coverage', $policy->coverage) == 'TPO' ? 'selected' : '' }}>TPO</option>
                            {{-- Add other coverage options as needed --}}
                        </select>
                    </div>

                    <div class="col-md-4">
                        {{-- Empty column to balance the row since 'Days' was moved up --}}
                    </div>
                </div>
                
                {{-- Next button for Tab 1 (FIXED with onclick) --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="#financial" class="btn btn-primary" onclick="showTab('financial')">
                        Next: Financial Details <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            {{-- Tab 2: Financial Details --}}
            <div class="tab-pane fade" id="financial" role="tabpanel" aria-labelledby="financial-tab">
                <h5 class="mb-3 text-primary">Core Premiums & Rates</h5>
                <div class="row g-3">
                    
                    <div class="col-md-4 form-group">
                        <label for="sum_insured_display" class="form-label">Sum Insured</label>
                        <input type="text" id="sum_insured_display" class="form-control" value="{{ old('sum_insured', $policy->sum_insured) }}" 
                            oninput="syncHiddenFields('sum_insured'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="sum_insured" name="sum_insured" value="{{ old('sum_insured', $policy->sum_insured) }}">
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="premium_type" class="form-label">Premium Calculation</label>
                        <select id="premium_type" name="premium_type" class="form-select" onchange="togglePremiumFields(); calculateFinancials()">
                            <option value="rate" {{ old('premium_type', $policy->premium_type) == 'rate' ? 'selected' : '' }}>Rate %</option>
                            <option value="premium" {{ old('premium_type', $policy->premium_type) == 'premium' ? 'selected' : '' }}>Fixed Premium</option>
                        </select>
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="rate_display" class="form-label">Rate (%)</label>
                        <input type="text" id="rate_display" class="form-control" value="{{ old('rate', $policy->rate) }}" 
                            oninput="syncHiddenFields('rate'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="rate" name="rate" value="{{ old('rate', $policy->rate) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="premium_display" class="form-label">Premium <span class="text-danger">*</span></label>
                        <input type="text" id="premium_display" class="form-control" value="{{ old('premium', $policy->premium) }}" 
                            oninput="syncHiddenFields('premium'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="premium" name="premium" value="{{ old('premium', $policy->premium) }}">
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3 text-primary">Commissions & Fees</h5>
                <div class="row g-3">
                    <div class="col-md-3 form-group">
                        <label for="c_rate_display" class="form-label">Commission Rate (%)</label>
                        <input type="text" id="c_rate_display" class="form-control" value="{{ old('c_rate', $policy->c_rate) }}" 
                            oninput="syncHiddenFields('c_rate'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="c_rate" name="c_rate" value="{{ old('c_rate', $policy->c_rate) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="commission_display" class="form-label">Commission Paid</label>
                        <input type="text" id="commission_display" class="form-control bg-light" value="{{ old('commission', $policy->commission) }}" readonly>
                        <input type="hidden" id="commission" name="commission" value="{{ old('commission', $policy->commission) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="wht_display" class="form-label">WHT (5%)</label>
                        <input type="text" id="wht_display" class="form-control bg-light" value="{{ old('wht', $policy->wht) }}" readonly>
                        <input type="hidden" id="wht" name="wht" value="{{ old('wht', $policy->wht) }}">
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="excess_display" class="form-label">Policy Excess</label>
                        <input type="text" id="excess_display" class="form-control" value="{{ old('excess', $policy->excess) }}" 
                            oninput="syncHiddenFields('excess'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="excess" name="excess" value="{{ old('excess', $policy->excess) }}">
                    </div>
                </div>
                
                <hr class="my-4">
                <h5 class="mb-3 text-primary">Statutory Levies & Charges</h5>
                <div class="row g-3">
                    <div class="col-md-2 form-group">
                        <label for="t_levy_display" class="form-label">Training Levy</label>
                        <input type="text" id="t_levy_display" class="form-control bg-light" value="{{ old('t_levy', $policy->t_levy) }}" readonly>
                        <input type="hidden" id="t_levy" name="t_levy" value="{{ old('t_levy', $policy->t_levy) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="pcf_levy_display" class="form-label">PCF Levy</label>
                        <input type="text" id="pcf_levy_display" class="form-control bg-light" value="{{ old('pcf_levy', $policy->pcf_levy) }}" readonly>
                        <input type="hidden" id="pcf_levy" name="pcf_levy" value="{{ old('pcf_levy', $policy->pcf_levy) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="s_duty_display" class="form-label">Stamp Duty</label>
                        <input type="text" id="s_duty_display" class="form-control" value="{{ old('s_duty', $policy->s_duty) }}" 
                            oninput="syncHiddenFields('s_duty'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="s_duty" name="s_duty" value="{{ old('s_duty', $policy->s_duty) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="policy_charge_display" class="form-label">Policy Charge</label>
                        <input type="text" id="policy_charge_display" class="form-control" value="{{ old('policy_charge', $policy->policy_charge) }}" 
                            oninput="syncHiddenFields('policy_charge'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="policy_charge" name="policy_charge" value="{{ old('policy_charge', $policy->policy_charge) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="pvt_display" class="form-label">PVT</label>
                        <input type="text" id="pvt_display" class="form-control" value="{{ old('pvt', $policy->pvt) }}" 
                            oninput="syncHiddenFields('pvt'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="pvt" name="pvt" value="{{ old('pvt', $policy->pvt) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="other_charges_display" class="form-label">Other Charges</label>
                        <input type="text" id="other_charges_display" class="form-control" value="{{ old('other_charges', $policy->other_charges) }}" 
                            oninput="syncHiddenFields('other_charges'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="other_charges" name="other_charges" value="{{ old('other_charges', $policy->other_charges) }}">
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3 text-primary">Add-ons & Totals</h5>
                <div class="row g-3">
                    <div class="col-md-2 form-group">
                        <label for="courtesy_car_display" class="form-label">Courtesy Car</label>
                        <input type="text" id="courtesy_car_display" class="form-control" value="{{ old('courtesy_car', $policy->courtesy_car) }}" 
                            oninput="syncHiddenFields('courtesy_car'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="courtesy_car" name="courtesy_car" value="{{ old('courtesy_car', $policy->courtesy_car) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="ppl_display" class="form-label">PPL</label>
                        <input type="text" id="ppl_display" class="form-control" value="{{ old('ppl', $policy->ppl) }}" 
                            oninput="syncHiddenFields('ppl'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="ppl" name="ppl" value="{{ old('ppl', $policy->ppl) }}">
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="road_rescue_display" class="form-label">Road Rescue</label>
                        <input type="text" id="road_rescue_display" class="form-control" value="{{ old('road_rescue', $policy->road_rescue) }}" 
                            oninput="syncHiddenFields('road_rescue'); calculateFinancials()" 
                            onfocusout="formatInput(this)">
                        <input type="hidden" id="road_rescue" name="road_rescue" value="{{ old('road_rescue', $policy->road_rescue) }}">
                    </div>
                    
                    <div class="col-md-3 form-group">
                        <label for="gross_premium_display" class="form-label">Gross Premium </label>
                        <input type="text" id="gross_premium_display" class="form-control text-primary fw-bold" value="{{ old('gross_premium', $policy->gross_premium) }}" readonly>
                        <input type="hidden" id="gross_premium" name="gross_premium" value="{{ old('gross_premium', $policy->gross_premium) }}">
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="net_premium_display" class="form-label">Net Premium </label>
                        <input type="text" id="net_premium_display" class="form-control text-success fw-bold" value="{{ old('net_premium', $policy->net_premium) }}" readonly>
                        <input type="hidden" id="net_premium" name="net_premium" value="{{ old('net_premium', $policy->net_premium) }}">
                    </div>
                </div>

                {{-- Previous/Next buttons for Tab 2 (FIXED with onclick) --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="#general" class="btn btn-outline-secondary" onclick="showTab('general')">
                        <i class="bi bi-arrow-left"></i> Previous: Policy & Coverage
                    </a>
                    <a href="#attachments" class="btn btn-primary" onclick="showTab('attachments')">
                        Next: Documents <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

           
            {{-- Tab 3: Documents --}}
            <div class="tab-pane fade" id="attachments" role="tabpanel" aria-labelledby="attachments-tab">
                <h5 class="mb-3 text-primary">Attachments</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Existing Documents</h6>
                            </div>
                            <div class="card-body">
                                @if($documents->isEmpty())
                                    <p class="text-muted">No attachments found on original policy.</p>
                                @else
                                    <div class="list-group list-group-flush">
                                        @foreach($documents as $index => $document)
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <div>
                                                    <a href="{{ Storage::url($document['file'] ?? '') }}" target="_blank" class="text-decoration-none">
                                                        <i class="bi bi-file-earmark-text me-1"></i> {{ $document['original_name'] ?? basename($document['file'] ?? 'Unknown File') }} 
                                                        @if(isset($document['document_type']))
                                                            <span class="badge bg-secondary ms-1">{{ $document['document_type'] }}</span>
                                                        @endif
                                                    </a>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="keep_documents[]" value="{{ $index }}" id="docSwitch{{ $index }}" checked>
                                                    <label class="form-check-label small" for="docSwitch{{ $index }}">Keep Copy</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Upload New Documents</h6>
                            </div>
                            <div class="card-body d-flex align-items-center">
                                <div class="form-group w-100">
                                    <label for="upload_file" class="form-label">Select new attachments (optional)</label>
                                    <input type="file" id="upload_file" name="upload_file[]" class="form-control" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Previous button for Tab 3 (FIXED with onclick) --}}
                <div class="d-flex justify-content-start mt-4">
                    <a href="#financial" class="btn btn-outline-secondary" onclick="showTab('financial')">
                        <i class="bi bi-arrow-left"></i> Previous: Financial Details
                    </a>
                </div>
            </div>

        </div> {{-- End Tab Content --}}

        {{-- Form Actions (Submit) --}}
        <div class="mt-4 p-3 bg-light border rounded d-flex justify-content-between">
            <a href="{{ route('policies.show', $policy->id) }}" class="btn btn-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i> Finalize & Create Renewal
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initial setup on load
        togglePremiumFields();
        calculateFinancials();
        // Run date calculation once on load to ensure end date is correct based on pre-filled days
        calculateEndDate(); 
    });

    // =======================================================
    // FIX: Manual Bootstrap Tab Activation
    // Requires Bootstrap JS to be loaded (usually via appPages layout)
    // =======================================================
    function showTab(tabId) {
        // Get the actual tab button element using its ID (e.g., 'financial-tab')
        const tabElement = document.getElementById(tabId + '-tab');
        if (tabElement) {
            // Check if bootstrap is defined globally (Bootstrap 5)
            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                // Manually create the Bootstrap Tab instance and show it
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
                // Scroll up to the tabs list for better user experience
                document.getElementById('renewTabs').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                console.error("Bootstrap 5 JS (bootstrap.Tab) not found. Cannot manually switch tabs.");
            }
        }
    }
    // =======================================================
    // End Fix
    // =======================================================


    // Helper function to remove formatting (commas)
    function cleanNumber(value) {
        if (typeof value === 'string') {
            return parseFloat(value.replace(/,/g, '')) || 0;
        }
        return parseFloat(value) || 0;
    }

    // Helper function to format numbers with commas (currency-like)
    function formatNumber(value) {
        // Ensures the number is rounded to 2 decimal places before formatting
        if (typeof value === 'number') {
            return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return String(value);
    }

    // Syncs the display field (with commas) to the hidden field (clean number)
    function syncHiddenFields(id) {
        const displayField = document.getElementById(id + '_display');
        const hiddenField = document.getElementById(id);
        
        if (displayField && hiddenField) {
            const cleanVal = cleanNumber(displayField.value);
            hiddenField.value = cleanVal;
            // Update display field immediately with formatted value
            displayField.value = formatNumber(cleanVal);
        }
    }

    // Format input field on focus out
    function formatInput(field) {
        const cleanVal = cleanNumber(field.value);
        field.value = formatNumber(cleanVal);
    }

    // Calculates End Date based on Start Date and Days
    function calculateEndDate() {
        const startDateField = document.getElementById('start_date');
        const endDateField = document.getElementById('end_date');
        const daysField = document.getElementById('days');
        
        const startDateValue = startDateField.value;
        // Ensure days is a number and is at least 1
        const days = Math.max(1, parseInt(daysField.value) || 365); 

        if (startDateValue) {
            // Create a Date object from the start date string
            let date = new Date(startDateValue);
            
            // Add the policy duration in days to the start date
            // Subtract 1 day because policy duration is inclusive
            date.setDate(date.getDate() + days - 1); 

            // Format the new date back to 'YYYY-MM-DD' for the input field
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            endDateField.value = `${year}-${month}-${day}`;
        } else {
            endDateField.value = ''; // Clear if start date is empty
        }
    }


    // Logic to toggle Premium/Rate fields visibility
    function togglePremiumFields() {
        const premiumType = document.getElementById('premium_type').value;
        const rateDisplay = document.getElementById('rate_display').closest('.form-group');
        const premiumDisplay = document.getElementById('premium_display').closest('.form-group');

        if (premiumType === 'rate') {
            // Show Rate, Hide Premium
            rateDisplay.style.display = 'block';
            premiumDisplay.style.display = 'none';
        } else {
            // Show Premium, Hide Rate
            rateDisplay.style.display = 'none';
            premiumDisplay.style.display = 'block';
        }
    }

    // The main financial calculation logic
    function calculateFinancials() {
        const sumInsured = cleanNumber(document.getElementById('sum_insured').value);
        const rate = cleanNumber(document.getElementById('rate').value);
        let premium = cleanNumber(document.getElementById('premium').value);
        const cRate = cleanNumber(document.getElementById('c_rate').value);
        const sDuty = cleanNumber(document.getElementById('s_duty').value);
        const policyCharge = cleanNumber(document.getElementById('policy_charge').value);
        const pvt = cleanNumber(document.getElementById('pvt').value);
        const courtesyCar = cleanNumber(document.getElementById('courtesy_car').value);
        const ppl = cleanNumber(document.getElementById('ppl').value);
        const roadRescue = cleanNumber(document.getElementById('road_rescue').value);

        const premiumType = document.getElementById('premium_type').value;
        
        // 1. Calculate Premium if Rate is used
        if (premiumType === 'rate' && sumInsured > 0 && rate > 0) {
            premium = sumInsured * (rate / 100);
            document.getElementById('premium').value = premium;
            document.getElementById('premium_display').value = formatNumber(premium);
        }

        // 2. Calculate Commission (C_Rate is assumed to be a percentage)
        let commission = premium * (cRate / 100);
        
        // 3. Calculate Statutory Levies
        // WHT: Often 5% of commission
        let wht = commission * 0.05; 
        
        // Training Levy (T_Levy): Often 0.1% of Premium 
        let tLevy = premium * 0.001; 
        
        // PCF Levy: Often 0.25% of Premium
        let pcfLevy = premium * 0.0025; 

        // 4. Calculate Gross Premium (Premium + All Levies/Charges)
        let grossPremium = premium + tLevy + pcfLevy + sDuty + policyCharge + pvt + courtesyCar + ppl + roadRescue;
        
        // 5. Calculate Net Premium (Premium - Commission + WHT + Levies/Charges)
        let netPremium = (premium - commission) + wht + tLevy + pcfLevy + sDuty + policyCharge + pvt + courtesyCar + ppl + roadRescue;
        
        // 6. Update hidden and display fields for calculated values
        
        // Commission
        document.getElementById('commission').value = commission;
        document.getElementById('commission_display').value = formatNumber(commission);

        // WHT
        document.getElementById('wht').value = wht;
        document.getElementById('wht_display').value = formatNumber(wht);

        // Training Levy
        document.getElementById('t_levy').value = tLevy;
        document.getElementById('t_levy_display').value = formatNumber(tLevy);

        // PCF Levy
        document.getElementById('pcf_levy').value = pcfLevy;
        document.getElementById('pcf_levy_display').value = formatNumber(pcfLevy);

        // Gross Premium
        document.getElementById('gross_premium').value = grossPremium;
        document.getElementById('gross_premium_display').value = formatNumber(grossPremium);

        // Net Premium
        document.getElementById('net_premium').value = netPremium;
        document.getElementById('net_premium_display').value = formatNumber(netPremium);
    }
</script>

@endsection