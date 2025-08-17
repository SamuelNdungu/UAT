document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#policyTypesTable')) {
        $('#policyTypesTable').DataTable().destroy();
    }
    
    $('#policyTypesTable').DataTable({
        "serverSide": true,  // Enable server-side processing
        "processing": true,
        "ajax": {
            "url": "{{ route('policy_types.index') }}",
            "type": "GET",
            "data": function(d) {
                d.search = $('input[name=search]').val();
            }
        },
        "columns": [
            {"data": "type_name"},
            {"data": "user_id"},
            {"data": "created_at"},
            {"data": "updated_at"},
            {"data": "actions", "orderable": false}
        ],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "order": [[0, "asc"]],
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "processing": "Loading...",
            "search": "_INPUT_",
            "searchPlaceholder": "Search records",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
});