@extends('layouts.appPages')

@section('content')
<div class="container"> 
    <form>
        <!-- Customer Type -->
        <div class="card card-danger">
            <div class="card-header">
                <h4 class="card-title">Customer Details</h4>
            </div>
            <div class="card-body">
                <div class="row mt-2 mb-4">
                    <label class="col-2" for="customer_type">Customer Type:</label>
                    <div class="col-10">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="individual" name="customer_type" value="Individual" disabled {{ $customer->customer_type == 'Individual' ? 'checked' : '' }}>
                            <label class="form-check-label" for="individual">Individual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="corporate" name="customer_type" value="Corporate" disabled {{ $customer->customer_type == 'Corporate' ? 'checked' : '' }}>
                            <label class="form-check-label" for="corporate">Corporate</label>
                        </div>
                    </div>
                </div>

                <!-- Individual Form -->
                <div id="individual-form" style="display: {{ $customer->customer_type == 'Individual' ? 'block' : 'none' }};">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="title">Title:</label>
                            <input type="text" id="title" class="form-control" value="{{ $customer->title }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" class="form-control" value="{{ $customer->first_name }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" class="form-control" value="{{ $customer->last_name }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="surname">Surname:</label>
                            <input type="text" id="surname" class="form-control" value="{{ $customer->surname }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="id_number">ID Number:</label>
                            <input type="text" id="id_number" class="form-control" value="{{ $customer->id_number }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" id="dob" class="form-control" value="{{ $customer->dob }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="occupation">Occupation:</label>
                            <input type="text" id="occupation" class="form-control" value="{{ $customer->occupation }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Corporate Form -->
                <div id="corporate-form" style="display: {{ $customer->customer_type == 'Corporate' ? 'block' : 'none' }};">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="corporate_name">Company Name:</label>
                            <input type="text" id="corporate_name" class="form-control" value="{{ $customer->corporate_name }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="business_no">Reg No:</label>
                            <input type="text" id="business_no" class="form-control" value="{{ $customer->business_no }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="industry_class">Industry Class:</label>
                            <input type="text" id="industry_class" class="form-control" value="{{ $customer->industry_class }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="industry_segment">Industry Segment:</label>
                            <input type="text" id="industry_segment" class="form-control" value="{{ $customer->industry_segment }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="contact_person">Contact Person:</label>
                            <input type="text" id="contact_person" class="form-control" value="{{ $customer->contact_person }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="row mb-3"> 
                    <div class="col-md-3">
                        <label for="email">Email:</label>
                        <input type="email" id="email" class="form-control" value="{{ $customer->email }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" class="form-control" value="{{ $customer->phone }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="address">Address:</label>
                        <input type="text" id="address" class="form-control" value="{{ $customer->address }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" class="form-control" value="{{ $customer->postal_code }}" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="city">City:</label>
                        <input type="text" id="city" class="form-control" value="{{ $customer->city }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label for="county">County:</label>
                        <input type="text" id="county" class="form-control" value="{{ $customer->county }}" readonly>
                    </div>

                    <div class="col-md-2">
                        <label for="country">Country:</label>
                        <input type="text" id="country" class="form-control" value="{{ $customer->country }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label for="kra_pin">KRA PIN:</label>
                        <input type="text" id="kra_pin" class="form-control" value="{{ $customer->kra_pin }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <label for="documents">Uploaded Files:</label><br>
                        
                        @if($customer->documents)
                            @php
                                $filePath = public_path('storage/documents/' . basename($customer->documents));
                            @endphp
                            @if(file_exists($filePath))
                                @php
                                    $fileName = basename($customer->documents);
                                @endphp
                                <a href="{{ asset('storage/documents/' . $fileName) }}" download>{{ $fileName }}</a>
                            @else
                                File not found
                            @endif
                        @endif


                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="notes">Notes:</label>
                        <textarea id="notes" class="form-control" readonly>{{ $customer->notes }}</textarea>
                    </div>
                    <div class="col-md-2">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" disabled>
                        <option value="1" {{ $customer->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$customer->status ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                    
                </div>
                <!-- Edit Button -->
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </form>
</div>

<script>
    window.onload = function() {
        if (document.querySelector('input[name="customer_type"]:checked')) {
            if (document.getElementById('individual').checked) {
                document.getElementById('individual-form').style.display = 'block';
            } else if (document.getElementById('corporate').checked) {
                document.getElementById('corporate-form').style.display = 'block';
            }
        }
    }
</script>
@endsection
