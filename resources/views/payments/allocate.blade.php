@extends('layouts.appPages')

@section('content')
<div class="container">
    <h1 class="my-4 text-center">Allocate Payment</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="payment-details mb-4">
        <h4>Payment Details</h4>
        <p><strong>Customer:</strong> 
            {{ $payment->corporate_name ?? $payment->customer_full_name }}
        </p>
        <p><strong>Payment Amount:</strong> {{ number_format($payment->payment_amount, 2) }}</p>
        <p><strong>Remaining Amount to Allocate:</strong> 
            <span id="remaining-to-allocate" class="text-success font-weight-bold">{{ number_format($payment->receipts->first()->remaining_amount, 2) }}</span>
        </p>
    </div>

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
                        <th scope="col">Insurer</th>
                        <th scope="col">Gross Premium</th>
                        <th scope="col">Allocation Amount</th>
                        <th scope="col">Remaining After Allocation</th>
                        <th scope="col">Balance</th> <!-- renamed header -->
                    </tr>
                </thead>
                <tbody> 
@foreach($policies as $policy)
    @php
        // remainingPremium = outstanding premium on policy
        $remainingPremium = max($policy->gross_premium - $policy->paid_amount, 0);
     
        $receiptRemaining = $payment->receipts->first()->remaining_amount ?? $payment->payment_amount;
        $maxAllocation = min($remainingPremium, $receiptRemaining);

        // Use old() allocation value if present, otherwise default to 0.00
        $oldAllocation = old('allocations.' . $policy->id . '.allocation_amount', 0);
        $initialAllocation = is_numeric($oldAllocation) ? (float) $oldAllocation : 0.00;

        // Remaining after allocation should reflect maxAllocation minus the allocation entered
        $remainingAfterAllocation = max(0, $maxAllocation - $initialAllocation);

        // Compute the displayed balance to match policy list: prefer policy->balance when available
        $displayBalance = isset($policy->balance)
            ? max((float)$policy->balance, 0)
            : max($policy->gross_premium - $policy->paid_amount, 0);

        // Format for display
        $displayBalanceFormatted = number_format($displayBalance, 2);
    @endphp
    <tr style="white-space: nowrap;">
        <td>{{ $policy->fileno }}</td>
        <td>{{ $policy->policy_type_name }}</td>
        <td>{{ $policy->reg_no }}</td> 
        <td>{{ $policy->insurer_name }}</td>
        <td>{{ number_format($policy->gross_premium, 2) }}</td>
        <td>
            <input type="number" step="0.01" 
                name="allocations[{{ $policy->id }}][allocation_amount]" 
                id="allocation_amount_{{ $policy->id }}" 
                class="form-control allocation-input" 
                value="{{ number_format($initialAllocation, 2, '.', '') }}"
                max="{{ $maxAllocation }}"
                {{ ($remainingPremium == 0) ? 'readonly' : '' }}>
            <input type="hidden" name="allocations[{{ $policy->id }}][policy_id]" value="{{ $policy->id }}">
        </td>
        <td>
            <input type="text" 
                class="form-control remaining-amount" 
                id="remaining_amount_{{ $policy->id }}" 
                value="{{ number_format($remainingAfterAllocation, 2) }}" 
                readonly>
        </td>

        <!-- Show canonical balance (gross - paid or policy->balance) -->
        <td>{{ $displayBalanceFormatted }}</td>
    </tr>
@endforeach
</tbody>

            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Allocate</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allocationInputs = document.querySelectorAll('.allocation-input');
        const totalRemainingElement = document.getElementById('remaining-to-allocate');

        // Parse initial remaining-to-allocate from server-rendered value (ensure numeric)
        const initialRemaining = parseFloat((totalRemainingElement.textContent || '0').replace(/,/g, '')) || 0;

        // Initialize total allocated amount from the allocation inputs (these now default to 0 or old values)
        let totalAllocated = Array.from(allocationInputs).reduce((sum, input) => {
            return sum + (parseFloat(input.value) || 0);
        }, 0);

        // Update the displayed remaining-to-allocate
        updateRemainingAmount(totalRemainingElement, initialRemaining, totalAllocated);

        allocationInputs.forEach(input => {
            input.addEventListener('input', function() {
                const policyId = this.id.split('_')[2];
                const maxAllocation = parseFloat(this.max) || 0;
                let allocationAmount = parseFloat(this.value) || 0;

                if (allocationAmount > maxAllocation) {
                    allocationAmount = maxAllocation;
                    this.value = allocationAmount.toFixed(2);
                }
                if (allocationAmount < 0) {
                    allocationAmount = 0;
                    this.value = allocationAmount.toFixed(2);
                }

                const remainingAfterAllocation = Math.max(0, maxAllocation - allocationAmount);
                document.getElementById(`remaining_amount_${policyId}`).value = remainingAfterAllocation.toFixed(2);

                // Recalculate total allocated
                totalAllocated = Array.from(allocationInputs).reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
                updateRemainingAmount(totalRemainingElement, initialRemaining, totalAllocated);
            });
        });

        function updateRemainingAmount(element, initialRemaining, totalAllocated) {
            const remainingToAllocate = Math.max(0, initialRemaining - totalAllocated);
            element.textContent = remainingToAllocate.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    });
</script>

@endsection
