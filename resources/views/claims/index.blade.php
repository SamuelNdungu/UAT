@extends('layouts.app')

@section('content')
<style>
    .card-box {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .card-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .card-box.selected {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transform: translateY(-10px);
    }
</style>

<div class="container"> 
    <div class="row mb-3">
        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-cyan" style="border-radius: 5px;" onclick="window.location='{{ route('claims.index', ['filter' => 'all']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['totalClaims'] }}</h3>
                    <p>Total Claims</p>
                </div>
                <div class="icon">
                    <i class="fa fa-chart-line" aria-hidden="true"></i>
                </div>
                <a href="{{ route('claims.index', ['filter' => 'all']) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-green" style="border-radius: 5px;" onclick="window.location='{{ route('claims.index', ['filter' => 'Open']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['openClaims'] }}</h3>
                    <p>Open Claims</p>
                </div>
                <div class="icon">
                    <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
                </div>
                <a href="{{ route('claims.index', ['filter' => 'Open']) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-orange" style="border-radius: 5px;" onclick="window.location='{{ route('claims.index', ['filter' => 'Closed']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['closedClaims'] }}</h3>
                    <p>Closed Claims</p>
                </div>
                <div class="icon">
                    <i class="fa fa-file-alt" aria-hidden="true"></i>
                </div>
                <a href="{{ route('claims.index', ['filter' => 'Closed']) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-red" style="border-radius: 5px;" onclick="window.location='{{ route('claims.index', ['filter' => 'Pending']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['pendingClaims'] }}</h3>
                    <p>Pending Claims</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                </div>
                <a href="{{ route('claims.index', ['filter' => 'Pending']) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card card-danger">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">
                    <h4 class="card-title">Claims List</h4>
                </div>
                <div class="col-md-6 text-md-end text-start">
                    <a href="{{ route('claims.create') }}" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                        <i class="fas fa-plus" style="font-size: 0.75rem;"></i> Add
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
                <table id="myTable" class="table table-striped rounded-top dt-responsive nowrap" style="width:100%; font-size: 12px;">
                    <thead style="white-space: nowrap;">
                        <tr>
                            <th>#</th>
                            <th>Claim Number</th>
                            <th>File No.</th>
                            <th>Policy Number</th>
                            <th>Policy Type</th>
                            <th>Reg No</th>
                            <th>Sum Insured</th>
                            <th>Customer Name</th>
                            <th>Customer Code</th>
                            <th>Claimant Name</th>
                            <th>Reported Date</th>
                            <th>Type of Loss</th>
                            <th>Loss Date</th>
                            <th>Follow-up Date</th>
                            <th>Amount Claimed</th>
                            <th>Amount Paid</th>
                            <th>Status</th> 
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody style="white-space: nowrap;">
                        @if($claims->count() === 0)
                            <tr>
                                <td colspan="19" class="text-center">No claims found.</td>
                            </tr>
                        @else
                            @foreach($claims as $claim)
                                @php
                                    // safe helpers for both array/stdClass/Eloquent
                                    $id = data_get($claim,'id');
                                    $policyStatus = data_get($claim,'policy_status', data_get($claim,'policy.status',''));
                                    $reportedRaw = data_get($claim,'reported_date');
                                    $lossRaw = data_get($claim,'loss_date');
                                    $followRaw = data_get($claim,'followup_date');
                                @endphp
                                <tr class="claim-row" data-status="{{ data_get($claim,'status','') }}" data-policy-status="{{ $policyStatus }}">
                                    <td>{{ $id }}</td>
                                    <td>{{ data_get($claim,'claim_number','N/A') }}</td>
                                    <td>{{ data_get($claim,'fileno','') }}</td>
                                    <td>{{ data_get($claim,'policy_number','N/A') }}</td>
                                    <td>{{ data_get($claim,'policy_type_name','N/A') }}</td>
                                    <td>{{ data_get($claim,'reg_no','N/A') }}</td>
                                    <td>{{ is_numeric(data_get($claim,'sum_insured')) ? number_format(data_get($claim,'sum_insured'), 2) : data_get($claim,'sum_insured','N/A') }}</td>
                                    <td>{{ data_get($claim,'customer_name','N/A') }}</td>
                                    <td>{{ data_get($claim,'customer_code','') }}</td>
                                    <td>{{ data_get($claim,'claimant_name','') }}</td>
                                    <td class="reported-date">{{ $reportedRaw ? \Carbon\Carbon::parse($reportedRaw)->toIso8601String() : '' }}</td>
                                    <td>{{ data_get($claim,'type_of_loss','') }}</td>
                                    <td class="loss-date">{{ $lossRaw ? \Carbon\Carbon::parse($lossRaw)->toIso8601String() : '' }}</td>
                                    <td class="followup-date">{{ $followRaw ? \Carbon\Carbon::parse($followRaw)->toIso8601String() : '' }}</td>
                                    <td>{{ is_numeric(data_get($claim,'amount_claimed')) ? number_format(data_get($claim,'amount_claimed'), 2) : data_get($claim,'amount_claimed','N/A') }}</td>
                                    <td>{{ is_numeric(data_get($claim,'amount_paid')) ? number_format(data_get($claim,'amount_paid'), 2) : 'N/A' }}</td>
                                    <td>{{ data_get($claim,'status','') }}</td>
                                     
                                    <td style="white-space: nowrap; position: sticky; right: 0; background-color: white; z-index: 100; padding: 2px; border-left: 1px solid #ddd;">
                                        <a href="{{ route('claims.show', $id) }}" class="btn btn-info btn-xs action-view" aria-label="View" title="View" data-bs-toggle="tooltip" data-bs-placement="top" style="font-size: 0.5rem; padding: 2px 5px;">
                                            <i class="fas fa-eye" aria-hidden="true" style="font-size: 0.5rem;"></i>
                                        </a>
                                        <a href="{{ route('claims.edit', $id) }}" class="btn btn-warning btn-xs action-edit" aria-label="Edit" title="Edit" data-bs-toggle="tooltip" data-bs-placement="top" style="font-size: 0.5rem; padding: 2px 5px;">
                                            <i class="fas fa-pencil-alt" aria-hidden="true" style="font-size: 0.5rem;"></i>
                                        </a>
                                        <form action="{{ route('claims.destroy', $id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs action-delete" aria-label="Delete" title="Delete" data-bs-toggle="tooltip" data-bs-placement="top" style="font-size: 0.5rem; padding: 2px 5px;">
                                                <i class="fas fa-trash" aria-hidden="true" style="font-size: 0.5rem;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                     </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Claim Number</th>
                            <th>File No.</th>
                            <th>Policy Number</th>
                            <th>Policy Type</th>
                            <th>Reg No</th>
                            <th>Sum Insured</th>
                            <th>Customer Name</th>
                            <th>Customer Code</th>
                            <th>Claimant Name</th>
                            <th>Reported Date</th>
                            <th>Type of Loss</th>
                            <th>Loss Date</th>
                            <th>Follow-up Date</th>
                            <th>Amount Claimed</th>
                            <th>Amount Paid</th>
                            <th>Status</th> 
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div> 

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this record?');
}
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#myTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "order": [[0, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [17] }, // Disable ordering for the Actions column
            { "searchable": false, "targets": [17] } // Disable search for the Actions column
        ],
        "lengthMenu": [5, 10, 25, 50],
        "pageLength": 10,
        "language": {
            "search": "Search claims:"
        }
    });

    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el) })
    }

    // Format ISO dates in the table to a human-friendly form (dd-mm-yyyy)
    function fmtIsoToShort(iso) {
        if (!iso) return 'N/A';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return iso;
        return ('0' + d.getDate()).slice(-2) + '-' + ('0' + (d.getMonth()+1)).slice(-2) + '-' + d.getFullYear();
    }

    $('#myTable tbody tr').each(function() {
        var $tr = $(this);
        var reported = $tr.find('.reported-date').text().trim();
        var loss = $tr.find('.loss-date').text().trim();
        var follow = $tr.find('.followup-date').text().trim();
        if (reported) $tr.find('.reported-date').text(fmtIsoToShort(reported));
        if (loss) $tr.find('.loss-date').text(fmtIsoToShort(loss));
        if (follow) $tr.find('.followup-date').text(fmtIsoToShort(follow));

        // Disable actions when policy is cancelled
        var policyStatus = ($tr.data('policy-status') || '').toString().toLowerCase();
        if (['canceled','cancelled','cancel'].indexOf(policyStatus) !== -1) {
            $tr.find('.action-edit, .action-delete').addClass('disabled').attr('aria-disabled', 'true').off('click').attr('title', 'Action disabled — policy canceled');
        }
    });

    // Filter claims based on card selection
    $('.card-box').on('click', function() {
        var filter = $(this).data('filter');
        $('.card-box').removeClass('selected');
        $(this).addClass('selected');
        filterClaims(filter); // Call the filtering function
    });

    function filterClaims(status) {
        // If 'all' is selected, clear the search filter
        if (status === 'all') {
            table.search('').draw();
        } else {
            // Apply filtering to the 'Status' column (assuming it's the 16th column, index 15)
            table.column(15).search(status).draw();
        }
    }
});
</script>

@endsection
