@extends('layouts.appPages')

@section('content')
<style>
	/* Visual polish */
	.card-accent { border-top: 4px solid #007bff; border-radius: .375rem; }
	.group-heading { font-size: 1.125rem; color: #007bff; margin-bottom: .75rem; font-weight:600; }
	.result-item { padding: 8px; border-bottom: 1px solid #eee; cursor: pointer; }
	.result-item:hover { background-color: #f8f9fb; }
	.preview-thumb { width:100%; height:80px; object-fit:cover; border-radius:4px; }
	.attachment-card { width:120px; margin-right:.5rem; margin-bottom:.5rem; }
	.form-label.required::after { content: " *"; color: #d9534f; margin-left:2px; }
	.small-muted { font-size:0.85rem; color:#6c757d; }
	@media (max-width:767.98px){ .attachment-card{ width:100%; } }
</style>

<div class="container my-4">
	<div class="row g-3">
		<!-- Left column: Search / Policy details -->
		<div class="col-lg-4">
			<div class="card card-accent">
				<div class="card-header bg-white">
					<h5 class="mb-0"><i class="fas fa-search me-2 text-primary"></i>Find Policy</h5>
				</div>
				<div class="card-body">
					<p class="small-muted">Search by File No, Policy Type or Reg No (type 3+ characters)</p>
								<input type="text" id="search" class="form-control mb-2" placeholder="Search policies (3+ chars)" value="{{ old('search') }}">
					<div id="results" class="list-group mb-3" style="max-height:260px; overflow:auto;"></div>

					<div id="policy-details" class="mt-2">
						@if(old('fileno'))
							{{-- If form was previously submitted with a selected policy, show a compact summary --}}
							<div class="card">
								<div class="card-body small">
									<p class="mb-1"><strong>{{ old('customer_name') }}</strong></p>
									<p class="mb-0 small-muted">File No: {{ old('fileno') }} | Reg No: {{ old('reg_no') }}</p>
								</div>
							</div>
						@endif
					</div>

					<hr>

					<div class="small-muted">
						<p class="mb-1"><strong>Selected Policy</strong></p>
						<p class="mb-0">File No: <span id="pd-fileno">{{ old('fileno', 'N/A') }}</span></p>
						<p class="mb-0">Customer: <span id="pd-customer">{{ old('customer_name', 'N/A') }}</span></p>
						<p class="mb-0">Insurer: <span id="pd-insurer">{{ old('insurer', 'N/A') }}</span></p>
						<p class="mb-0">Due: <span id="pd-due">{{ old('due_amount', '0.00') }}</span></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Right column: Claim form -->
		<div class="col-lg-8">
			<div class="card card-accent">
				<div class="card-header bg-white">
					<h4 class="mb-0"><i class="fas fa-file-medical-alt me-2 text-primary"></i>New Claim</h4>
				</div>
				<div class="card-body">
					<!-- UX banner: explain disabled inputs for canceled policies -->
					<div class="alert alert-info small d-none" id="claim-ux-banner">
						<i class="fas fa-info-circle"></i>
						Some policy fields/inputs may be disabled because the selected policy is <strong>canceled</strong>. Claims cannot be registered against canceled policies. If you believe this is an error, check the policy status or contact an administrator.
					</div>
					@if ($errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data" id="claimForm">
						@csrf

						{{-- Hidden fields for selected policy (populated by search) --}}
						<input type="hidden" id="fileno" name="fileno" value="{{ old('fileno') }}">
						<input type="hidden" id="policy_id" name="policy_id" value="{{ old('policy_id') }}">
						<input type="hidden" id="customer_code" name="customer_code" value="{{ old('customer_code') }}">
						<input type="hidden" id="customer_name" name="customer_name" value="{{ old('customer_name') }}">
						<input type="hidden" id="policy_type" name="policy_type" value="{{ old('policy_type') }}">
						<input type="hidden" id="reg_no" name="reg_no" value="{{ old('reg_no') }}">
						<input type="hidden" id="make" name="make" value="{{ old('make') }}">
						<input type="hidden" id="model" name="model" value="{{ old('model') }}">
						<input type="hidden" id="description" name="description" value="{{ old('description') }}">
						<input type="hidden" id="start_date" name="start_date" value="{{ old('start_date') }}">
						<input type="hidden" id="end_date" name="end_date" value="{{ old('end_date') }}">
						<input type="hidden" id="insurer" name="insurer" value="{{ old('insurer') }}">
						<input type="hidden" id="sum_insured" name="sum_insured" value="{{ old('sum_insured') }}">
						<input type="hidden" id="gross_premium" name="gross_premium" value="{{ old('gross_premium') }}">
						<input type="hidden" id="paid_amount" name="paid_amount" value="{{ old('paid_amount') }}">
						<input type="hidden" id="due_amount" name="due_amount" value="{{ old('due_amount') }}">

						<div class="row g-3">
							<div class="col-md-4">
								<label class="form-label">Claim No</label>
								<input type="text" name="claim_no" id="claim_no" class="form-control" value="{{ old('claim_no', $newClaimNumber) }}" readonly>
								@error('claim_no') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-4">
								<label class="form-label required">Reported Date</label>
								<input type="date" name="reported_at" id="reported_at" class="form-control" value="{{ old('reported_at') }}" required>
								@error('reported_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-4">
								<label class="form-label required">Date of Loss</label>
								<input type="date" name="date_of_loss" id="date_of_loss" class="form-control" value="{{ old('date_of_loss') }}" required>
								@error('date_of_loss') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-6">
								<label class="form-label required">Type of Loss</label>
								<input type="text" name="type_of_loss" id="type_of_loss" class="form-control" value="{{ old('type_of_loss') }}" required>
								@error('type_of_loss') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-6">
								<label class="form-label required">Claimant Name</label>
								<input type="text" name="claimant_name" id="claimant_name" class="form-control" value="{{ old('claimant_name') }}" required>
								@error('claimant_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-6">
								<label class="form-label required">Amount Claimed</label>
								<input type="number" step="0.01" name="amount_claimed" id="amount_claimed" class="form-control" value="{{ old('amount_claimed') }}" required>
								@error('amount_claimed') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-3">
								<label class="form-label">Amount Paid</label>
								<input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" value="{{ old('amount_paid') }}">
								@error('amount_paid') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-3">
								<label class="form-label required">Status</label>
								<select name="status" id="status" class="form-control" required>
									<option value="open" {{ old('status') == 'open' ? 'selected' : '' }}>Open</option>
									<option value="investigating" {{ old('status') == 'investigating' ? 'selected' : '' }}>Investigating</option>
									<option value="settled" {{ old('status') == 'settled' ? 'selected' : '' }}>Settled</option>
									<option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
									<option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
								</select>
								@error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-12">
								<label class="form-label required">Loss Details</label>
								<textarea name="loss_details" id="loss_details" class="form-control" rows="4" required>{{ old('loss_details') }}</textarea>
								@error('loss_details') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-4">
								<label class="form-label">Follow-up Date</label>
								<input type="date" name="followup_date" id="followup_date" class="form-control" value="{{ old('followup_date') }}">
								@error('followup_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
							</div>

							<div class="col-md-8">
								<label class="form-label">Notes</label>
								<textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
							</div>

							{{-- Attachments upload --}}
{{-- Enhanced Documents Management --}}
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

							{{-- Submit --}}
							<div class="col-12 text-end mt-3">
								<button type="submit" class="btn btn-primary btn-lg">Submit Claim</button>
							</div>
						</div>
					</form>
				</div>
			</div>

			{{-- Recent claims quick list (optional) --}}
			<div class="card mt-3">
				<div class="card-body small">
					<p class="mb-1"><strong>Tip:</strong> After creating a claim you can view it from the claims list.</p>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	// Search policies
	const resultsDiv = document.getElementById('results');
	document.getElementById('search').addEventListener('input', function () {
		const q = this.value.trim();
		resultsDiv.innerHTML = '';
		if (q.length < 3) return;
		fetch(`{{ url('/api/search-policies') }}?search=${encodeURIComponent(q)}`)
			.then(r => r.json())
			.then(items => {
				if (!Array.isArray(items) || items.length === 0) {
					resultsDiv.innerHTML = '<div class="text-muted p-2">No results</div>';
					return;
				}
				items.forEach(policy => {
					const el = document.createElement('div');
					el.className = 'result-item';
					el.innerText = `${policy.fileno} — ${policy.customer_name} — ${policy.policy_type} — ${policy.reg_no || ''}`;
					el.addEventListener('click', () => fetchPolicyDetails(policy.fileno));
					resultsDiv.appendChild(el);
				});
			})
			.catch(err => {
				console.error(err);
				resultsDiv.innerHTML = '<div class="text-danger p-2">Error fetching results</div>';
			});
	});

	function fetchPolicyDetails(fileno) {
		fetch(`{{ url('/api/get-policy-details') }}?fileno=${encodeURIComponent(fileno)}`)
			.then(r => r.json())
			.then(data => {
				// populate hidden inputs
				['fileno','policy_id','customer_code','customer_name','policy_type','reg_no','make','model','description','start_date','end_date','insurer','sum_insured','gross_premium','paid_amount','due_amount'].forEach(k => {
					const el = document.getElementById(k);
					if (el && data[k] !== undefined) el.value = data[k];
				});

					// UI summary
				document.getElementById('pd-fileno').innerText = data.fileno || 'N/A';
				document.getElementById('pd-customer').innerText = data.customer_name || 'N/A';
				document.getElementById('pd-insurer').innerText = data.insurer || 'N/A';
				document.getElementById('pd-due').innerText = data.due_amount ? parseFloat(data.due_amount).toLocaleString() : '0.00';

					// If policy is canceled, show banner and disable submit
					const submitBtn = document.querySelector('#claimForm button[type="submit"]');
					let existingAlert = document.getElementById('policy-cancel-alert');
					const isCancelled = data.status && ['canceled','cancelled','cancel'].indexOf((data.status||'').toLowerCase()) !== -1;
					if (isCancelled) {
						document.getElementById('claim-ux-banner').classList.remove('d-none');
						if (!existingAlert) {
							const alertEl = document.createElement('div');
							alertEl.id = 'policy-cancel-alert';
							alertEl.className = 'alert alert-danger mt-2';
							alertEl.innerHTML = '<strong>Policy canceled:</strong> Claims cannot be registered against canceled policies.';
							// insert alert above the form
							document.getElementById('claimForm').insertAdjacentElement('beforebegin', alertEl);
						}
						if (submitBtn) submitBtn.disabled = true;
					} else {
						document.getElementById('claim-ux-banner').classList.add('d-none');
						if (existingAlert) existingAlert.remove();
						if (submitBtn) submitBtn.disabled = false;
					}

				// render policy-details card
				// UI summary (format dates)
				document.getElementById('policy-details').innerHTML = `
					<div class="card mb-2">
						<div class="card-body small">
							<div class="row">
								<div class="col-6"><strong>File No:</strong> ${data.fileno || ''}</div>
								<div class="col-6"><strong>Reg No:</strong> ${data.reg_no || ''}</div>
								<div class="col-6"><strong>Policy Type:</strong> ${data.policy_type || ''}</div>
								<div class="col-6"><strong>Insurer:</strong> ${data.insurer || ''}</div>
								<div class="col-6"><strong>Period:</strong> ${data.start_date ? new Date(data.start_date).toLocaleDateString() : ''} → ${data.end_date ? new Date(data.end_date).toLocaleDateString() : ''}</div>
								<div class="col-6"><strong>Due:</strong> ${data.due_amount ? parseFloat(data.due_amount).toLocaleString() : '0.00'}</div>
								<div class="col-12 small-muted mt-2"><em>Policy id (internal): ${data.policy_id ?? 'N/A'}. Display uses fileno (e.g. ${data.fileno ?? 'FN-00049'}).</em></div>
							</div>
						</div>
					</div>
				`;
				resultsDiv.innerHTML = '';
			})
			.catch(err => {
				console.error(err);
				document.getElementById('policy-details').innerHTML = '<div class="text-danger small">Unable to fetch policy details.</div>';
			});
	}

	// Attachments preview
	const attachmentsInput = document.getElementById('attachments');
	const previewsDiv = document.getElementById('attachment-previews');

	attachmentsInput.addEventListener('change', function () {
		previewsDiv.innerHTML = '';
		Array.from(this.files).forEach(file => {
			const url = URL.createObjectURL(file);
			const ext = file.name.split('.').pop().toLowerCase();
			const card = document.createElement('div');
			card.className = 'attachment-card';

			let inner = '';
			if (['jpg','jpeg','png','gif'].includes(ext)) {
				inner = `<img src="${url}" class="preview-thumb" alt="${file.name}">`;
			} else if (ext === 'pdf') {
				inner = `<div class="d-flex align-items-center justify-content-center" style="height:80px;"><i class="fas fa-file-pdf fa-2x text-danger"></i></div>`;
			} else {
				inner = `<div class="d-flex align-items-center justify-content-center" style="height:80px;"><i class="fas fa-file fa-2x"></i></div>`;
			}
			card.innerHTML = `
				<div class="card p-2">
					${inner}
					<div class="card-body p-2 text-truncate small">
						${file.name}
						<div class="small-muted">${(file.size/1024).toFixed(0)} KB</div>
					</div>
				</div>
			`;
			previewsDiv.appendChild(card);
		});
	});
});


// Document Management Functions
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
