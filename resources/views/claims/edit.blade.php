@extends('layouts.appPages')

@section('content')
<style>
    .form-label.required::after {
        content: " *";
        color: red;
    }

    .group-heading {
        margin-top: 20px;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
        color: #007bff;
        font-size: 1.25rem;
    }

    .event-group {
        margin-bottom: 20px;
    }
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Edit Claim</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('claims.update', $claim->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Policy Details Section -->
                <div class="group-heading">Policy Details</div>
                <div class="row">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="claim_number" class="form-label">Claim No</label>
                            <input type="text" name="claim_number" class="form-control" value="{{ old('claim_number', $claim->claim_number) }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="customer_code" class="form-label">Cust Code</label>
                            <input type="text" name="customer_code" class="form-control" value="{{ old('customer_code', $claim->customer_code) }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $claim->policy->customer_name) }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="policy_type" class="form-label">Policy Type</label>
                            <input type="text" name="policy_type" class="form-control" 
                                value="{{ old('policy_type', $claim->policy && $claim->policy->policy_type ? $claim->policy->policy_type->type_name : 'N/A') }}" readonly>
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="fileno" class="form-label">File No</label>
                            <input type="text" name="fileno" class="form-control" value="{{ old('fileno', $claim->fileno) }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="reg_no" class="form-label">Reg No</label>
                            <input type="text" name="reg_no" class="form-control" value="{{ old('reg_no', $claim->policy->reg_no) }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $claim->policy->description) }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="policy_id" class="form-label required">Policy</label>
                    <select name="policy_id" class="form-control" required>
                        @foreach($policies as $policy)
                        <option value="{{ $policy->id }}" {{ $claim->policy_id == $policy->id ? 'selected' : '' }}>
                            {{ $policy->policy_no }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Claim Details Section -->
                <div class="group-heading">Claims Details</div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="reported_date" class="form-label required">Reported Date</label>
                            <input type="date" name="reported_date" class="form-control" value="{{ old('reported_date', \Illuminate\Support\Carbon::parse($claim->reported_date)->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="type_of_loss" class="form-label required">Type of Loss</label>
                            <input type="text" name="type_of_loss" class="form-control" value="{{ old('type_of_loss', $claim->type_of_loss) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="loss_date" class="form-label required">Loss Date</label>
                            <input type="date" name="loss_date" class="form-control" value="{{ old('loss_date', \Illuminate\Support\Carbon::parse($claim->loss_date)->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="followup_date" class="form-label">Follow-up Date</label>
                            <input type="date" name="followup_date" class="form-control" value="{{ old('followup_date', $claim->followup_date ? \Illuminate\Support\Carbon::parse($claim->followup_date)->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="claimant_name" class="form-label required">Claimant Name</label>
                            <input type="text" name="claimant_name" class="form-control" value="{{ old('claimant_name', $claim->claimant_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="amount_claimed" class="form-label required">Amount Claimed</label>
                            <input type="number" step="0.01" name="amount_claimed" class="form-control" value="{{ old('amount_claimed', $claim->amount_claimed) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="amount_paid" class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" name="amount_paid" class="form-control" value="{{ old('amount_paid', $claim->amount_paid) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Open" {{ $claim->status == 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="Closed" {{ $claim->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="loss_details" class="form-label required">Loss Details</label>
                    <textarea name="loss_details" class="form-control" required>{{ old('loss_details', $claim->loss_details) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="upload_file" class="form-label">Upload File</label>
                    <input type="file" name="upload_file" class="form-control">
                </div>

                {{-- Existing attachments (preview) --}}
                @if(!empty($claim->attachments) && is_array($claim->attachments))
                    <div class="group-heading bg-primary text-white p-2 mb-2">Existing Attachments</div>
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        @foreach($claim->attachments as $idx => $att)
                            @php
                                $path = $att['path'] ?? $att['file'] ?? null;
                                $name = $att['original_name'] ?? ($att['name'] ?? basename($path ?? ''));
                                $url = $path ? asset('storage/' . $path) : null;
                                $ext = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
                            @endphp

                            <div class="card text-center" style="width:120px;">
                                @if($url && in_array($ext, ['jpg','jpeg','png','gif']))
                                    <a href="{{ $url }}" target="_blank" class="d-block" style="height:80px; overflow:hidden;">
                                        <img src="{{ $url }}" alt="{{ $name }}" style="width:100%; height:80px; object-fit:cover;">
                                    </a>
                                @elseif($url && $ext === 'pdf')
                                    <a href="{{ $url }}" target="_blank" class="d-flex align-items-center justify-content-center" style="height:80px;">
                                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                    </a>
                                @else
                                    <a href="{{ $url ?? '#' }}" target="_blank" class="d-flex align-items-center justify-content-center" style="height:80px;">
                                        <i class="fas fa-file fa-2x"></i>
                                    </a>
                                @endif

                                <div class="card-body p-2">
                                    <a href="{{ $url ?? '#' }}" download class="small text-truncate d-block">{{ \Illuminate\Support\Str::limit($name, 24) }}</a>
                                    {{-- Optional: show filename and an index (non-destructive preview) --}}
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_attachments[]" value="{{ $idx }}" id="remove_att_{{ $idx }}">
                                        <label class="form-check-label small" for="remove_att_{{ $idx }}">Remove</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Standardized action buttons --}}
                @php
                    $actionButtons = [
                        ['url' => route('claims.index'), 'label' => 'Go Back', 'icon' => 'fas fa-arrow-left', 'variant' => 'primary', 'attrs' => ['title' => 'Back to list', 'aria-label' => 'Back to list']],
                        ['url' => route('claims.show', $claim->id), 'label' => 'View', 'icon' => 'fas fa-eye', 'variant' => 'secondary', 'attrs' => ['title' => 'View claim', 'aria-label' => 'View claim']],
                    ];
                @endphp

                <div class="row mb-3">
                    <div class="col-12">
                        @include('shared.action-buttons', ['buttons' => $actionButtons])
                    </div>
                </div>

                <!-- Event Section -->
                <div class="group-heading">Events</div>
                <div id="events">
                    @foreach($claim->events as $index => $event)
                    <div class="event-group">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="events[{{ $index }}][event_date]" class="form-label required">Event Date</label>
                                    <input type="date" name="events[{{ $index }}][event_date]" class="form-control" value="{{ old("events[$index][event_date]", \Illuminate\Support\Carbon::parse($event->event_date)->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="events[{{ $index }}][event_type]" class="form-label required">Event Type</label>
                                    <input type="text" name="events[{{ $index }}][event_type]" class="form-control" value="{{ old("events[$index][event_type]", $event->event_type) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="events[{{ $index }}][description]" class="form-label">Description</label>
                                    <textarea name="events[{{ $index }}][description]" class="form-control">{{ old("events[$index][description]", $event->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-secondary" id="addEvent">Add Another Event</button>

                <button type="submit" class="btn btn-primary mt-3">Update Claim</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('addEvent').addEventListener('click', function() {
        let eventIndex = document.querySelectorAll('.event-group').length;
        let eventGroup = document.createElement('div');
        eventGroup.classList.add('event-group');
        eventGroup.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="events[${eventIndex}][event_date]" class="form-label required">Event Date</label>
                        <input type="date" name="events[${eventIndex}][event_date]" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="events[${eventIndex}][event_type]" class="form-label required">Event Type</label>
                        <input type="text" name="events[${eventIndex}][event_type]" class="form-control">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="events[${eventIndex}][description]" class="form-label">Description</label>
                        <textarea name="events[${eventIndex}][description]" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <hr>
        `;
        document.getElementById('events').appendChild(eventGroup);
    });
</script>
@endsection
