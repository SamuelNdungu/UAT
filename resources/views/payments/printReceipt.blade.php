@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3">From:</h6>
                    <div>
                        <strong>Bima Connect</strong>
                    </div>
                    <div>Your Insurance Partner</div>
                    <div>Email: info@bimaconnect.com</div>
                    <div>Phone: +254 700 000000</div>
                </div>

                <div class="col-sm-6">
                    <h6 class="mb-3">To:</h6>
                    <div>
                        <strong>{{ $payment->corporate_name ?: $payment->customer_full_name }}</strong>
                    </div>
                    <div>Receipt No: {{ $payment->receipts->first()->receipt_number }}</div>
                    <div>Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</div>
                </div>
            </div>

            <div class="table-responsive-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="center">#</th>
                            <th>Description</th>
                            <th class="right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="center">1</td>
                            <td class="left">Payment Received</td>
                            <td class="right">KES {{ number_format($payment->payment_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($payment->receipts->first()->allocated_amount > 0)
            <div class="row">
                <div class="col-lg-12">
                    <h5 class="mt-4">Allocation Details</h5>
                    <div class="table-responsive-sm">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Policy Number</th>
                                    <th>Policy Type</th>
                                    <th>Registration No.</th>
                                    <th>Allocated Amount</th>
                                    <th>Allocation Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->allocations as $allocation)
                                <tr>
                                    <td>{{ $allocation->policy->policy_number }}</td>
                                    <td>{{ $allocation->policy->policy_type }}</td>
                                    <td>{{ $allocation->policy->reg_no }}</td>
                                    <td>KES {{ number_format($allocation->amount, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($allocation->created_at)->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-right"><strong>Total Allocated:</strong></td>
                                    <td colspan="2"><strong>KES {{ number_format($payment->receipts->first()->allocated_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-right"><strong>Remaining Amount:</strong></td>
                                    <td colspan="2"><strong>KES {{ number_format($payment->receipts->first()->remaining_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="mt-4">
                        <p class="text-muted font-13">Thank you for your business!</p>
                        <p class="text-muted font-13">This is a computer generated receipt and does not require a signature.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn { display: none; }
        .container { max-width: 100%; }
    }
</style>
@endpush