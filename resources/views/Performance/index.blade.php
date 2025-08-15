@extends('layouts.app')

@section('content')
<title>Sales Table</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .table-smaller {
            font-size: 0.8rem; /* Adjust the font size as needed */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 d-flex align-items-center">
            <h4 class="card-title">Sales</h4>                    
        </div>
        <div class="row mb-3">
            <div class="col-md-6 text-md-end text-start">
                <a href="{{ route('export.pdf') }}" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.9rem;">
                    <i class="fas fa-file-pdf" style="font-size: 0.65rem;"></i> Export PDF 
                </a>
                <a href="{{ route('export.excel') }}" class="btn btn-success" style="padding: 5px 10px; font-size: 0.9rem;">
                    <i class="fas fa-file-excel" style="font-size: 0.65rem;"></i> Export Excel 
                </a>
            </div>
        </div>
        <table class="table table-striped table-bordered table-smaller">
            <thead>
                <tr>
                    <th>Cust Code</th>
                    <th>Name</th>
                    <th>Policy Type</th>
                    <th>Insurer</th>
                    <th>Reg.No</th>
                    <th>Gross Premium</th>
                    <th>Commission</th>
                    <th>Paid Amount</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody style="white-space: nowrap;">
                @php
                    $totalGrossPremium = 0;
                    $totalCommission = 0;
                    $totalPaidAmount = 0;
                    $totalBalance = 0;
                @endphp
                @foreach($policies as $policy)
                <tr>
                    <td>{{ $policy->customer_code }}</td>
                    <td>{{ $policy->customer_name }}</td>
                    <td>{{ $policy->policy_type_name }}</td> 
                    <td>{{ $policy->insurer_name }}</td>  
                    <td>{{ $policy->reg_no }}</td> 
                    <td class="text-end">{{ number_format($policy->gross_premium, 2) }}</td> 
                    <td class="text-end">{{ number_format($policy->commission, 2) }}</td> 
                    <td class="text-end">{{ number_format($policy->paid_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($policy->balance, 2) }}</td>
                </tr>
                @php
                    $totalGrossPremium += $policy->gross_premium;
                    $totalPaidAmount += $policy->paid_amount;
                    $totalCommission += $policy->commission;
                    $totalBalance += $policy->balance;
                @endphp
                @endforeach 
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Totals:</td>
                    <td class="text-end fw-bold">{{ number_format($totalGrossPremium, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($totalCommission, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($totalPaidAmount, 2) }}</td>                    
                    <td class="text-end fw-bold">{{ number_format($totalBalance, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <!-- Pagination Links -->
        <div class="d-flex justify-content-center">
            {{ $policies->links() }}
        </div>
    </div>

    <!-- Bootstrap JS and its dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
@endsection
