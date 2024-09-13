@extends('layouts.appPages')

@section('content')
<div class="container"> 
    <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Customer Type -->
        <div class="card card-danger">
            <div class="card-header">
                <h4 class="card-title">Update Customer Details</h4>
            </div>
            <div class="card-body">
                <div class="row mt-2 mb-4">
                    <label class="col-2" for="customer_type">Customer Type:</label>
                    <div class="col-10">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="individual" name="customer_type" value="Individual" onclick="showIndividualForm()" {{ $customer->customer_type == 'Individual' ? 'checked' : '' }}>
                            <label class="form-check-label" for="individual">Individual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="corporate" name="customer_type" value="Corporate" onclick="showCorporateForm()" {{ $customer->customer_type == 'Corporate' ? 'checked' : '' }}>
                            <label class="form-check-label" for="corporate">Corporate</label>
                        </div>
                    </div>
                </div>

                <!-- Individual Form -->
                <div id="individual-form" style="display: {{ $customer->customer_type == 'Individual' ? 'block' : 'none' }};">
                    <div class="row mb-3">
                        <div class="col-md-2">
                        <label for="title">Title:</label>
                            <select id="title" name="title" class="form-control">
                                <option value="">Select Title</option>
                                <option value="Mr." {{ $customer->title == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                <option value="Mrs." {{ $customer->title == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                <option value="Ms." {{ $customer->title == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                <option value="Prof." {{ $customer->title == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                                <option value="Pst." {{ $customer->title == 'Pst.' ? 'selected' : '' }}>Pst.</option>
                                <option value="Hon." {{ $customer->title == 'Hon.' ? 'selected' : '' }}>Hon.</option>
                                <option value="Rev." {{ $customer->title == 'Rev.' ? 'selected' : '' }}>Rev.</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                        <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" name="first_name" class="form-control"  value="{{ $customer->first_name }}">
                        </div>
                        <div class="col-md-3">
                        <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="{{ $customer->last_name }}">
                        </div>
                        <div class="col-md-3">
                        <label for="surname">Surname:</label>
                            <input type="text" id="surname" name="surname" class="form-control"  value="{{ $customer->surname }}">
                        </div>
                    </div>

                    <!---  -->
                    <div class="row mb-3">
                    <div class="col-md-2">
                            <label for="id_number">ID Number:</label>
                            <input type="text" id="id_number" name="id_number" class="form-control" value="{{ $customer->id_number }}">
                        </div>

                        <div class="col-md-3">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" id="dob" name="dob" class="form-control" value="{{ $customer->dob }}">
                        </div>
                        <div class="col-md-3">
                            <label for="occupation">Occupation:</label>
                            <input type="text" id="occupation" name="occupation" class="form-control" value="{{ $customer->occupation }}">
                        </div>

                    </div>
                    <!--  -->
                </div>

                <!-- Corporate Form -->
                <div id="corporate-form" style="display: {{ $customer->customer_type == 'Corporate' ? 'block' : 'none' }};">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="corporate_name">Company Name:</label>
                            <input type="text" id="corporate_name" name="corporate_name" class="form-control" value="{{ $customer->corporate_name }}">
                        </div>
                        <div class="col-md-2">
                            <label for="business_no">Reg No:</label>
                            <input type="text" id="business_no" name="business_no" class="form-control" value="{{ $customer->business_no }}">
                        </div>
                        <div class="col-md-2">
                            <label for="industry_class">Industry Class:</label>
                            <input type="text" id="industry_class" name="industry_class" class="form-control" value="{{ $customer->industry_class  }}">
                        </div>
                        <div class="col-md-2">
                            <label for="industry_segment">Industry Segment:</label>
                            <input type="text" id="industry_segment" name="industry_segment" class="form-control" value="{{ $customer->industry_segment }}">
                        </div>
                        <div class="col-md-3">
                            <label for="contact_person">Contact Person:</label>
                            <input type="text" id="contact_person" name="contact_person" class="form-control" value="{{ $customer->contact_person }}">
                        </div>
                    </div>



                    </div>
                <!--  -->
                <div class="row mb-3">

                <!--  -->
                </div>

                <!-- Common Fields -->
                <div class="row mb-3"> 


                        <div class="col-md-3">
                             <label for="email">Email:</label>
                             <input type="email" id="email" name="email" class="form-control" value="{{ $customer->email }}">
                        </div>
                        <div class="col-md-2">
                            <label for="phone">Phone:</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ $customer->phone }}">
                        </div>

                        <div class="col-md-4">
                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address" class="form-control" value="{{ $customer->address }}">
                        </div>
                        <div class="col-md-2">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" value="{{ $customer->postal_code }}">
                    </div>
                        </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" class="form-control" value="{{ $customer->city }}">
                    </div>
                    <div class="col-md-2">
                        <label for="county">County:</label>
                        <input type="text" id="county" name="county" class="form-control" value="{{ $customer->county }}">
                    </div>

                    <div class="col-md-2">
                        <label for="country">Country:</label>
                        <input type="text" id="country" name="country" class="form-control" value="{{ $customer->country }}">
                    </div>
                    <div class="col-md-2">
                            <label for="kra_pin">KRA PIN:</label>
                            <input type="text" id="kra_pin" name="kra_pin" class="form-control" value="{{ $customer->kra_pin }}">
                        </div>

                        <div class="col-md-3">
                        <div class="custom-file">
                            <label for="documents">Upload:</label>
                            <input type="file" id="documents" name="documents" class="form-control" multiple> <!-- Add "multiple" attribute and make name an array -->
                        </div> 
                        </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="notes">Notes:</label>
                        <textarea id="notes" name="notes" class="form-control">{{ $customer->notes }}</textarea>
                    </div>
                
                <div class="col-md-2">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                    <option value="1" {{ $customer->status ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$customer->status ? 'selected' : '' }}>Inactive</option>
                    </select>

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

    // Automatically show the correct form based on the initial customer type
    window.onload = function() {
        if (document.querySelector('input[name="customer_type"]:checked')) {
            if (document.getElementById('individual').checked) {
                showIndividualForm();
            } else if (document.getElementById('corporate').checked) {
                showCorporateForm();
            }
        }
    }
</script>
@endsection
