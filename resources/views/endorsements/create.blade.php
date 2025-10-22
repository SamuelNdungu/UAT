@extends('layouts.appPages')
@section('content')
<style>
    .group-heading {
        margin-top: 20px;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
        color: #007bff;
        font-size: 1.25rem;
    }
    .form-section {
        margin-top: 30px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }
    .form-section .section-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }
</style>
<div class="container">
    <h1 class="my-4">Create Policy Endorsement</h1>

    <!-- Policy Details Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Policy Details</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2"><strong>File No:</strong> {{ $policy->fileno ?? '-' }}</div>
                <div class="col-md-4 mb-2"><strong>Policy Type:</strong> {{ $policy->policyTypeName ?? '-' }}</div>
                <div class="col-md-4 mb-2"><strong>Policy No:</strong> {{ $policy->policy_no ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-2"><strong>Reg No:</strong> {{ $policy->reg_no ?? '-' }}</div>
                <div class="col-md-4 mb-2"><strong>Insured:</strong> {{ $policy->insured ?? '-' }}</div>
                <div class="col-md-4 mb-2"><strong>Policy Period:</strong> {{ $policy->start_date ? $policy->start_date->format('d M Y') : '-' }} to {{ $policy->end_date ? $policy->end_date->format('d M Y') : '-' }}</div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-2"><strong>Sum Insured:</strong> {{ number_format($policy->sum_insured, 2) ?? '-' }}</div>
                <div class="col-md-4 mb-2"><strong>Gross Premium:</strong> {{ number_format($policy->gross_premium, 2) ?? '-' }}</div>
                <div class="col-md-4 mb-2"><strong>Status:</strong> {{ ucfirst($policy->status ?? '-') }}</div>
            </div>
        </div>
    </div>

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

    <form method="POST" action="{{ route('policies.endorsements.store', $policy->id) }}">
        @csrf

        <!-- Hidden Policy ID Field -->
        <input type="hidden" name="policy_id" value="{{ $policy->id }}">

        <!-- Endorsement Details Section -->
        <div class="form-section">
            <div class="section-title">Endorsement Details</div>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="type">Endorsement Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="addition" {{ old('type') == 'addition' ? 'selected' : '' }}>Addition</option>
                        <option value="deletion" {{ old('type') == 'deletion' ? 'selected' : '' }}>Deletion</option>
                        <option value="cancellation" {{ old('type') == 'cancellation' ? 'selected' : '' }}>Cancellation</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="effective_date">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" name="effective_date" id="effective_date" class="form-control @error('effective_date') is-invalid @enderror" value="{{ old('effective_date') }}" required>
                    @error('effective_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Financial Details Section -->
        <div class="form-section">
            <div class="section-title">Financial Details</div>
            <div class="row">
                @foreach(['sum_insured', 'rate', 'commission_rate', 'wht', 'aa_charges', 'premium', 'commission', 'net_premium', 'pvt', 'ppl', 'excess', 'courtesy_car', 's_duty', 't_levy', 'pcf_levy', 'policy_charge', 'other_charges', 'road_rescue', 'paid_amount', 'balance', 'premium_impact'] as $field)
                    <div class="col-md-4 form-group mb-3">
                        <label for="{{ $field }}">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                        <input type="text" name="{{ $field }}" id="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $policy->$field ?? '') }}">
                        @error($field)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Description Section -->
        <div class="form-section">
            <div class="section-title">Additional Information</div>
            <div class="row">
                <div class="col-md-6 form-group mb-3" id="cancellation-reason-group" style="{{ old('type') == 'cancellation' ? '' : 'display: none;' }}">
                    <label for="reason">Cancellation Reason</label>
                    <select name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror">
                        <option value="">Select Reason</option>
                        <option value="non-payment" {{ old('reason') == 'non-payment' ? 'selected' : '' }}>Non-Payment</option>
                        <option value="client-request" {{ old('reason') == 'client-request' ? 'selected' : '' }}>Client Request</option>
                        <option value="fraud" {{ old('reason') == 'fraud' ? 'selected' : '' }}>Fraud</option>
                        <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <small class="form-text text-muted">Visible when cancelling a policy.</small>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary btn-lg">Create Endorsement</button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeField = document.getElementById('type');
    const form = document.querySelector('form');

    if (!typeField || !form) {
        return;
    }
    
    // --- BLADE INJECTION ---
    // Safely inject the gross premium value from the policy object
    // Assuming $policy->gross_premium is a decimal/numeric field
    const originalGrossPremium = @json($policy->gross_premium ?? 0);
    // -----------------------

    // 1. FIELDS THAT MUST ALWAYS REMAIN POSITIVE (Rates and Multipliers)
    const rateFields = ['rate', 'commission_rate'];

    // 2. FIELDS THAT MUST FLIP SIGN (Monetary Deltas)
    const deltaFields = [
        'sum_insured', 'wht', 'aa_charges', 'premium', 'commission', 
        'net_premium', 'pvt', 'ppl', 'excess', 'courtesy_car', 's_duty', 
        't_levy', 'pcf_levy', 'policy_charge', 'other_charges', 'road_rescue', 
        'paid_amount', 'balance', 'premium_impact'
    ];
    
    // Combine all fields for listener setup
    const allFields = [...rateFields, ...deltaFields];
    const inputElements = allFields.map(id => document.getElementById(id)).filter(el => el !== null);

    // Helper: Formats a raw number to a comma-separated string with 2 decimals.
    function formatNumberWithCommas(x) {
        if (x === '' || isNaN(x)) return '';
        
        let num = parseFloat(x);
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Helper: Cleans the input value (removes commas) and returns a float (0 if invalid).
    function getRawValue(input) {
        // Remove any non-numeric characters except minus and dot (covers commas, spaces, currency symbols, NBSPs)
        let raw = input.value === undefined || input.value === null ? '' : input.value.toString();
        // normalize common unicode minus to ASCII hyphen
        raw = raw.replace(/\u2212/g, '-');
        // remove non-numeric except dot and hyphen
        const cleaned = raw.replace(/[^0-9.-]+/g, '');
        const parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }

    // Core function to apply sign logic and formatting to a single input field
    function applySignAndFormat(field) {
        let raw = getRawValue(field);
        const isRateField = rateFields.includes(field.id);
        
        if (raw === 0) {
            field.value = ''; 
            return;
        }

        // START SIGN LOGIC
        if (isRateField) {
            // Rule: Rate fields are ALWAYS positive magnitude
            raw = Math.abs(raw);
        } else {
            // Rule: Delta fields flip sign based on type
            if (typeField.value === 'deletion' || typeField.value === 'cancellation') {
                raw = -Math.abs(raw); // Ensure it's negative
            } else if (typeField.value === 'addition') {
                raw = Math.abs(raw); // Ensure it's positive
            }
        }
        // END SIGN LOGIC

        // Apply final formatting for display
        field.value = formatNumberWithCommas(raw);
    }
    
    // Function to apply logic to all fields (used on type change and load)
    function applyLogicToAllFields() {
        // --- NEW PRE-FILL LOGIC FOR PREMIUM IMPACT ---
        const premiumImpactField = document.getElementById('premium_impact');
        if (premiumImpactField && typeField.value === 'cancellation') {
            // Check if the field is currently empty (not pre-filled by old() or user input)
            if (premiumImpactField.value === '' || getRawValue(premiumImpactField) === 0) {
                 // Pre-fill with the positive gross premium value. Sign logic will run next.
                premiumImpactField.value = originalGrossPremium.toString();
            }
        }
        // ---------------------------------------------
        
        inputElements.forEach(field => {
            applySignAndFormat(field);
        });
        
        // Handle cancellation reason group visibility
        const cancellationGroup = document.getElementById('cancellation-reason-group');
        const reasonField = document.getElementById('reason');
        if (cancellationGroup) {
            const isCancellation = typeField.value === 'cancellation';
            cancellationGroup.style.display = isCancellation ? '' : 'none';
            if (reasonField) {
                reasonField.required = isCancellation;
                if (!isCancellation) {
                    reasonField.value = '';
                }
            }
        }
    }

    // --- Core Listeners ---

    // 1. Setup Focus/Blur listeners for all financial inputs
    inputElements.forEach(field => {
        // On Focus: Remove formatting (show raw digits) for easy editing
        field.addEventListener('focus', function(e) {
            let raw = getRawValue(this);
            // Clear value if 0, otherwise show raw number string
            this.value = raw === 0 ? '' : raw.toString(); 
        });

        // On Blur: Apply sign logic and formatting
        field.addEventListener('blur', function(e) {
            applySignAndFormat(this);
        });
    });

    // 2. On Type Change: Re-apply sign logic to all fields
    // This is the trigger that now handles the pre-fill logic if 'Cancellation' is selected
    typeField.addEventListener('change', applyLogicToAllFields);

    // 3. On Form Submission: Clean all fields for server processing
    form.addEventListener('submit', function(e) {
        // Before submission, ensure all fields are cleaned (no commas) and fixed to 2 decimals, respecting the sign
            inputElements.forEach(field => {
            let finalRaw = getRawValue(field);
            const isRateField = rateFields.includes(field.id);
            const originalValue = (field.value || '').toString().trim();

            if (originalValue === '') {
                field.value = '';
                return;
            }

            // Re-apply sign logic one final time to be safe for submission
            if (!isRateField) {
                if (typeField.value === 'deletion' || typeField.value === 'cancellation') {
                    finalRaw = -Math.abs(finalRaw);
                } else if (typeField.value === 'addition') {
                    finalRaw = Math.abs(finalRaw);
                }
            } else {
                // Ensure rates are always positive for submission
                finalRaw = Math.abs(finalRaw);
            }
            
            // Set the clean value for submission (e.g., -10000.00 or 15.00)
            field.value = finalRaw.toFixed(2);
        });
        
        // Final confirmation check for Cancellation
        const reasonField = document.getElementById('reason');
        if (typeField.value === 'cancellation') {
            const confirmation = confirm('⚠️ WARNING: Are you sure you want to cancel this policy? This action will result in negative financial deltas and lock the policy.');
            if (!confirmation) {
                e.preventDefault();
            }
            if (reasonField && reasonField.value === '') {
                alert('Please select a cancellation reason before proceeding.');
                e.preventDefault();
            }
        }
    });

    // On page load, apply logic if type is already set (e.g., from old() values)
    applyLogicToAllFields();
});
</script>
@endsection
