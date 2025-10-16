@extends('layouts.appPages')

@section('content')
<div class="container">
    {{-- General Error Block --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops! There were some problems with your input.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data" id="customerForm">
        @csrf
        @method('PUT')
        
        @php
            // Logic to determine the currently selected status (or old input) for the dropdown.
            $rawStatus = $customer->status;
            $rawLower = strtolower((string) $rawStatus);

            if (in_array($rawLower, ['1', 'true', 'yes', 'active', 'activated'], true)) {
                $normalizedStatus = 'Active';
            } elseif (in_array($rawLower, ['0', 'false', 'no', 'inactive', 'deactivated'], true)) {
                $normalizedStatus = 'Inactive';
            } elseif ($rawLower === 'blacklisted') {
                $normalizedStatus = 'Blacklisted';
            } else {
                $normalizedStatus = (string) $rawStatus;
            }

            $mapToStored = function($val) {
                $v = strtolower((string)$val);
                if (in_array($v, ['1', 'true', 'yes', 'active', 'activated'], true)) return '1';
                if (in_array($v, ['0', 'false', 'no', 'inactive', 'deactivated'], true)) return '0';
                if ($v === 'blacklisted') return 'Blacklisted';
                return (string)$val;
            };

            $storedStatusValue = $mapToStored(old('status', $normalizedStatus));
        @endphp

        <div class="card card-danger">
            <div class="card-header">
                <h4 class="card-title">Update Customer Details</h4>
            </div>
            <div class="card-body">
                <div class="row mt-2 mb-4">
                    <label class="col-2" for="customer_type">Customer Type:</label>
                    <div class="col-10">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="individual" name="customer_type" value="Individual" onclick="showIndividualForm()" {{ old('customer_type', $customer->customer_type) == 'Individual' ? 'checked' : '' }}>
                            <label class="form-check-label" for="individual">Individual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="corporate" name="customer_type" value="Corporate" onclick="showCorporateForm()" {{ old('customer_type', $customer->customer_type) == 'Corporate' ? 'checked' : '' }}>
                            <label class="form-check-label" for="corporate">Corporate</label>
                        </div>
                        {{-- Error for customer_type --}}
                        @error('customer_type')
                            <div class="text-danger small mt-1" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>
                </div>

                <div id="individual-form" style="display: {{ old('customer_type', $customer->customer_type) == 'Individual' ? 'block' : 'none' }};">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="title">Title:</label>
                            <select id="title" name="title" class="form-control @error('title') is-invalid @enderror">
                                <option value="">Select Title</option>
                                <option value="Mr." {{ old('title', $customer->title) == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                <option value="Mrs." {{ old('title', $customer->title) == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                <option value="Ms." {{ old('title', $customer->title) == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                <option value="Prof." {{ old('title', $customer->title) == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                                <option value="Pst." {{ old('title', $customer->title) == 'Pst.' ? 'selected' : '' }}>Pst.</option>
                                <option value="Hon." {{ old('title', $customer->title) == 'Hon.' ? 'selected' : '' }}>Hon.</option>
                                <option value="Rev." {{ old('title', $customer->title) == 'Rev.' ? 'selected' : '' }}>Rev.</option>
                            </select>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" name="first_name" 
                                class="form-control @error('first_name') is-invalid @enderror"  
                                value="{{ old('first_name', $customer->first_name) }}"
                                placeholder="Required for Individual">
                            @error('first_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" name="last_name" 
                                class="form-control @error('last_name') is-invalid @enderror" 
                                value="{{ old('last_name', $customer->last_name) }}"
                                placeholder="Required for Individual">
                            @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="surname">Surname:</label>
                            <input type="text" id="surname" name="surname" 
                                class="form-control @error('surname') is-invalid @enderror"  
                                value="{{ old('surname', $customer->surname) }}">
                            @error('surname')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="id_number">ID Number:</label>
                            <input type="text" id="id_number" name="id_number" 
                                class="form-control @error('id_number') is-invalid @enderror" 
                                value="{{ old('id_number', $customer->id_number) }}">
                            @error('id_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" id="dob" name="dob" 
                                class="form-control @error('dob') is-invalid @enderror" 
                                value="{{ old('dob', $customer->dob) }}">
                            @error('dob')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="occupation">Occupation:</label>
                            <input type="text" id="occupation" name="occupation" 
                                class="form-control @error('occupation') is-invalid @enderror" 
                                value="{{ old('occupation', $customer->occupation) }}">
                            @error('occupation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div id="corporate-form" style="display: {{ old('customer_type', $customer->customer_type) == 'Corporate' ? 'block' : 'none' }};">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="corporate_name">Company Name:</label>
                            <input type="text" id="corporate_name" name="corporate_name" 
                                class="form-control @error('corporate_name') is-invalid @enderror" 
                                value="{{ old('corporate_name', $customer->corporate_name) }}"
                                placeholder="Required for Corporate">
                            @error('corporate_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="business_no">Reg No:</label>
                            <input type="text" id="business_no" name="business_no" 
                                class="form-control @error('business_no') is-invalid @enderror" 
                                value="{{ old('business_no', $customer->business_no) }}">
                            @error('business_no')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="industry_class">Industry Class:</label>
                            <input type="text" id="industry_class" name="industry_class" 
                                class="form-control @error('industry_class') is-invalid @enderror" 
                                value="{{ old('industry_class', $customer->industry_class) }}">
                            @error('industry_class')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="industry_segment">Industry Segment:</label>
                            <input type="text" id="industry_segment" name="industry_segment" 
                                class="form-control @error('industry_segment') is-invalid @enderror" 
                                value="{{ old('industry_segment', $customer->industry_segment) }}">
                            @error('industry_segment')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="contact_person">Contact Person:</label>
                            <input type="text" id="contact_person" name="contact_person" 
                                class="form-control @error('contact_person') is-invalid @enderror" 
                                value="{{ old('contact_person', $customer->contact_person) }}">
                            @error('contact_person')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3"> 
                    <div class="col-md-3">
                         <label for="email">Email:</label>
                         <input type="email" id="email" name="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $customer->email) }}"
                            placeholder="e.g., user@example.com">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="phone">Phone:</label>
                        <input type="tel" id="phone" name="phone" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            value="{{ old('phone', $customer->phone) }}"
                            placeholder="e.g., +2547XXXXXXXX">
                        @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" 
                            class="form-control @error('address') is-invalid @enderror" 
                            value="{{ old('address', $customer->address) }}">
                        @error('address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" name="postal_code" 
                            class="form-control @error('postal_code') is-invalid @enderror" 
                            value="{{ old('postal_code', $customer->postal_code) }}">
                        @error('postal_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" 
                            class="form-control @error('city') is-invalid @enderror" 
                            value="{{ old('city', $customer->city) }}">
                        @error('city')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="county">County:</label>
                        <input type="text" id="county" name="county" 
                            class="form-control @error('county') is-invalid @enderror" 
                            value="{{ old('county', $customer->county) }}">
                        @error('county')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label for="country">Country:</label>
                        <input type="text" id="country" name="country" 
                            class="form-control @error('country') is-invalid @enderror" 
                            value="{{ old('country', $customer->country) }}">
                        @error('country')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="kra_pin">KRA PIN:</label>
                        <input type="text" id="kra_pin" name="kra_pin" 
                            class="form-control @error('kra_pin') is-invalid @enderror" 
                            value="{{ old('kra_pin', $customer->kra_pin) }}"
                            maxlength="11" 
                            placeholder="e.g., P051365947X">
                        @error('kra_pin')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <div class="custom-file">
                            <label for="documents">Upload:</label>
                            <input type="file" id="documents" name="documents[]" 
                                class="form-control @error('documents') is-invalid @enderror @error('documents.*') is-invalid @enderror" multiple> 
                            @error('documents')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            @error('documents.*')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div> 
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="notes">Notes:</label>
                        <textarea id="notes" name="notes" 
                            class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $customer->notes) }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                
                    <div class="col-md-2">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="1" {{ $storedStatusValue === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $storedStatusValue === '0' ? 'selected' : '' }}>Inactive</option>
                            <option value="Blacklisted" {{ $storedStatusValue === 'Blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </div>
    </form>
</div>

<script>
    function showIndividualForm() {
        document.getElementById('individual-form').style.display = 'block';
        document.getElementById('corporate-form').style.display = 'none';
    }

    function showCorporateForm() {
        document.getElementById('individual-form').style.display = 'none';
        document.getElementById('corporate-form').style.display = 'block';
    }

    window.onload = function() {
        const individualChecked = document.getElementById('individual').checked;
        const corporateChecked = document.getElementById('corporate').checked;

        if (individualChecked) {
            showIndividualForm();
        } else if (corporateChecked) {
            showCorporateForm();
        }
    }
</script>
@endsection