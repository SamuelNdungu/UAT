File: {{ $policy->fileno ?? '' }}
Customer: {{ $policy->customer->customer_name ?? $policy->customer_name ?? '' }}
Policy No: {{ $policy->policy_no ?? '' }}
Type: {{ $policy->policyType->type_name ?? $policy->policy_type_id ?? '' }}
@if(in_array($policy->policy_type_id, [35,36,37]))
Vehicle: {{ $policy->reg_no ?? '' }} {{ $policy->make ?? '' }} {{ $policy->model ?? '' }}
@else
Desc: {{ \Illuminate\Support\Str::limit($policy->description ?? '', 80) }}
@endif
Expiry: {{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}
