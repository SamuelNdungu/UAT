@extends('layouts.appPages')
@section('content')
<div class="container fancy-container">
    <div class="gradient-banner mb-4">
        <div class="d-flex align-items-center">
            <span class="banner-icon me-2"><i class="fas fa-car"></i></span>
            <h1 class="my-2 mb-0" style="font-weight: 700; letter-spacing: 1px;">Vehicle Types</h1>
        </div>
           </div>
    <hr class="section-divider mb-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <a href="{{ route('vehicle_types.create') }}" class="btn btn-primary mb-3" style="font-size:1.1rem; font-weight:600; padding:10px 24px;"><i class="fas fa-plus"></i> Add Vehicle Type</a>
    <div class="card-body">
        <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
            <table id="vehicleTypesTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                <thead style="white-space: nowrap;">
                    <tr>
                        <th><i class="fas fa-car"></i> Make</th>
                        <th><i class="fas fa-car-side"></i> Model</th>
                        <th><i class="fas fa-user"></i> User ID</th>
                        <th><i class="fas fa-calendar-plus"></i> Created At</th>
                        <th><i class="fas fa-calendar-check"></i> Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody style="white-space: nowrap;">
                    @foreach($vehicleTypes as $type)
                    <tr>
                        <td>{{ $type->make }}</td>
                        <td>{{ $type->model }}</td>
                        <td>{{ $type->user_id }}</td>
                        <td>{{ $type->created_at }}</td>
                        <td>{{ $type->updated_at }}</td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('vehicle_types.show', $type->id) }}" class="btn btn-info btn-xs" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('vehicle_types.edit', $type->id) }}" class="btn btn-warning btn-xs" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('vehicle_types.destroy', $type->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash-alt"></i>
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

<!-- First, include jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Then include DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#vehicleTypesTable')) {
        $('#vehicleTypesTable').DataTable().destroy();
    }
    $('#vehicleTypesTable').DataTable({
        "processing": true,
        "pageLength": 20,
        "lengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
        "ordering": true,
        "searching": true,
        "info": true,
        "responsive": true,
        "autoWidth": false,
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search records",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [{
            "targets": -1,
            "orderable": false
        }]
    });
});
</script>

<style>
.gradient-banner {
    background: linear-gradient(90deg, #4f8cff 0%, #6ed6ff 100%);
    border-radius: 16px;
    padding: 32px 28px 18px 28px;
    box-shadow: 0 4px 24px 0 rgba(60, 72, 88, 0.12);
    position: relative;
    color: #fff;
    margin-top: 24px;
    margin-bottom: 0;
}
.banner-icon {
    font-size: 2.2rem;
    color: #fff;
    background: rgba(255,255,255,0.12);
    border-radius: 50%;
    padding: 10px;
    box-shadow: 0 2px 8px 0 rgba(60, 72, 88, 0.10);
    margin-right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.section-divider {
    border: none;
    border-top: 1.5px solid #e0e6ed;
    margin: 0 0 24px 0;
    opacity: 0.7;
}
.card-clickable {
    cursor: pointer;
    transition: transform 0.3s ease-in-out;
}
.card-clickable:hover {
    transform: scale(1.05);
}
</style>
@endsection

