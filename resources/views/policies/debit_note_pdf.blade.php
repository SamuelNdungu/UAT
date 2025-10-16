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
        /* Header: two-column layout (logo left, contact details right) */
        .header {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            color: #25255F;
            border-bottom: 2px solid #111184;
            padding-bottom: 10px;
            gap: 10px;
        }
        /* Left column: logo - fixed width */
        .logo { flex: 0 0 40%; text-align: left; }
        .logo img { width: 220px; height: auto; display:block; }
        /* Right column: contact details - right aligned */
        .contact-details {
            flex: 1 1 60%;
            display: flex;
            flex-direction: column;
            align-items: flex-end; /* right align */
            text-align: left;
            font-size: 11px;
        }
        .contact-item { margin-bottom: 4px; }
        .contact-item i { margin-right: 8px; color:#02066F; min-width:16px; text-align:center; }
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
                width: 320px !important;
            }
            .contact-details { font-size:10px !important; }
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
    <header class="header" role="banner" aria-label="Company header">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:50%; vertical-align:top; padding-right:10px;">
                    <div class="logo" aria-hidden="true">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo.png'))) }}" alt="Company logo">
                    </div>
                </td>
                <td style="width:50%; vertical-align:top; text-align:right; padding-left:10px;">
                    <div class="contact-item"><i class="fa-solid fa-map-location-dot"></i>P.O BOX 14657</div>
                    <div class="contact-item"><i class="fa-solid fa-map-location-dot"></i>Nairobi 00100 GPO</div>
                    <div class="contact-item"><i class="fa-solid fa-phone"></i>Mobile: +254 0722 270897</div>
                    <div class="contact-item"><i class="fa-solid fa-envelope"></i>Email: info@emelyInsurance.co.ke</div>
                </td>
            </tr>
        </table>
    </header>

    <h3 class="my-1 text-center" style="margin-top:6px;">Debit Note</h3>

    <!-- Client Details Section -->
    <div class="group-heading">Client Details</div>
    <table>
        <tbody>
            <tr>
                <td class="form-group" style="width:33%;">
                    <label>File No:</label>
                    <div class="value">{{ $policy->fileno }}</div>
                </td>
                <td class="form-group" style="width:33%;">
                    <label>Customer Code:</label>
                    <div class="value">{{ $policy->customer_code }}</div>
                </td>
                <td class="form-group" style="width:34%;">
                    <label>Customer Name:</label>
                    <div class="value">{{ $policy->customer_name }}</div>
                </td>
            </tr>
            <tr>
                <td class="form-group">
                    <label>Phone:</label>
                    <div class="value">{{ $policy->customer->phone ?? $policy->phone ?? '-' }}</div>
                </td>
                <td class="form-group">
                    <label>Email:</label>
                    <div class="value">{{ $policy->customer->email ?? $policy->email ?? '-' }}</div>
                </td>
                <td class="form-group">
                    <label></label>
                    <div class="value"></div>
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