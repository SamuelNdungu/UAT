@extends('layouts.appPages')

@section('content')
<div class="container"> 
    <style>
        /* Local improvements for customer show */
        .customer-card-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:0.75rem; }
        .customer-badge { font-size:0.85rem; padding:.35rem .6rem; border-radius:.35rem; color:#fff; }
        .badge-active { background:#198754; }
        .badge-inactive { background:#6c757d; }
        .detail-label { font-weight:600; color:#0d6efd; width:140px; display:inline-block; }
        .muted { color:#6c757d; }
        .doc-link { display:block; margin-bottom:.25rem; }
        @media (max-width:767.98px){ .detail-label{ display:block; width:100%; margin-bottom:.25rem; } }
    </style>

    <form>
        <div class="card card-danger">
            <div class="card-header">
                <h4 class="card-title">Customer Details</h4>
            </div>
            <div class="card-body">
                <div class="customer-card-head">
                    <div>
                        <h5 class="mb-0">{{ $customer->customer_type }} Customer</h5>
                        <small class="muted">Code: {{ $customer->customer_code }}</small>
                    </div>
                    <div>
                        <span class="customer-badge {{ $customer->status ? 'badge-active' : 'badge-inactive' }}">
                            {{ $customer->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        {{-- Identity / Company --}}
                        @if($customer->customer_type === 'Individual')
                            <p><span class="detail-label">Full Name</span> <span class="value">{{ $customer->title }} {{ $customer->first_name }} {{ $customer->last_name }} {{ $customer->surname }}</span></p>
                            <p><span class="detail-label">ID Number</span> <span class="value">{{ $customer->id_number }}</span></p>
                            <p><span class="detail-label">DOB</span> <span class="value">{{ $customer->dob }}</span></p>
                            <p><span class="detail-label">Occupation</span> <span class="value">{{ $customer->occupation }}</span></p>
                        @else
                            <p><span class="detail-label">Company</span> <span class="value">{{ $customer->corporate_name }}</span></p>
                            <p><span class="detail-label">Company No</span> <span class="value">{{ $customer->business_no }}</span></p>
                            <p><span class="detail-label">Industry</span> <span class="value">{{ $customer->industry_class }} / {{ $customer->industry_segment }}</span></p>
                            <p><span class="detail-label">Contact Person</span> <span class="value">{{ $customer->contact_person }}</span></p>
                        @endif
                    </div>

                    <div class="col-lg-6">
                        {{-- Contact & address --}}
                        <p><span class="detail-label">Email</span> <span class="value">{{ $customer->email ?? '-' }}</span></p>
                        <p><span class="detail-label">Phone</span> <span class="value">{{ $customer->phone ?? '-' }}</span></p>
                        <p><span class="detail-label">Address</span> <span class="value">{{ $customer->address }}</span></p>
                        <p><span class="detail-label">City / County</span> <span class="value">{{ $customer->city }} / {{ $customer->county }}</span></p>
                        <p><span class="detail-label">KRA PIN</span> <span class="value">{{ $customer->kra_pin }}</span></p>
                    </div>
                </div>

                <hr>
                <div class="row">
                    <div class="col-lg-8">
                        <h6 class="mb-2">Notes</h6>
                        <div class="preserve-formatting">{{ $customer->notes }}</div>
                    </div>
                    <div class="col-lg-4">
                        <h6 class="mb-2">Documents</h6>
                        @if($customer->documents)
                            @php
                                $docs = is_array($customer->documents) ? $customer->documents : [$customer->documents];
                            @endphp
                            @foreach($docs as $doc)
                                @php
                                    $fileName = basename($doc);
                                @endphp
                                <a class="doc-link" href="{{ asset('storage/documents/' . $fileName) }}" download><i class="fas fa-file"></i> {{ $fileName }}</a>
                            @endforeach
                        @else
                            <p class="muted">No documents uploaded</p>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    @php
                        $actionButtons = [
                            ['url' => route('customers.index'), 'label' => 'Go Back', 'icon' => 'fas fa-arrow-left', 'variant' => 'primary', 'attrs' => ['title' => 'Back to list', 'aria-label' => 'Back to list']],
                            ['url' => route('customers.edit', $customer->id), 'label' => 'Edit', 'icon' => 'fas fa-edit', 'variant' => 'warning', 'attrs' => ['title' => 'Edit customer', 'aria-label' => 'Edit customer']],
                            ['url' => route('customers.statement', $customer->id), 'label' => 'Statement', 'icon' => 'fas fa-file-invoice', 'variant' => 'secondary', 'attrs' => ['title' => 'View statement']],
                        ];
                    @endphp
                    @include('shared.action-buttons', ['buttons' => $actionButtons])
                </div>
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
