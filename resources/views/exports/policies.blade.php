<!DOCTYPE html>
<html>
<head>
    <title>Policy Report</title>
    <style>
        /* General body styling */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        /* Heading alignment */
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px; /* Reduced font size */
        }

        th, td {
            padding: 5px;
            text-align: left;
            border: 1px solid #dddddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* CSS for landscape orientation during print */
        @page {
            size: A4 landscape;
            margin: 1mm; /* Adjust margins if needed */
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact; /* Ensure colors are printed correctly */
            }
            table {
                width: 100%; /* Force the table to fit the page */
                font-size: 9px; /* Further reduce font size for print */
            }

            th, td {
                padding: 3px; /* Reduce padding to save space */
            }

            h1 {
                font-size: 14px; /* Reduce the title font size */
            }
        }
    </style>

</head>
<body>
    <h1>Policy Report</h1>
    <table>
        <thead>
            <tr>
            <th>File No.</th>
        <th>Buss Date</th>
        <th>Cust Code</th>
        <th>Name</th>
        <th>Policy Type</th>
        <th>Coverage</th>
        <th>Start Date</th> 
        <th>End Date</th>
        <th>Insurer</th>
        <th>Policy No</th>
        <th>Reg.No</th>
       
        <th>Premium</th>
        <th>C. Rate (%)</th>
        <th>Comm.</th>
        <th>WHT</th>
        <th>Stamp Duty</th>
        <th>T.Levy</th>
        <th>PCF Levy</th>
        <th>Policy Charge</th>
        <th>AA Charges</th>
        <th>Other Charges</th>
        <th>Gross Premium</th>
        <th>Net Premium</th>
        <th>Paid Amount</th>
        <th>Balance</th>  
            </tr>
        </thead>
        <tbody>
            @foreach ($policies as $policy)
                <tr>
                <td>{{ $policy->fileno }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->created_at)->format('Y-m-d') }}</td> 
                            <td>{{ $policy->customer_code }}</td>
                            <td>{{ $policy->customer_name }}</td>
                            <td>{{ $policy->policyType ? $policy->policyType->type_name : 'N/A', }}</td>
                            <td>{{ $policy->coverage }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') }}</td>
                             
                            <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') }}</td>
                            <td>{{ $policy->insurer ? $policy->insurer->name : 'N/A',  }}</td>
                            <td>{{ $policy->policy_no }}</td>
                            <td>{{ $policy->reg_no }}</td>
                            
                            <td>{{ number_format($policy->premium, 2) }}</td>
                            <td>{{ $policy->c_rate }}</td>
                            <td>{{ number_format($policy->commission, 2) }}</td>
                            <td>{{ number_format($policy->wht, 2) }}</td>
                            <td>{{ number_format($policy->s_duty, 2) }}</td>
                            <td>{{ number_format($policy->t_levy, 2) }}</td>
                            <td>{{ number_format($policy->pcf_levy, 2) }}</td>
                            <td>{{ number_format($policy->policy_charge, 2) }}</td>
                            <td>{{ number_format($policy->aa_charges, 2) }}</td>
                            <td>{{ number_format($policy->other_charges, 2) }}</td>
                            <td>{{ number_format($policy->gross_premium, 2) }}</td>
                            <td>{{ number_format($policy->net_premium, 2) }}</td>
                            <td>{{ number_format($policy->paid_amount, 2) }}</td>
                            <td>{{ number_format($policy->balance, 2) }}</td>
                            
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
