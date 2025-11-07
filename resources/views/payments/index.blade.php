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
        <form method="GET" action="{{ route('payments.index') }}" class="row gx-2 gy-2 align-items-center">
            <div class="col-auto">
                <select name="filter" class="form-select form-select-sm">
                    <option value="both" {{ request('filter') == 'both' ? 'selected' : '' }}>Both</option>
                    <option value="allocated" {{ request('filter') == 'allocated' ? 'selected' : '' }}>Allocated</option>
                    <option value="unallocated" {{ request('filter', 'unallocated') == 'unallocated' ? 'selected' : '' }}>Unallocated</option>
                    <option value="zero-payment" {{ request('filter') == 'zero-payment' ? 'selected' : '' }}>Payment = 0</option>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From">
            </div>
            <div class="col-auto">
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To">
            </div>
            <div class="col-auto">
                <input type="text" name="customer" class="form-control form-control-sm" value="{{ request('customer') }}" placeholder="Customer name or corporate">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Filter</button>
            </div>
        </form>
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
                            @php $receipt = $payment->receipts->first(); @endphp
                            <tr data-allocated="{{ $receipt ? $receipt->allocated_amount : 0 }}"
                                data-remaining="{{ $receipt ? $receipt->remaining_amount : 0 }}"
                                data-payment="{{ $payment->payment_amount }}">
                                <td>{{ $payment->id }}</td>
                                <td>{{ $receipt ? $receipt->receipt_number : '-' }}</td>
                                <td>{{ $payment->corporate_name ?: $payment->customer_full_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                <td>{{ number_format($payment->payment_amount, 2) }}</td>
                                <td>{{ number_format($receipt ? $receipt->allocated_amount : 0, 2) }}</td>
                                <td>{{ number_format($receipt ? $receipt->remaining_amount : $payment->payment_amount, 2) }}</td>
                                <td>
                                    @if($receipt && $receipt->remaining_amount == 0)
                                        <!-- Show Unallocate button only -->
                                        @can('unallocate', $payment)
                                            <form action="{{ route('allocations.unallocateAll', $payment->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to unallocate all policies for this payment?');" title="Unallocate all policies" aria-label="Unallocate all policies for payment {{ $payment->id }}">
                                                    <i class="fas fa-undo" aria-hidden="true" style="font-size:0.85rem; margin-right:6px;"></i> Unallocate
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-danger btn-sm" disabled title="You do not have permission to unallocate" aria-label="Unallocate disabled">Unallocate</button>
                                        @endcan
                                    @elseif($receipt && $receipt->allocated_amount == 0)
                                        <!-- Show Allocate button only -->
                                        @can('allocate', $payment)
                                            <a href="{{ route('payments.allocate', $payment->id) }}" class="btn btn-warning btn-sm" title="Allocate payment to policies" aria-label="Allocate payment {{ $payment->id }}">
                                                <i class="fas fa-hand-holding" aria-hidden="true" style="font-size:0.85rem; margin-right:6px;"></i> Allocate
                                            </a>
                                        @else
                                            <button class="btn btn-warning btn-sm" disabled title="You do not have permission to allocate" aria-label="Allocate disabled">Allocate</button>
                                        @endcan
                                    @else
                                        <!-- Show both Allocate and Unallocate buttons -->
                                        @can('allocate', $payment)
                                            <a href="{{ route('payments.allocate', $payment->id) }}" class="btn btn-warning btn-sm" title="Allocate payment to policies" aria-label="Allocate payment {{ $payment->id }}">Allocate</a>
                                        @else
                                            <button class="btn btn-warning btn-sm" disabled title="You do not have permission to allocate" aria-label="Allocate disabled">Allocate</button>
                                        @endcan

                                        @can('unallocate', $payment)
                                            <form action="{{ route('allocations.unallocateAll', $payment->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to unallocate all policies for this payment?');" title="Unallocate all policies" aria-label="Unallocate all policies for payment {{ $payment->id }}">
                                                    <i class="fas fa-undo" aria-hidden="true" style="font-size:0.85rem; margin-right:6px;"></i> Unallocate
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-danger btn-sm" disabled title="You do not have permission to unallocate" aria-label="Unallocate disabled">Unallocate</button>
                                        @endcan
                                    @endif

                                    <!-- Add Print Receipt Button -->
                                    @can('print', $payment)
                                        <a href="{{ route('payments.printReceipt', $payment->id) }}" class="btn btn-success btn-sm" title="Print receipt" aria-label="Print receipt for payment {{ $payment->id }}">
                                            <i class="fas fa-print" aria-hidden="true" style="font-size:0.85rem; margin-right:6px;"></i> Print
                                        </a>
                                    @else
                                        <button class="btn btn-success btn-sm" disabled title="You do not have permission to print receipts" aria-label="Print disabled">Print Receipt</button>
                                    @endcan
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
                <div class="mt-3 d-flex justify-content-center">
                    {{ $payments->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Client-side filtering kept for quick UI but server-side filters have been added.
    // The server-side form submits GET parameters; client-side filtering remains optional.
</script>
@endsection
