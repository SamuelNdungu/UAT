<style>
#leadsTable thead th,
#leadsTable tbody td {
    font-size: 0.85rem;
    white-space: nowrap;
    padding: 8px;
}
</style>

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-lg-4 col-sm-6">
            <div class="card-box bg-cyan card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('leads.index', ['filter' => 'total']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['totalLeads'] ?? 0 }}</h3>
                    <p>Total Leads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="card-box bg-purple card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('leads.index', ['filter' => 'active']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['activeLeads'] ?? 0 }}</h3>
                    <p>Active Leads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="card-box bg-info card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('leads.index', ['filter' => 'converted']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['convertedLeads'] ?? 0 }}</h3>
                    <p>Converted Leads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-4 col-sm-6">
            <div class="card-box bg-green card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('leads.index', ['filter' => 'high_probability']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['highProbabilityLeads'] ?? 0 }}</h3>
                    <p>High Probability Leads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="card-box bg-orange card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('leads.index', ['filter' => 'follow_up']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['followUpLeads'] ?? 0 }}</h3>
                    <p>Needs Follow-up</p>
                </div>
                <div class="icon">
                    <i class="fas fa-phone" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="card-box bg-red card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('leads.index', ['filter' => 'lost']) }}'">
                <div class="inner">
                    <h3>{{ $metrics['lostLeads'] ?? 0 }}</h3>
                    <p>Lost Leads</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Leads Management</h3>
            <div class="card-tools">
                <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Lead
                </a>
                <a href="{{ route('leads.index', ['export' => 'pdf']) }}" class="btn btn-danger btn-sm ml-2">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="{{ route('leads.index', ['export' => 'excel']) }}" class="btn btn-success btn-sm ml-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="leadsTable" class="table table-bordered table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Contact Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Policy Type</th>
                            <th>Lead Source</th>
                            <th>Deal Size</th>
                            <th>Probability</th>
                            <th>Weighted Revenue</th>
                            <th>Deal Stage</th>
                            <th>Deal Status</th>
                            <th>Date Initiated</th>
                            <th>Closing Date</th>
                            <th>Follow-up Date</th>
                            <th>Next Action</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr>
                                <td>{{ $lead->id }}</td>
                                <td>{{ $lead->lead_type }}</td>
                                <td>{{ $lead->lead_type === 'Corporate' ? $lead->corporate_name : $lead->first_name . ' ' . $lead->last_name }}</td>
                                <td>{{ $lead->contact_name }}</td>
                                <td>{{ $lead->email }}</td>
                                <td>{{ $lead->mobile }}</td>
                                <td>{{ $lead->policy_type }}</td>
                                <td>{{ $lead->lead_source }}</td>
                                <td>{{ number_format($lead->deal_size, 2) }}</td>
                                <td>{{ $lead->probability }}%</td>
                                <td>{{ number_format($lead->weighted_revenue_forecast, 2) }}</td>
                                <td>{{ $lead->deal_stage }}</td>
                                <td>{{ $lead->deal_status }}</td>
                                <td>{{ $lead->date_initiated ? \Carbon\Carbon::parse($lead->date_initiated)->format('d-m-Y') : '' }}</td>
                                <td>{{ $lead->closing_date ? \Carbon\Carbon::parse($lead->closing_date)->format('d-m-Y') : '' }}</td>
                                <td>{{ $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('d-m-Y') : '' }}</td> 
                                <td>{{ $lead->next_action }}</td>
                                <td>{{ $lead->notes }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-warning btn-xs">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this lead?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#leadsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{
            targets: -1,
            orderable: false
        }],
        dom: '<"d-flex justify-content-between"<"d-flex"l<"ml-2"f>>t<"d-flex justify-content-between"ip>>',
        language: {
            lengthMenu: "_MENU_ records per page",
            search: "Search:",
            paginate: {
                first: '«',
                previous: '‹',
                next: '›',
                last: '»'
            }
        }
    });
});
</script>

@endsection
