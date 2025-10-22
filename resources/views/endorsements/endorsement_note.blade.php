@extends('layouts.pdf')

@section('content')
<style>
    body { font-family: Arial, sans-serif; font-size: 14px; }
    .section { margin-bottom: 15px; }
    .bold { font-weight: bold; }
    .note-title { text-align: center; margin-bottom: 10px; }
</style>

    <div class="note-title">
        <h2>Policy Endorsement Note</h2>
        <p>File No: <span class="bold">{{ $policy->fileno }}</span> &nbsp; | &nbsp; Policy No: <span class="bold">{{ $policy->policy_no }}</span></p>
    </div>

    <div class="section">
        <span class="bold">Endorsement Type:</span> {{ $endorsement->endorsement_type }}<br>
        <span class="bold">Effective Date:</span> {{ $endorsement->effective_date }}<br>
        <span class="bold">Premium Impact:</span> {{ number_format($endorsement->premium_impact, 2) }}<br>
    </div>

    <div class="section">
        <span class="bold">Description:</span><br>
        {{ $endorsement->description }}
    </div>

    <div class="section">
        <span class="bold">Generated On:</span> {{ now()->format('d-m-Y H:i') }}
    </div>

@endsection
