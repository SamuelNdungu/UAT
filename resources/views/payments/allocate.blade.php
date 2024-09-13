@extends('layouts.appPages')

@section('content')
<div class="container">
    <h1 class="my-4">Allocate Payment</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h4>Payment Details</h4>
    <p><strong>Customer:</strong> 
        @if($payment->corporate_name)
            {{ $payment->corporate_name }}
        @else
            {{ $payment->customer_full_name }}
        @endif
    </p>
    <p><strong>Payment Amount:</strong> {{ number_format($payment->payment_amount, 2) }}</p>
    <p><strong>Remaining Amount to Allocate:</strong> <span id="remaining-to-allocate">{{ number_format($payment->receipts->first()->remaining_amount, 2) }}</span></p>

    <form action="{{ route('payments.storeAllocation', $payment->id) }}" method="POST">
        @csrf

        <h4>Select Policies to Allocate</h4>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">File No</th>
                        <th scope="col">Policy Type</th>
                        <th scope="col">Reg No</th>
                        <th scope="col">Start Date</th>
                        <th scope="col">End Date</th>
                        <th scope="col">Insurer</th>
                        <th scope="col">Gross Premium</th>
                        <th scope="col">Allocation Amount</th>
                        <th scope="col">Remaining After Allocation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($policies as $policy)
                        @php
                            // Calculate remaining premium that can be allocated
                            $remainingPremium = max($policy->gross_premium - $policy->paid_amount, 0);

                            // Calculate the maximum allocation amount as the lesser of remaining premium and remaining amount in receipt
                            $maxAllocation = min($remainingPremium, $payment->receipts->first()->remaining_amount);

                            // Format the paid amount with commas
                            $formattedPaidAmount = number_format($policy->paid_amount, 2);

                            // Set the initial allocation amount to the paid amount or 0 if none exists
                            $initialAllocation = $policy->paid_amount > 0 ? $formattedPaidAmount : 0;
                        @endphp
                        <tr style="white-space: nowrap;">
                            <td>{{ $policy->fileno }}</td>
                            <td>{{ $policy->policy_type_name }}</td>
                            <td>{{ $policy->reg_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') }}</td>
                            <td>{{ $policy->insurer_name }}</td>
                            <td>{{ number_format($policy->gross_premium, 2) }}</td>
                            <td>
                                <input type="number" step="0.01" 
                                    name="allocations[{{ $policy->id }}][allocation_amount]" 
                                    id="allocation_amount_{{ $policy->id }}" 
                                    class="form-control allocation-input" 
                                    value="{{ $initialAllocation }}"
                                    max="{{ $maxAllocation }}"
                                    {{ $remainingPremium == 0 ? 'readonly' : '' }}>
                                <input type="hidden" name="allocations[{{ $policy->id }}][policy_id]" value="{{ $policy->id }}">
                            </td>
                            <td>
                                <input type="text" 
                                    class="form-control remaining-amount" 
                                    id="remaining_amount_{{ $policy->id }}" 
                                    value="{{ number_format($maxAllocation, 2) }}" 
                                    readonly>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-success mt-3">Allocate</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allocationInputs = document.querySelectorAll('.allocation-input');
        const totalRemainingElement = document.getElementById('remaining-to-allocate');

        const initialRemaining = parseFloat(totalRemainingElement.textContent.replace(/,/g, ''));
        let totalAllocated = 0;

        allocationInputs.forEach(input => {
            totalAllocated += parseFloat(input.value) || 0;

            input.addEventListener('input', function() {
                const policyId = this.id.split('_')[2];
                const maxAllocation = parseFloat(this.max);
                let allocationAmount = parseFloat(this.value) || 0;

                if (allocationAmount > maxAllocation) {
                    allocationAmount = maxAllocation;
                    this.value = allocationAmount.toFixed(2);
                }

                const remainingAfterAllocation = maxAllocation - allocationAmount;
                document.getElementById(`remaining_amount_${policyId}`).value = remainingAfterAllocation.toFixed(2);

                totalAllocated = Array.from(allocationInputs).reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
                const remainingToAllocate = initialRemaining - totalAllocated;

                totalRemainingElement.textContent = remainingToAllocate.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
        });

        // Initialize the remaining amount to allocate on page load
        const remainingToAllocate = initialRemaining - totalAllocated;
        totalRemainingElement.textContent = remainingToAllocate.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });
</script>

@endsection
