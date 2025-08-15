@extends('layouts.appPages')

@section('content')

<style>
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

    /* Toggle Switch Styling */
    .switch {
        --_switch-bg-clr: #70a9c5;
        --_switch-padding: 2px;
        --_slider-bg-clr: rgba(12, 74, 110, 0.65);
        --_slider-bg-clr-on: rgba(12, 74, 110, 1);
        --_slider-txt-clr: #ffffff;
        --_label-padding: 0.8rem 1.3rem;
        --_switch-easing: cubic-bezier(0.47, 1.64, 0.41, 0.8);
        color: white;
        width: fit-content;
        display: flex;
        justify-content: center;
        position: relative;
        border-radius: 7px;
        cursor: pointer;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        position: relative;
        isolation: isolate;
        font-size: 0.9rem;
    }

    /* Customer Search Dropdown Styling */
    .search-dropdown {
        position: relative;
    }

    .search-dropdown input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .search-results div {
        padding: 8px;
        cursor: pointer;
    }

    .search-results div:hover {
        background-color: #f8f9fa;
    }

    .switch input[type="checkbox"] {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }

    .switch > span {
        display: grid;
        place-content: center;
        transition: opacity 300ms ease-in-out 150ms;
        padding: var(--_label-padding);
    }

    .switch::before,
    .switch::after {
        content: "";
        position: absolute;
        border-radius: inherit;
        transition: inset 150ms ease-in-out;
    }

    .switch::before {
        background-color: var(--_slider-bg-clr);
        inset: var(--_switch-padding) 50% var(--_switch-padding) var(--_switch-padding);
        transition: inset 500ms var(--_switch-easing), background-color 500ms ease-in-out;
        z-index: -1;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.3);
    }

    .switch::after {
        background-color: var(--_switch-bg-clr);
        inset: 0;
        z-index: -2;
    }

    .switch:focus-within::after {
        inset: -0.25rem;
    }

    .switch:has(input:checked):hover > span:first-of-type,
    .switch:has(input:not(:checked)):hover > span:last-of-type {
        opacity: 1;
        transition-delay: 0ms;
        transition-duration: 100ms;
    }

    .switch:has(input:checked):hover::before {
        inset: var(--_switch-padding) var(--_switch-padding) var(--_switch-padding) 45%;
    }

    .switch:has(input:not(:checked)):hover::before {
        inset: var(--_switch-padding) 45% var(--_switch-padding) var(--_switch-padding);
    }

    .switch:has(input:checked)::before {
        background-color: var(--_slider-bg-clr-on);
        inset: var(--_switch-padding) var(--_switch-padding) var(--_switch-padding) 50%;
    }

    .switch > span:last-of-type,
    .switch > input:checked + span:first-of-type {
        opacity: 0.75;
    }

    .switch > input:checked ~ span:last-of-type {
        opacity: 1;
    }
</style>

<div class="container">
    <h1 class="my-4">Create Lead</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('leads.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

     

        <!-- Lead Type Section -->
        <div class="group-heading d-flex justify-content-between align-items-center row mb-4">
            <span>Select Lead Type</span>
        </div>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="filter" class="switch" aria-label="Toggle Lead Type">
                        <input type="checkbox" id="filter" name="lead_type" value="Corporate" {{ old('lead_type', 'Individual') === 'Corporate' ? 'checked' : '' }}>
                        <span>Individual</span>
                        <span>Corporate</span>
                    </label>
                    <input type="hidden" name="lead_type" value="Individual">
                    @error('lead_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="group-heading d-flex justify-content-between align-items-center">
            <span>Contact Information</span>
        </div>
        <div class="row">
            <div id="corporate_fields" class="col-md-6 d-none">
                <div class="form-group">
                    <label for="corporate_name">Corporate Name <span class="text-danger">*</span></label>
                    <input type="text" id="corporate_name" name="corporate_name" class="form-control @error('corporate_name') is-invalid @enderror" value="{{ old('corporate_name') }}">
                    @error('corporate_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_name">Contact Name <span class="text-danger">*</span></label>
                    <input type="text" id="contact_name" name="contact_name" class="form-control @error('contact_name') is-invalid @enderror" value="{{ old('contact_name') }}">
                    @error('contact_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div id="individual_fields" class="col-md-6">
                <div class="form-group">
                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile <span class="text-danger">*</span></label>
                    <input type="text" id="mobile" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" required>
                    @error('mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Policy Details Section -->
        <div class="group-heading d-flex justify-content-between align-items-center">
            <span>Policy Details</span>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="policy_type">Policy Type <span class="text-danger">*</span></label>
                    <input type="text" id="policy_type" name="policy_type" class="form-control @error('policy_type') is-invalid @enderror" value="{{ old('policy_type') }}" required>
                    @error('policy_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="lead_source">Lead Source</label>
                    <input type="text" id="lead_source" name="lead_source" class="form-control @error('lead_source') is-invalid @enderror" value="{{ old('lead_source') }}">
                    @error('lead_source')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="upload">Upload Documents</label>
                    <input type="file" id="upload" name="upload[]" class="form-control @error('upload') is-invalid @enderror" multiple disabled>
                    @error('upload')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Deal Information Section -->
        <div class="group-heading d-flex justify-content-between align-items-center">
            <span>Deal Information</span>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="deal_size">Deal Size</label>
                    <input type="number" id="deal_size" name="deal_size" class="form-control @error('deal_size') is-invalid @enderror" value="{{ old('deal_size') }}" step="0.01" min="0" onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46">
                    @error('deal_size')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="probability">Probability (%)</label>
                    <input type="number" id="probability" name="probability" class="form-control @error('probability') is-invalid @enderror" value="{{ old('probability') }}" min="0" max="100" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    @error('probability')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="weighted_revenue_forecast">Weighted Revenue Forecast</label>
                    <input type="number" id="weighted_revenue_forecast" name="weighted_revenue_forecast" class="form-control @error('weighted_revenue_forecast') is-invalid @enderror" value="{{ old('weighted_revenue_forecast') }}" step="0.01" min="0" onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46">
                    @error('weighted_revenue_forecast')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="deal_stage">Deal Stage</label>
                    <select id="deal_stage" name="deal_stage" class="form-control @error('deal_stage') is-invalid @enderror">
                        <option value="">Select Deal Stage</option>
                        <option value="Qualification" {{ old('deal_stage') === 'Qualification' ? 'selected' : '' }}>Qualification</option>
                        <option value="Proposal" {{ old('deal_stage') === 'Proposal' ? 'selected' : '' }}>Proposal</option>
                        <option value="Negotiating" {{ old('deal_stage') === 'Negotiating' ? 'selected' : '' }}>Negotiating</option>
                        <option value="Closed-Won" {{ old('deal_stage') === 'Closed-Won' ? 'selected' : '' }}>Closed-Won</option>
                        <option value="Closed-Lost" {{ old('deal_stage') === 'Closed-Lost' ? 'selected' : '' }}>Closed-Lost</option>
                    </select>
                    @error('deal_stage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="deal_status">Deal Status</label>
                    <select id="deal_status" name="deal_status" class="form-control @error('deal_status') is-invalid @enderror">
                        <option value="">Select Deal Status</option>
                        <option value="Open" {{ old('deal_status') === 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="Won" {{ old('deal_status') === 'Won' ? 'selected' : '' }}>Won</option>
                        <option value="Lost" {{ old('deal_status') === 'Lost' ? 'selected' : '' }}>Lost</option>
                        <option value="On Hold" {{ old('deal_status') === 'On Hold' ? 'selected' : '' }}>On Hold</option>
                    </select>
                    @error('deal_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="date_initiated">Date Initiated</label>
                    <input type="date" id="date_initiated" name="date_initiated" class="form-control @error('date_initiated') is-invalid @enderror" value="{{ old('date_initiated') }}">
                    @error('date_initiated')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="closing_date">Closing Date</label>
                    <input type="date" id="closing_date" name="closing_date" class="form-control @error('closing_date') is-invalid @enderror" value="{{ old('closing_date') }}">
                    @error('closing_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="follow_up_date">Follow-up Date</label>
                    <input type="date" id="follow_up_date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date') }}">
                    @error('follow_up_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="next_action">Next Action <span class="text-danger">*</span></label>
                    <input type="text" id="next_action" name="next_action" class="form-control @error('next_action') is-invalid @enderror" value="{{ old('next_action') }}" required>
                    @error('next_action')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Create Lead</button>
                <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
// Function to handle lead type changes
function handleLeadTypeChange(e) {
    const corporateFields = document.getElementById('corporate_fields');
    const individualFields = document.getElementById('individual_fields');
    const isChecked = e.target ? e.target.checked : false;

    if (isChecked) {
        corporateFields.classList.remove('d-none');
        individualFields.classList.add('d-none');
        document.getElementById('filter').value = 'Corporate';
    } else {
        corporateFields.classList.add('d-none');
        individualFields.classList.remove('d-none');
        document.getElementById('filter').value = 'Individual';
    }
}

// Add event listener to the checkbox
document.getElementById('filter').addEventListener('change', handleLeadTypeChange);

// Call the function on page load to set initial state
const initialState = document.getElementById('filter').checked;
handleLeadTypeChange({ target: { checked: initialState } });

// Update hidden input when checkbox changes
document.getElementById('filter').addEventListener('change', function(e) {
    document.querySelector('input[type="hidden"][name="lead_type"]').value = e.target.checked ? 'Corporate' : 'Individual';
});

// Add validation for numeric inputs
const numericInputs = document.querySelectorAll('input[type="number"]');
numericInputs.forEach(input => {
    input.addEventListener('input', function(e) {
        if (this.value < 0) this.value = 0;
        if (this.id === 'probability' && this.value > 100) this.value = 100;
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customer_search');
    const searchResults = document.getElementById('search_results');
    const selectedCustomerId = document.getElementById('selected_customer_id');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const searchTerm = this.value.trim();

        if (searchTerm.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/customers/search?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(customer => {
                            const div = document.createElement('div');
                            div.textContent = `${customer.first_name} ${customer.last_name} - ${customer.email}`;
                            div.onclick = () => {
                                searchInput.value = `${customer.first_name} ${customer.last_name}`;
                                selectedCustomerId.value = customer.id;
                                searchResults.style.display = 'none';
                                // Auto-fill other fields if needed
                            };
                            searchResults.appendChild(div);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 300);
    });

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});
</script>

@endsection

@push('scripts')
<script src="{{ asset('js/customer-search.js') }}"></script>
@endpush
