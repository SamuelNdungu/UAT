@extends('layouts.pdf')

@section('content')
<style>
    /* Local styles for debit note (kept compact because layouts.pdf already provides basic PDF styles) */
    body { font-family: Arial, sans-serif; color: #02066F; }
    .group-heading { background-color: #02066F; color: white; font-weight: bold; padding: 10px; margin-bottom: 5px; border: 1px solid #02066F; }
    .form-group { margin-bottom: 5px; }
    label { font-weight: bold; color: #02066F; display: inline-block; width: 150px; font-size: 13px; }
    .value { display: inline-block; color: #02066F; font-size: 13px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
    th, td { padding: 5px; border-bottom: 1px solid #ddd; text-align: left; }
    .preserve-formatting { white-space: pre-wrap; font-size: 12px; border: 1px solid #ccc; padding: 10px; }
    .text-center { text-align: center; }
    .total-row { margin-top: 30px; display:flex; justify-content:space-between; }
</style>

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

          <div class="preserve-formatting">{{ $policy->description }}</div> 

    @endif

    <!-- Financial Details Section -->
    <div class="group-heading">Financial Details (KES)</div>

    @if ($policy->policy_type_id != '35' && $policy->policy_type_id != '36' && $policy->policy_type_id != '37')
        <!-- Non-vehicle policy types: show only selected fields -->
        <table>
            <tbody>
                <tr>
                    <td class="form-group">
                        <label>Basic Premium</label>
                        <span class="value">{{ number_format($policy->premium, 2) }}</span>
                    </td> 
                    <td class="form-group">
                        <label>S. Duty</label>
                        <span class="value">{{ number_format($policy->s_duty, 2) }}</span>
                    </td>
                    <td class="form-group">
                        <label>T. Levy</label>
                        <span class="value">{{ number_format($policy->t_levy, 2) }}</span>
                    </td>
                    <td class="form-group">
                        <label>PCF Levy</label>
                        <span class="value">{{ number_format($policy->pcf_levy, 2) }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="form-group">
                        <label>Policy Charge</label>
                        <span class="value">{{ number_format($policy->policy_charge, 2) }}</span>
                    </td>
                    <td class="form-group">
                        <label>Gross Premium</label>
                        <span class="value">{{ number_format($policy->gross_premium, 2) }}</span>
                    </td>
                    <td class="form-group"></td>
                    <td class="form-group"></td>
                </tr>
            </tbody>
        </table>
    @else
        <!-- Vehicle policy types: show full financial table -->
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
                    <td class="form-group"></td>
                    <td class="form-group"></td>
                </tr>
            </tbody>
        </table>
    @endif

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

@endsection