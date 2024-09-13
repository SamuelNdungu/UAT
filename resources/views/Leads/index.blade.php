@extends('layouts.app')

@section('content')

<div class="container">
    <div class="card card-danger">
        <div class="card-header">
            <h4 class="card-title">Leads List</h4>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('leads.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Lead
                </a>
            </div>
        </div>

        <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 100%;">
            <table id="leadsTable" class="table table-striped rounded-top" style="width: 100%; font-size: 12px;">
                <thead style="white-space: nowrap;">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Policy Type</th>
                        <th>Estimated Premium</th>
                        <th>Follow-up Date</th>
                        <th>Lead Source</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody style="white-space: nowrap;">
                    @foreach($leads as $lead)
                        <tr>
                            <td>{{ $lead->id }}</td>
                            <td>{{ $lead->lead_type }}</td>
                            <td>
                                @if($lead->lead_type === 'Corporate')
                                    {{ $lead->corporate_name }}
                                @elseif($lead->lead_type === 'Individual')
                                    {{ $lead->first_name . ' ' . $lead->last_name }}
                                @endif
                            </td>
                            <td>{{ $lead->email }}</td>
                            <td>{{ $lead->mobile }}</td>
                            <td>{{ $lead->policy_type }}</td>
                            <td>{{ $lead->estimated_premium }}</td>
                            <td>{{ $lead->follow_up_date->format('d/m/Y') }}</td>
                            <td>{{ $lead->lead_source }}</td>
                            <td>{{ $lead->notes }}</td>
                            <td style="white-space: nowrap; position: sticky; right: 0; background-color: white; z-index: 100; padding: 10px; border-left: 1px solid #ddd;">
                                <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-info btn-sm" aria-label="View" title="View">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </a>
                                <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-warning btn-sm" aria-label="Edit" title="Edit">
                                    <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                                </a>
                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Delete" title="Delete">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Policy Type</th>
                        <th>Estimated Premium</th>
                        <th>Follow-up Date</th>
                        <th>Lead Source</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>

<script>
$(document).ready(function() {
    $('#leadsTable').DataTable();
});
</script>

@endsection
