<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debit Note</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #02066F;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            color: #25255F;
            border-bottom: 2px solid #111184;
            padding-bottom: 10px;
        }
        .header-content {
            display: flex;
            align-items: center;
        }
        .logo img {
            width: 250px;
        }
        .contact-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-size: 11px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .contact-item i {
            margin-right: 5px;
        }
        .date {
            font-size: 10px;
            text-align: right;
            margin-top: 10px;
        }
        .group-heading {
            background-color: #02066F;
            color: white;
            font-weight: bold;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #02066F;
        }
        .form-group {
            margin-bottom: 5px;
        }
        label {
            font-weight: bold;
            color: #02066F;
            display: inline-block;
            width: 150px;
            font-size: 13px;
        }
        .value {
            display: inline-block;
            color: #02066F;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
        }
        th, td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        .mt-5 {
            margin-top: 10px;
        }
        .qr-code {
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .preserve-formatting {
            white-space: pre-wrap;
            font-size: 12px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        @media print {
            body {
                margin: 0;
                padding: px;
                font-size: 12px;
            }
            .header {
                margin-bottom: 30px;
            }
            .group-heading {
                background-color: #02066F !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }
            th, td {
                padding: 5px !important;
            }
            .logo img {
                width: 350px !important;
            }
            .logo p {
                font-size: 11px !important;
                margin-left: 5px !important;
            }
            .qr-code {
                border: 1px solid #000 !important;
            }
        }
    </style>
</head>
<body> 
<div class="container">
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo.png'))) }}" alt="Logo">
            </div>
            <div class="contact-details">
                <div class="contact-item">
                    <i class="fa-solid fa-map-location-dot"></i> 4th Floor, Delta Corner Annex, Ring Road Westlands Lane, Nairobi, Kenya
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-phone"></i> <strong>Mobile:</strong> 0796 947159
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-envelope"></i> <strong>Email:</strong> customerservice@midrash.co.ke
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-globe"></i> <strong>Website:</strong> www.midrash.co.ke
                </div>
            </div>
        </div>
    </header>

    <h3 class="my-1 text-center">Debit Note</h3>

    <!-- Client Details Section -->
    <div class="group-heading">Client Details</div>
    <table>
        <tbody>
            <tr>
                <td class="form-group">
                    <label>File No:</label>
                    <span class="value">{{ $policy->fileno }}</span>
                </td>
                <td class="form-group">
                    <label>Customer Code</label>
                    <span class="value">{{ $policy->customer_code }}</span>
                </td>
                <td class="form-group">
                    <label>Customer Name</label>
                    <span class="value">{{ $policy->customer_name }}</span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Policy Details Section -->
    <div class="group-heading">Policy Details</div>
    <table>
        <tbody>
            <tr>
                <td class="form-group">
                    <label>Policy No</label>
                    <span class="value">{{ $policy->policy_no }}</span>
                </td>
                <td class="form-group">
                    <label>Policy Type</label>
                    <span class="value">{{ $policy->policy_type_name }}</span>
                </td>
                <td class="form-group">
                    <label>Coverage</label>
                    <span class="value">{{ $policy->coverage }}</span>
                </td> 
                <td class="form-group">
                    <label>Insurer</label>
                    <span class="value">{{ $policy->insurer_name }}</span>
                </td>
            </tr>
            <tr>
                <td class="form-group">
                    <label>Start Date</label>
                    <span class="value">{{ \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') }}</span>
                </td>
                <td class="form-group">
                    <label>Days</label>
                    <span class="value">{{ $policy->days }}</span>
                </td>
                <td class="form-group">
                    <label>End Date</label>
                    <span class="value">{{ \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') }}</span>
                </td>
                <td class="form-group">
                    <label></label>
                    <span class="value"></span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Vehicle Details Section -->
    @if ($policy->policy_type_id == '35' || $policy->policy_type_id == '36' || $policy->policy_type_id == '37')
        <div class="group-heading">Vehicle Details</div>
        <table>
            <tbody>
                <tr>
                    <td class="form-group">
                        <label>Make</label>
                        <span class="value">{{ $policy->make }}</span>
                    </td>
                    <td class="form-group">
                        <label>Model</label>
                        <span class="value">{{ $policy->model }}</span>
                    </td>
                    <td class="form-group">
                        <label>Y.O.M</label>
                        <span class="value">{{ $policy->yom }}</span>
                    </td>
                    <td class="form-group">
                        <label>CC</label>
                        <span class="value">{{ $policy->cc }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="form-group">
                        <label>Body Type</label>
                        <span class="value">{{ $policy->body_type }}</span>
                    </td>
                    <td class="form-group">
                        <label>Chassis No</label>
                        <span class="value">{{ $policy->chassisno }}</span>
                    </td>
                    <td class="form-group">
                        <label>Engine No</label>
                        <span class="value">{{ $policy->engine_no }}</span>
                    </td>
                    <td class="form-group">
                        <label></label>
                        <span class="value"></span>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Description Section -->
    @if ($policy->policy_type_id != '35' && $policy->policy_type_id != '36' && $policy->policy_type_id != '37')
        <div class="group-heading">Description</div>
        <table>
            <tbody>
                <tr>
                    <td class="form-group">
                        <label>Description</label>
                        <span class="value">{{ $policy->description }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Financial Details Section -->
    <div class="group-heading">Financial Details (KES)</div>
    <table>
        <tbody>
            <tr>
                <td class="form-group">
                    <label>Sum Insured</label>
                    <span class="value">{{ number_format($policy->sum_insured, 2) }}</span>
                </td>
                <td class="form-group">
                    <label>Rate</label>
                    <span class="value">{{ number_format($policy->rate, 2) }}%</span>
                </td>
                <td class="form-group">
                    <label>Basic Premium</label>
                    <span class="value">{{ number_format($policy->premium, 2) }}</span>
                </td> 
                <td class="form-group">
                    <label>S. Duty</label>
                    <span class="value">{{ number_format($policy->s_duty, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td class="form-group">
                    <label>T. Levy</label>
                    <span class="value">{{ number_format($policy->t_levy, 2) }}</span>
                </td>
                <td class="form-group">
                    <label>PCF Levy</label>
                    <span class="value">{{ number_format($policy->pcf_levy, 2) }}</span>
                </td>
                <td class="form-group">
                    <label>Policy Charge</label>
                    <span class="value">{{ number_format($policy->policy_charge, 2) }}</span>
                </td>
                <td class="form-group">
                    <label>Other Charges</label>
                    <span class="value">{{ number_format($policy->other_charges, 2) }}</span>
                </td> 
            </tr>
            @if ($policy->policy_type_id == '35' || $policy->policy_type_id == '36' || $policy->policy_type_id == '37')
            <tr>
                <td class="form-group">
                    <label>PVT</label>
                    <span class="value">{{ number_format($policy->pvt, 2) }}</span>
                </td>
                <td class="form-group">
                    <label>Excess</label>
                    <span class="value">{{ number_format($policy->excess, 2) }}</span>
                </td>
                <td class="form-group">
                    <label>Courtesy Car</label>
                    <span class="value">{{ number_format($policy->courtesy_car, 2) }}</span>
                </td>
                <td class="form-group">
                    <label>PPL</label>
                    <span class="value">{{ number_format($policy->ppl, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td class="form-group">
                    <label>Road Rescue</label>
                    <span class="value">{{ number_format($policy->road_rescue, 2) }}</span>
                </td>
            @endif
                <td class="form-group">
                    <label>Gross Premium</label>
                    <span class="value">{{ number_format($policy->gross_premium, 2) }}</span>
                </td>
                <td class="form-group">
                    <label></label>
                    <span class="value"></span>
                </td>
                <td class="form-group">
                    <label></label>
                    <span class="value"></span>
                </td>
                <td class="form-group">
                    <label></label>
                    <span class="value"></span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Cover Details Section -->
    <div class="group-heading">Cover Details</div>
    <p class="preserve-formatting">{{ $policy->cover_details }}</p>

    <div style="margin-top: 50px; display: flex; justify-content: space-between; font-size: 11px;">
        <div><strong>Prepared by:</strong> {{ Auth::user()->name }}</div>
        <div><strong>Print Date:</strong> {{ \Carbon\Carbon::now()->format('d-m-Y') }}</div>
    </div>
</div>
</body>
</html>

 