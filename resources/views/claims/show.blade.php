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

    .form-label {
        font-weight: bold;
        color: #333;
    }

    .form-control-plaintext {
        background-color: #f8f9fa;
        padding: 8px 10px;
        border-radius: 4px;
        font-size: 1rem;
        color: #495057;
        border: 1px solid #ced4da;
    }
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Claim Details</h4>
        </div>
        <div class="card-body ">
            <!-- Policy Details Section -->
            <div class="mb-4">
                <h5 class="text-primary mb-3">Policy Details</h5>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="me-3 text-primary fs-3">
                                        <i class="fas fa-id-badge"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Claim No</small>
                                        <div class="fw-bold">{{ $claim->claim_number }}</div>
                                        <small class="text-muted">Cust: {{ $claim->customer_code }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Customer</small>
                                <div class="fw-bold">{{ $claim->policy->customer_name }}</div>
                                <div class="text-muted small mt-1">Policy Type: {{ $claim->policy->policyType->type_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Policy / Vehicle</small>
                                <div class="fw-bold">{{ $claim->policy->policy_no ?? 'N/A' }} &middot; {{ $claim->policy->reg_no ?? 'N/A' }}</div>
                                <div class="text-muted small mt-1">Sum Insured: {{ number_format($claim->policy->sum_insured ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Claim Details Section -->
            <div class="mb-4">
                <h5 class="text-primary mb-3">Claim Details</h5>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Reported</small>
                                <div class="fw-bold">{{ \Illuminate\Support\Carbon::parse($claim->reported_date)->format('d M Y') }}</div>
                                <small class="text-muted">Loss</small>
                                <div>{{ $claim->type_of_loss }} &middot; {{ \Illuminate\Support\Carbon::parse($claim->loss_date)->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Claimant</small>
                                <div class="fw-bold">{{ $claim->claimant_name }}</div>
                                <small class="text-muted">Follow-up</small>
                                <div>{{ $claim->followup_date ? \Illuminate\Support\Carbon::parse($claim->followup_date)->format('d M Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Financials</small>
                                <div class="fw-bold">Claimed: {{ number_format($claim->amount_claimed, 2) }}</div>
                                <div class="text-muted">Paid: {{ $claim->amount_paid ? number_format($claim->amount_paid, 2) : 'N/A' }}</div>
                                <div class="mt-2"><span class="badge bg-{{ $claim->status == 'Closed' ? 'success' : ($claim->status == 'Open' ? 'warning' : 'secondary') }}">{{ $claim->status }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label ">Loss Details</label>
                <p class="form-control-plaintext">{{ $claim->loss_details }}</p>
            </div>
            
            <!-- Uploaded Files Section (legacy single file) -->
            <div class="group-heading">Uploaded Files</div>
            <div class="row">
                @if($claim->documents && $claim->documents->count() > 0)
                    @foreach($claim->documents as $doc)
                        @php
                            $docUrl = route('claims.attachment', ['claim' => $claim->id, 'idx' => basename($doc->path)]);
                        @endphp
                        <div class="me-3 mb-2">
                            <a href="{{ $docUrl }}" target="_blank">{{ $doc->original_name ?? basename($doc->path) }}</a>
                        </div>
                    @endforeach
                @elseif($claim->upload_file)
                    @php $legacyUrl = route('claims.attachment', ['claim' => $claim->id, 'idx' => 'upload_file']); @endphp
                    <a href="{{ $legacyUrl }}" target="_blank">View Document</a>
                @else
                    N/A
                @endif
            </div>

 
            <!-- Event Section -->
            <div class="group-heading">Events</div>
            <div id="events">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claim->events as $event)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('Y-m-d') }}</td>
                            <td>{{ $event->event_type }}</td>
                            <td>{{ $event->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Attachments / Documents preview (if any) --}}
            @if($claim->documents && $claim->documents->count() > 0)
                <div class="group-heading bg-primary text-white p-2 mb-2">Documents</div>
                <div class="mb-4 d-flex flex-wrap gap-2">
                    @foreach($claim->documents as $doc)
                        @php
                            $path = $doc->path;
                            $name = $doc->original_name ?? basename($path ?? '');
                            $viewUrl = $path ? route('claims.attachment', ['claim' => $claim->id, 'idx' => basename($path)]) : null;
                            $ext = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
                        @endphp

                        <div class="card text-center" style="width:120px;">
                            @if($viewUrl && in_array($ext, ['jpg','jpeg','png','gif']))
                                @php $thumbUrl = $viewUrl . '?thumb=1'; @endphp
                                <a href="{{ $viewUrl }}" target="_blank" class="d-block" style="height:80px; overflow:hidden;">
                                    <img src="{{ $thumbUrl }}" alt="{{ $name }}" style="width:100%; height:80px; object-fit:cover;">
                                </a>
                            @elseif($viewUrl && $ext === 'pdf')
                                <a href="{{ $viewUrl }}" target="_blank" class="d-flex align-items-center justify-content-center" style="height:80px;">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </a>
                            @else
                                <a href="{{ $viewUrl ?? '#' }}" target="_blank" class="d-flex align-items-center justify-content-center" style="height:80px;">
                                    <i class="fas fa-file fa-2x"></i>
                                </a>
                            @endif
                            <div class="card-body p-2">
                                <a href="{{ $viewUrl ?? '#' }}" download class="small text-truncate d-block">{{ \Illuminate\Support\Str::limit($name, 24) }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(!empty($claim->attachments) && is_array($claim->attachments))
                {{-- Fallback to legacy attachments array if documents not present --}}
                <div class="group-heading bg-primary text-white p-2 mb-2">Attachments</div>
                <div class="mb-4 d-flex flex-wrap gap-2">
                    @foreach($claim->attachments as $idx => $att)
                        @php
                            $path = $att['path'] ?? $att['file'] ?? null;
                            $name = $att['original_name'] ?? ($att['name'] ?? basename($path ?? ''));
                            $viewUrl = $path ? route('claims.attachment', ['claim' => $claim->id, 'idx' => $idx]) : null;
                            $ext = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
                        @endphp

                        <div class="card text-center" style="width:120px;">
                            @if($viewUrl && in_array($ext, ['jpg','jpeg','png','gif']))
                                @php $thumbUrl = $viewUrl . '?thumb=1'; @endphp
                                <a href="{{ $viewUrl }}" target="_blank" class="d-block" style="height:80px; overflow:hidden;">
                                    <img src="{{ $thumbUrl }}" alt="{{ $name }}" style="width:100%; height:80px; object-fit:cover;">
                                </a>
                            @elseif($viewUrl && $ext === 'pdf')
                                <a href="{{ $viewUrl }}" target="_blank" class="d-flex align-items-center justify-content-center" style="height:80px;">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </a>
                            @else
                                <a href="{{ $viewUrl ?? '#' }}" target="_blank" class="d-flex align-items-center justify-content-center" style="height:80px;">
                                    <i class="fas fa-file fa-2x"></i>
                                </a>
                            @endif
                            <div class="card-body p-2">
                                <a href="{{ $viewUrl ?? '#' }}" download class="small text-truncate d-block">{{ \Illuminate\Support\Str::limit($name, 24) }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Standardized action buttons --}}
            @php
                $actionButtons = [
                    ['url' => route('claims.index'), 'label' => 'Go Back', 'icon' => 'fas fa-arrow-left', 'variant' => 'primary', 'attrs' => ['title' => 'Back to list', 'aria-label' => 'Back to list']],
                    ['url' => route('claims.edit', $claim->id), 'label' => 'Edit', 'icon' => 'fas fa-edit', 'variant' => 'warning', 'attrs' => ['title' => 'Edit claim', 'aria-label' => 'Edit claim']],
                ];

                // Add Print button only if the named route exists
                if (\Illuminate\Support\Facades\Route::has('claims.print')) {
                    $actionButtons[] = ['url' => route('claims.print', $claim->id), 'label' => 'Print', 'icon' => 'fas fa-print', 'variant' => 'success', 'target' => '_blank', 'attrs' => ['title' => 'Print claim', 'aria-label' => 'Print claim']];
                }
            @endphp

            <div class="row mb-3">
                <div class="col-12">
                    @include('shared.action-buttons', ['buttons' => $actionButtons])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
