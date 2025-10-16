@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Debtors Aging Cards -->
        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-gold card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('collection.index', ['filter' => 'less_than_30']) }}'">
                <div class="inner">
                    <h5> KES {{ number_format($metrics['balanceLessThan30'], 2) }} </h5>
                    <p>  < 30 Days </p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-day" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-blue card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('collection.index', ['filter' => '30_to_60']) }}'">
                <div class="inner">
                    <h5> KES {{ number_format($metrics['balance30To60'], 2) }} </h5>
                    <p>  30-60 Days </p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-alt" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-red card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('collection.index', ['filter' => '60_to_90']) }}'">
                <div class="inner">
                    <h5> KES {{ number_format($metrics['balance60To90'], 2) }} </h5>
                    <p>  60-90 Days </p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-check" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card-box bg-purple card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('collection.index', ['filter' => 'more_than_90']) }}'">
                <div class="inner">
                    <h5> KES {{ number_format($metrics['balanceMoreThan90'], 2) }} </h5>
                    <p>  > 90 Days </p>
                </div>
                <div class="icon">
                    <i class="fa fa-hourglass-end" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card card-danger mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">
                    <h4 class="card-title">Debtors List</h4>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">

            <div class="col-md-6 text-md-end text-start"> 

                    <a href="{{ route('collection.export.pdf') }}" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.9rem;">
                        <i class="fas fa-file-pdf" style="font-size: 0.65rem;"></i> Export PDF
                    </a>

                    <a href="{{ route('collection.export.excel') }}" class="btn btn-success" style="padding: 5px 10px; font-size: 0.9rem;">
                        <i class="fas fa-file-excel" style="font-size: 0.65rem;"></i> Export Excel
                    </a>
                </div>

                <table id="myTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                    <thead style="white-space: nowrap;">
                        <tr>
                            <th>File No.</th>
                            <th>Entry Date</th>
                            <th>Customer Code</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Policy Type</th>
                            <th>Reg.No</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Gross Premium</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Aging Band</th> <!-- New Column for Aging Band -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody style="white-space: nowrap;">
                        @php $customerCache = []; @endphp
                        @foreach($filteredPolicies as $policy)
                        <tr>
                            <td>{{ $policy->fileno }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->created_at)->format('d-m-Y') }}</td>
                            <td>{{ $policy->customer_code ?? '-' }}</td>
                            <td>{{ $policy->customer_name }}</td>
                            {{-- Email: prefer fields on $policy, fallback to customers table (cached) --}}
                            @php
                                $email = $policy->email ?? $policy->customer_email ?? null;
                                if (!$email) {
                                    $code = $policy->customer_code ?? null;
                                    if ($code) {
                                        if (!isset($customerCache[$code])) {
                                            $customerCache[$code] = \App\Models\Customer::where('customer_code', $code)->first(['email','phone']);
                                        }
                                        $email = $customerCache[$code]->email ?? null;
                                    }
                                }
                                $email = $email ?? '-';
                            @endphp
                            <td>{{ $email }}</td>

                            {{-- Phone: prefer fields on $policy, fallback to customers table (cached) --}}
                            @php
                                $phone = $policy->phone ?? $policy->telephone ?? null;
                                if (!$phone) {
                                    $code = $policy->customer_code ?? $policy->customer_code ?? null;
                                    if ($code) {
                                        if (!isset($customerCache[$code])) {
                                            $customerCache[$code] = \App\Models\Customer::where('customer_code', $code)->first(['email','phone']);
                                        }
                                        $phone = $customerCache[$code]->phone ?? null;
                                    }
                                }
                                $phone = $phone ?? '-';
                            @endphp
                            <td>{{ $phone }}</td>

                            <td>{{ $policy->policy_type_name }}</td>
                            <td>{{ $policy->reg_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('d-m-Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}</td>
                            <td>{{ number_format($policy->gross_premium, 2) }}</td>
                            <td>{{ number_format($policy->paid_amount, 2) }}</td>
                            <td>{{ number_format($policy->balance, 2) }}</td> <!-- Due Amount -->
                            <td>{{ $policy->aging_band }}</td> <!-- Aging Band -->

                              <!-- Action Buttons -->
        <td>
            <button class="btn btn-sm btn-primary" onclick="sendEmail('{{ $policy->customer_name }}', '{{ $policy->balance }}', '{{ $policy->customer_code }}')">
                <i class="fas fa-envelope"></i>
            </button>
            <button class="btn btn-sm btn-success" onclick="sendSMS('{{ $policy->customer_name }}', '{{ $policy->balance }}', '{{ $policy->customer_code }}')">
                <i class="fas fa-sms"></i>
            </button>
        </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>File No.</th>
                            <th>Buss Date</th>
                            <th>Customer Code</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Policy Type</th>
                            <th>Reg.No</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Gross Premium</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Aging Band</th> <!-- New Column for Aging Band -->
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this record?');
}
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



<script>function sendEmail(customerName, balance, customerCode) {
    if (confirm(`Send email to ${customerName} showing balance: KES ${balance}?`)) {
        // Make an AJAX request to send the email
        fetch(`/send-email/${customerCode}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ balance: balance })
        }).then(response => {
            if (response.ok) {
                alert('Email sent successfully!');
            } else {
                alert('Failed to send email.');
            }
        });
    }
}

function sendSMS(customerName, balance, customerCode) {
    if (confirm(`Send SMS to ${customerName} showing balance: KES ${balance}?`)) {
        // Make an AJAX request to send the SMS
        fetch(`/send-sms/${customerCode}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ balance: balance })
        }).then(response => {
            if (response.ok) {
                alert('SMS sent successfully!');
            } else {
                alert('Failed to send SMS.');
            }
        });
    }
}

</script>

@endsection
