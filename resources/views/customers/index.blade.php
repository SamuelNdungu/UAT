@extends('layouts.app')

@section('content')

<div class="container"> 
    <div class="container">
        <div class="row mb-3">
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-cyan card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('customers.index', ['filter' => 'total']) }}'">
                    <div class="inner">
                        <h3>  {{ $metrics['totalCustomers'] }} </h3>
                        <p> Total Customers </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-chart-line" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-green card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('customers.index', ['filter' => 'active']) }}'">
                    <div class="inner">
                        <h3>  {{ $metrics['activeCustomers'] }}</h3>
                        <p>Active Customers </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-orange card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('customers.index', ['filter' => 'inactive']) }}'">
                    <div class="inner">
                        <h3>  {{ $metrics['inactiveCustomers'] }} </h3>
                        <p> Inactive Customers </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-file-alt" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-red card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('customers.index', ['filter' => 'claims']) }}'">
                    <div class="inner">
                        <h3> 50 </h3>
                        <p> Claims </p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="card card-danger">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6 d-flex align-items-center">
                        <h3 class="card-title">Customers List</h3>
                    </div>
                    <div class="col-md-6 text-md-end text-start">
                        <a href="{{ route('customers.create') }}" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">
                            <i class="fas fa-plus" style="font-size: 0.75rem;"></i> Add </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
                    <table id="myTable" class="table table-striped rounded-top" style="width: auto; font-size: 10px;">
                        <thead style="white-space: nowrap;">
                            <tr >
                                <th>Code</th>    
                                <th>Type</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>ID</th>
                                <th>KRA</th>
                                <th>Address</th>
                                <th>Contact Person</th>
                                <th>Notes</th>
                                <th>Occupation</th>
                                <th>Documents</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody" style="white-space: nowrap;">
                            @foreach($customers as $customer)
                            <tr class="{{ $customer->status ? 'active-customer' : 'inactive-customer' }} {{ $customer->has_claims ? 'has-claims' : '' }}">
                                <td>{{ $customer->customer_code }}</td>
                                <td>{{ $customer->customer_type }}</td>
                                <td>
                                    @if($customer->customer_type === 'Individual')
                                        {{ $customer->first_name . ' ' . $customer->last_name . ' ' . $customer->surname }}
                                    @elseif($customer->customer_type === 'Corporate')
                                        {{ $customer->corporate_name }}
                                    @endif
                                </td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->id_number }}</td>
                                <td>{{ $customer->kra_pin }}</td>
                                <td>{{ $customer->address }}</td>
                                <td>{{ $customer->contact_person }}</td>
                                <td>{{ $customer->notes }}</td>
                                <td>{{ $customer->customer_type === 'Individual' ? $customer->occupation : $customer->industry_segment }}</td>
                                <td>{{ $customer->documents }}</td>
                                <td>{{ $customer->status == 1 ? 'Active' : 'Inactive' }}</td>
                                <td style="white-space: nowrap; position: sticky; right: 0; background-color: white; z-index: 100; padding: 5px; border-left: 1px solid #ddd;">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-info btn-sm" aria-label="View" title="View" style="padding: 1px 8px;">
                                        <i class="fas fa-eye" aria-hidden="true" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning btn-sm" aria-label="Edit" title="Edit" style="padding: 1px 8px;">
                                        <i class="fas fa-pencil-alt" aria-hidden="true" style="font-size: 0.8rem;"></i>
                                    </a>
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" aria-label="Delete" title="Delete" style="padding: 1px 8px;">
                                            <i class="fas fa-trash" aria-hidden="true" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
       
    </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>

<script>
$(document).ready(function() {
    // Add hover animation
    $('.card-clickable').hover(
        function() {
            $(this).css({
                'transform': 'scale(1.05)',
                'transition': 'transform 0.3s ease-in-out'
            });
        },
        function() {
            $(this).css('transform', 'scale(1)');
        }
    );
});
</script>

<style>
.card-clickable {
    cursor: pointer;
    transition: transform 0.3s ease-in-out;
}

.card-clickable:hover {
    transform: scale(1.05);
}

.card-clickable:active {
    transform: scale(0.95);
}
</style>

@endsection
