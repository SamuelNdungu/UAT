<table>
    <thead>
        <tr>
            <th>Policy No</th>
            <th>Customer Name</th>
            <th>Policy Type</th>
            <th>Insurer</th>
            <th>Premium</th>
            <th>Commission</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($policies as $policy)
            <tr>
                <td>{{ $policy->policy_no }}</td>
                <td>{{ $policy->customer_name ?? ($policy->customer->name ?? '-') }}</td>
                <td>{{ $policy->policyType->type_name ?? '-' }}</td>
                <td>{{ $policy->insurer->name ?? '-' }}</td>
                <td>{{ $policy->gross_premium }}</td>
                <td>{{ $policy->commission }}</td>
                <td>{{ $policy->start_date }}</td>
                <td>{{ $policy->end_date }}</td>
                <td>{{ $policy->status ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
