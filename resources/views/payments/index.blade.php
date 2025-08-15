@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- First Row of Metrics -->
        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-cyan" style="border-radius: 5px;">
                <div class="inner">
                    <h5>KES {{ number_format($metrics['totalSales'], 2) }}</h5>
                    <p>Total Sales</p>
                </div>
                <div class="icon">
                    <i class="fa fa-chart-line" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-green" style="border-radius: 5px;">
                <div class="inner">
                    <h5>KES {{ number_format($metrics['totalPayments'], 2) }}</h5>
                    <p>Total Payments</p>
                </div>
                <div class="icon">
                    <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-orange" style="border-radius: 5px;">
                <div class="inner">
                    <h5>KES {{ number_format($metrics['balance'], 2) }}</h5>
                    <p>Balance</p>
                </div>
                <div class="icon">
                    <i class="fa fa-balance-scale" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-red" style="border-radius: 5px;">
                <div class="inner">
                    <h5>KES {{ number_format($metrics['totalAllocated'], 2) }}</h5>
                    <p>Total Allocated</p>
                </div>
                <div class="icon">
                    <i class="fa fa-wallet" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <h4 class="my-4">Payments</h4>
    </div>

    <div class="mb-3">
        <label><input type="radio" name="filter" value="allocated" onclick="filterTable()"> Allocated</label>
        <label><input type="radio" name="filter" value="unallocated" onclick="filterTable()" checked> Unallocated</label>
        <label><input type="radio" name="filter" value="both" onclick="filterTable()"> Both</label>
        <label><input type="radio" name="filter" value="zero-payment" onclick="filterTable()"> Payment = 0</label> <!-- New Radio Button -->
    </div>

    <div class="card card-danger">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">
                    <h4 class="card-title">Payments List</h4>
                </div>
                <div class="col-md-6 text-md-end text-start">
                    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                        <i class="fas fa-plus" style="font-size: 0.75rem;"></i> Add
                    </a>

                    <a href="{{ route('payments.export.pdf') }}" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.9rem;">
                        <i class="fas fa-file-pdf" style="font-size: 0.65rem;"></i> Export PDF
                    </a>

                    <a href="{{ route('payments.export.excel') }}" class="btn btn-success" style="padding: 5px 10px; font-size: 0.9rem;">
                        <i class="fas fa-file-excel" style="font-size: 0.65rem;"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
                <table id="paymentsTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                    <thead style="white-space: nowrap;">
                        <tr>
                            <th>ID</th>
                            <th>Receipt No.</th>
                            <th>Customer</th>
                            <th>Payment Date</th>
                            <th>Payment Amount</th>
                            <th>Allocated Amount</th>
                            <th>Remaining Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr data-allocated="{{ $payment->receipts->first()->allocated_amount }}"
                                data-remaining="{{ $payment->receipts->first()->remaining_amount }}"
                                data-payment="{{ $payment->payment_amount }}">
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->receipts->first()->receipt_number }}</td>
                                <td>{{ $payment->corporate_name ?: $payment->customer_full_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                <td>{{ number_format($payment->payment_amount, 2) }}</td>
                                <td>{{ number_format($payment->receipts->first()->allocated_amount, 2) }}</td>
                                <td>{{ number_format($payment->receipts->first()->remaining_amount, 2) }}</td>
                                <td>
                                    @if($payment->receipts->first()->remaining_amount == 0)
                                        <!-- Show Unallocate button only -->
                                        <form action="{{ route('allocations.unallocateAll', $payment->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to unallocate all policies for this payment?');">Unallocate</button>
                                        </form>
                                    @elseif($payment->receipts->first()->allocated_amount == 0)
                                        <!-- Show Allocate button only -->
                                        <a href="{{ route('payments.allocate', $payment->id) }}" class="btn btn-warning btn-sm">Allocate</a>
                                    @else
                                        <!-- Show both Allocate and Unallocate buttons -->
                                        <a href="{{ route('payments.allocate', $payment->id) }}" class="btn btn-warning btn-sm">Allocate</a>
                                        <form action="{{ route('allocations.unallocateAll', $payment->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to unallocate all policies for this payment?');">Unallocate</button>
                                        </form>
                                    @endif
                                    <!-- Add Print Receipt Button -->
                                    <a href="{{ route('payments.printReceipt', $payment->id) }}" class="btn btn-success btn-sm">Print Receipt</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Receipt No.</th>
                            <th>Customer</th>
                            <th>Payment Date</th>
                            <th>Payment Amount</th>
                            <th>Allocated Amount</th>
                            <th>Remaining Amount</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function filterTable() {
        var filterValue = document.querySelector('input[name="filter"]:checked').value;
        var rows = document.querySelectorAll('#paymentsTable tbody tr');

        rows.forEach(function(row) {
            var allocatedAmount = parseFloat(row.getAttribute('data-allocated'));
            var remainingAmount = parseFloat(row.getAttribute('data-remaining'));
            var paymentAmount = parseFloat(row.getAttribute('data-payment'));

            if (filterValue === 'allocated') {
                row.style.display = remainingAmount === 0 ? '' : 'none';
            } else if (filterValue === 'unallocated') {
                row.style.display = allocatedAmount === 0 ? '' : 'none';
            } else if (filterValue === 'zero-payment') {
                row.style.display = paymentAmount === 0 ? '' : 'none';
            } else {
                row.style.display = (allocatedAmount > 0 && remainingAmount > 0) ? '' : 'none';
            }
        });
    }

    // Initial filter on page load
    document.addEventListener('DOMContentLoaded', function() {
        filterTable();
    });
</script>
@endsection
