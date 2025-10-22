@extends('layouts.appPages')
@section('content')
<div class="container">
    <h2>Policy Endorsements for File: {{ $policy->fileno }}</h2>
    <a href="{{ route('policies.endorsements.create', $policy->id) }}" class="btn btn-success mb-3">Add Endorsement</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Reason</th>
                <th>Effective Date</th>
                <th>Premium Impact</th>
                <th>Description</th>
                <th>Document</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($endorsements as $endorsement)
            <tr>
                <td>{{ $endorsement->id }}</td>
                <td>{{ $endorsement->endorsement_type }}</td>
                <td>{{ $endorsement->reason ?? 'N/A' }}</td>
                <td>{{ $endorsement->effective_date }}</td>
                <td>{{ $endorsement->premium_impact }}</td>
                <td>{{ $endorsement->description }}</td>
                <td>
                    @if($endorsement->document_path)
                        <a href="{{ asset('storage/' . $endorsement->document_path) }}" target="_blank">View PDF</a>
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    <a href="{{ route('policies.endorsements.show', [$policy->id, $endorsement->id]) }}" class="btn btn-sm btn-info">Show</a>
                    <a href="{{ route('policies.endorsements.print', [$policy->id, $endorsement->id]) }}" class="btn btn-sm btn-secondary">Print Note</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
