@extends('layouts.appPages')

@section('content')
<div class="container">
    <div class="gradient-banner mb-4">
        <div class="d-flex align-items-center">
            <span class="banner-icon me-2"><i class="fas fa-shield-alt"></i></span>
            <h2 class="my-2 mb-0" style="font-weight: 700; letter-spacing: 1px;">Policy Types</h2>
        </div>
           </div>
    <hr class="section-divider mb-4">
    
    <a href="{{ route('policy_types.create') }}" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Add Policy Type</a>
    <hr class="section-divider mb-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
        <div class="card shadow-sm border-0 mb-3" style="background: #f8fafc; border-radius: 12px;">
            <hr class="section-divider mb-4">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
                <hr class="section-divider mb-4">
                <table id="policyTypesTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                    <thead style="white-space: nowrap;">
                        <tr>
                            <th><i class="fas fa-file-signature"></i> Type Name</th>
                            <th><i class="fas fa-user"></i> User ID</th>
                            <th><i class="fas fa-calendar-plus"></i> Created At</th>
                            <th><i class="fas fa-calendar-check"></i> Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody style="white-space: nowrap;">
                        @foreach($types as $type)
                        <tr>
                            <td>{{ $type->type_name }}</td>
                            <td>{{ $type->user_id }}</td>
                            <td>{{ $type->created_at }}</td>
                            <td>{{ $type->updated_at }}</td>
                            <td style="white-space: nowrap; position: sticky; right: 0; background-color: white; z-index: 100; padding: 2px; border-left: 1px solid #ddd;">
                                <a href="{{ route('policy_types.show', $type->id) }}" class="btn btn-info btn-xs" aria-label="View" title="View" style="font-size: 0.7rem; padding: 2px 5px;">
                                    <i class="fas fa-eye" aria-hidden="true" style="font-size: 0.7rem;"></i>
                                </a>
                                <a href="{{ route('policy_types.edit', $type->id) }}" class="btn btn-warning btn-xs" aria-label="Edit" title="Edit" style="font-size: 0.7rem; padding: 2px 5px;">
                                    <i class="fas fa-edit" aria-hidden="true" style="font-size: 0.7rem;"></i>
                                </a>
                                <form action="{{ route('policy_types.destroy', $type->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" aria-label="Delete" title="Delete" style="font-size: 0.7rem; padding: 2px 5px;">
                                        <i class="fas fa-trash" aria-hidden="true" style="font-size: 0.7rem;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><i class="fas fa-file-signature"></i> Type Name</th>
                            <th><i class="fas fa-user"></i> User ID</th>
                            <th><i class="fas fa-calendar-plus"></i> Created At</th>
                            <th><i class="fas fa-calendar-check"></i> Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <hr class="section-divider mt-4 mb-4">
            <!-- Laravel pagination links removed for DataTables client-side pagination -->
        </div>
    <!-- End card-body -->

<!-- First, include jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Then include DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#policyTypesTable')) {
        $('#policyTypesTable').DataTable().destroy();
    }
    
    $('#policyTypesTable').DataTable({
        "processing": true,
        "pageLength": 20,  // Default page length
        "lengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],  // More options for page length
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
