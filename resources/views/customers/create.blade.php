@extends('layouts.appPages')

@section('content')
<div class="container"> 
    <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Customer Type -->
        <div class="card ">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title">New Customer</h4>
            </div>
            <div class="card-body">
                <div class="row mt-2 mb-4">
                    <label class="col-2" for="customer_type">Customer Type:<span class="text-danger">*</span></label>
                    <div class="col-10">
                        <div class="custom-control custom-radio form-check form-check-inline">
                            <input class="custom-control-input" type="radio" id="individual" name="customer_type" value="Individual" onclick="showIndividualForm()">
                            <label class="form-check-label" for="individual">Individual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="custom-control-input" type="radio" id="corporate" name="customer_type" value="Corporate" onclick="showCorporateForm()">
                            <label class="form-check-label" for="corporate">Corporate</label>
                        </div>
                         
                    </div>
                </div>

                <!-- Individual Form -->
                <div id="individual-form" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="title">Title:</label>
                            <select id="title" name="title" class=" form-select ">
                                <option value="">Select</option>
                                <option value="Mr." {{ old('title') == 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                <option value="Mrs."{{ old('title') == 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                <option value="Ms."{{ old('title') == 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                <option value="Prof."{{ old('title') == 'Prof.' ? 'selected' : '' }}>Prof.</option>
                                <option value="Pst."{{ old('title') == 'Pst.' ? 'selected' : '' }}>Pst.</option>
                                <option value="Hon."{{ old('title') == 'Hon.' ? 'selected' : '' }}>Hon.</option>
                                <option value="Rev."{{ old('title') == 'Rev.' ? 'selected' : '' }}>Rev.</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="first_name">First Name:<span class="text-danger">*</span></label>
                            <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
                            
                        </div>
                        <div class="col-md-3">
                            <label for="last_name">Last Name:<span class="text-danger">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
                           
                        </div>
                        <div class="col-md-3">
                            <label for="surname">Surname:</label>
                            <input type="text" id="surname" name="surname" class="form-control" value="{{ old('surname') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="id_number">ID Number:<span class="text-danger">*</span></label>
                            <input type="text" id="id_number" name="id_number" class="form-control @error('id_number') is-invalid @enderror" value="{{ old('id_number') }}">
                            @if ($errors->has('id_number'))<div class="text-danger">">{{ $errors->first('id_number') }}</div>
                            @endif
 
                        </div>
                        <div class="col-md-3">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" id="dob" name="dob" class="form-control" value="{{ old('dob') }}" >
                        </div>
                        <div class="col-md-3">
                            <label for="occupation">Occupation:</label>
                            <input type="text" id="occupation" name="occupation" class="form-control" value="{{ old('occupation') }}">
                        </div>
                    </div>
                </div>

                <!-- Corporate Form -->
                <div id="corporate-form" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="corporate_name">Company Name:<span class="text-danger">*</span></label>
                            <input type="text" id="corporate_name" name="corporate_name" class="form-control @error('corporate_name') is-invalid @enderror" value="{{ old('corporate_name') }}">
                           
                        </div>
                        <div class="col-md-2">
                            <label for="business_no">Company No:</label>
                            <input type="text" id="business_no" name="business_no" class="form-control" value="{{ old('business_no') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="industry_class">Industry Class:</label>
                            <input type="text" id="industry_class" name="industry_class" class="form-control" value="{{ old('industry_class') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="industry_segment">Industry Segment:</label>
                            <input type="text" id="industry_segment" name="industry_segment" class="form-control" value="{{ old('industry_segment') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="contact_person">Contact Person:<span class="text-danger">*</span></label>
                            <input type="text" id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                        </div>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="row mb-3">

                <div class="col-md-3">
                    <label for="email">Email:<span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-2">
                    <label for="phone">Phone:<span class="text-danger">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" pattern="^\d{10}$" required>
                </div>

                    <div class="col-md-4">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" value="{{ old('postal_code') }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="city">City:<span class="text-danger">*</span></label>
                        <input type="text" id="city" name="city" class="form-control @error('city') is-invalid @enderror"  value="{{ old('city') }}">
 
                    </div>
                    <div class="col-md-2">
                        <label for="county">County:</label>
                        <input type="text" id="county" name="county" class="form-control" value="{{ old('county') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="country">Country:</label>
                        <input type="text" id="country" name="country" class="form-control" value="Kenya" value="{{ old('country') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="kra_pin">KRA PIN:<span class="text-danger">*</span></label>
                        <input type="text" id="kra_pin" name="kra_pin" class="form-control" value="{{ old('kra_pin') }}">
                            @if ($errors->has('kra_pin'))<div class="text-danger"> ">{{ $errors->first('kra_pin') }}</div>@endif
                    </div>
                    <div class="col-md-3">
                        <label for="agent_id">Agent</label>
                        <select name="agent_id" id="agent_id" class="form-control">
                            <option value="">-- Select Agent --</option>
                            @foreach(\App\Models\Agent::all() as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->agent_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
    <div class="group-heading mt-3">Documents</div>
    <div class="mb-2 small-muted">Add multiple documents with descriptions for better organization.</div>

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
                    @if(old('document_description'))
                        @foreach(old('document_description') as $key => $description)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <input type="text" name="document_description[]" class="form-control" placeholder="Enter description" value="{{ $description }}">
                                    @error('document_description.'.$key) <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td>
                                    <input type="file" name="upload_file[]" class="form-control">
                                    @error('upload_file.'.$key) <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeDocumentRow(this)">Remove</button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- Initial empty row --}}
                        <tr>
                            <td>1</td>
                            <td>
                                <input type="text" name="document_description[]" class="form-control" placeholder="Enter description">
                            </td>
                            <td>
                                <input type="file" name="upload_file[]" class="form-control">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeDocumentRow(this)">Remove</button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary" onclick="addDocumentRow()">Add Document</button>
        </div>
    </div>
</div>

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="notes">Notes:</label>
                        <textarea id="notes" name="notes" class="form-control" value="{{ old('notes') }}"></textarea>
                    </div>
                </div>
                <div class="rowr">
                <button type="submit" class="btn btn-primary">Save</button>
                </div>
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
</script>
<script>
document.getElementById('email').addEventListener('input', function() {
    var emailField = this;
    var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(emailField.value)) {
        emailField.setCustomValidity('Please enter a valid email address.');
    } else {
        emailField.setCustomValidity('');
    }
});

document.getElementById('phone').addEventListener('input', function() {
    var phoneField = this;
    var phonePattern = /^\d{10}$/;
    if (!phonePattern.test(phoneField.value)) {
        phoneField.setCustomValidity('Please enter a valid 10-digit phone number.');
    } else {
        phoneField.setCustomValidity('');
    }
});

// Document Management Functions for Customers
function addDocumentRow() {
    const table = document.getElementById('documentsTable').getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length;
    
    const newRow = table.insertRow();
    newRow.innerHTML = `
        <td>${rowCount + 1}</td>
        <td>
            <input type="text" name="document_description[]" class="form-control" placeholder="Enter description">
        </td>
        <td>
            <input type="file" name="upload_file[]" class="form-control">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeDocumentRow(this)">Remove</button>
        </td>
    `;
    
    updateRowNumbers();
}

function removeDocumentRow(button) {
    const row = button.closest('tr');
    const table = document.getElementById('documentsTable').getElementsByTagName('tbody')[0];
    
    if (table.rows.length > 1) {
        row.remove();
        updateRowNumbers();
    } else {
        alert('You need at least one document row.');
    }
}

function updateRowNumbers() {
    const table = document.getElementById('documentsTable').getElementsByTagName('tbody')[0];
    const rows = table.rows;
    
    for (let i = 0; i < rows.length; i++) {
        rows[i].cells[0].textContent = i + 1;
    }
}
</script>

@endsection