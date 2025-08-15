@extends('layouts.appPages')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-lg-4 col-sm-6">
                    <div class="card-box bg-cyan card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('fees.index', ['filter' => 'total']) }}'">
                        <div class="inner">
                            <h3>KES {{ number_format($metrics['totalAmount'] ?? 0, 2) }}</h3>
                            <p>Total Amount</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-money-bill" aria-hidden="true"></i>
                        </div>
                        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="card-box bg-purple card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('fees.index', ['filter' => 'total']) }}'">
                        <div class="inner">
                            <h3>KES {{ $metrics['totalFees'] > 0 ? number_format($metrics['totalAmount'] / $metrics['totalFees'], 2) : '0.00' }}</h3>
                            <p>Average Fee Amount</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-calculator" aria-hidden="true"></i>
                        </div>
                        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="card-box bg-info card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('fees.index', ['filter' => 'total']) }}'">
                        <div class="inner">
                            <h3>{{ $metrics['totalFees'] ?? 0 }}</h3>
                            <p>Total Invoices</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-file-invoice" aria-hidden="true"></i>
                        </div>
                        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                
            </div>
            <div class="row mb-3">
                <div class="col-lg-4 col-sm-6">
                    <div class="card-box bg-green card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('fees.index', ['filter' => 'paid']) }}'">
                        <div class="inner">
                            <h3>{{ $metrics['paidFees'] ?? 0 }}</h3>
                            <p>Paid Invoices</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                        </div>
                        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6">
                    <div class="card-box bg-orange card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('fees.index', ['filter' => 'pending']) }}'">
                        <div class="inner">
                            <h3>{{ $metrics['pendingFees'] ?? 0 }}</h3>
                            <p>Pending Invoices</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-clock" aria-hidden="true"></i>
                        </div>
                        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6">
                    <div class="card-box bg-red card-clickable" style="border-radius: 5px; cursor: pointer; transition: transform 0.3s ease-in-out;" onclick="window.location='{{ route('fees.index', ['filter' => 'overdue']) }}'">
                        <div class="inner">
                            <h3>{{ $metrics['overdueFees'] ?? 0 }}</h3>
                            <p>Overdue Invoices</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                        </div>
                        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
 
                
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Fees List</h3>
                            <div>
                                <a href="{{ route('fees.create') }}" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">
                                    <i class="fas fa-plus" style="font-size: 0.65rem;"></i> Add Fee
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="feesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Cust Code</th>
                                        <th>Cust Name</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fees as $fee)
                                    <tr>
                                        <td>{{ $fee->invoice_number }}</td>
                                        <td>{{ $fee->customer ? $fee->customer->customer_code : 'N/A' }}</td>
                                        <td>{{ $fee->customer ? $fee->customer->customer_name : 'N/A' }}</td>
                                        <td>{{ $fee->date }}</td>
                                        <td>{{ \Carbon\Carbon::parse($fee->date)->addDays(30)->format('Y-m-d') }}</td>
                                        <td>{{ number_format($fee->amount, 2) }}</td>
                                        <td>{{ $fee->description }}</td>
                                        <td>{{ $fee->status }}</td>
                                        <td>
                                            <a href="{{ route('fees.show', $fee->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('fees.edit', $fee->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a href="{{ route('fees.print', $fee->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" style="text-align:right">Total:</th>
                                        <th></th>
                                        <th colspan="3"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(function () {
        // Add hover effect for cards
        $('.card-clickable').hover(
            function() {
                $(this).css('transform', 'scale(1.05)');
            },
            function() {
                $(this).css('transform', 'scale(1)');
            }
        );
        var feesTable = $('#feesTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api();
                
                // Calculate total amount from all visible rows
                var total = api
                    .column(5, { search: 'applied', page: 'current' })
                    .data()
                    .reduce(function (acc, val) {
                        // Remove currency symbol, commas, and spaces, then convert to number
                        return acc + parseFloat(val.replace(/[^d.-]/g, '')) || 0;
                    }, 0);
                
                // Update total in footer with currency symbol and formatting
                $(api.column(5).footer()).html('KES ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        });
    
        // Add hover effect for cards
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

    // Add styles for card hover effect
    $('<style>\n        .card-clickable {\n            cursor: pointer;\n            transition: transform 0.3s ease-in-out;\n        }\n        .card-clickable:hover {\n            transform: scale(1.05);\n        }\n    </style>').appendTo('head');
</script>
@endpush
@endsection