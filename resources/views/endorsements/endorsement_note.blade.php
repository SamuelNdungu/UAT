@extends('layouts.pdf')

@section('content')
<style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 14px; color: #222; }
    .note-header { padding:  20px; border-bottom: 4px solid #e9ecef; display:flex; justify-content:space-between; align-items:center; }
    .note-title { font-size: 1.25rem; font-weight:700; }
    .note-meta { font-size: 0.95rem; color:#444; }

    /* Accent variants */
    .accent-addition { background: #e6f9ec; border-bottom-color: #28a745; }
    .accent-addition .note-title { color: #155724; }
    .accent-deletion { background: #fdecea; border-bottom-color: #dc3545; }
    .accent-deletion .note-title { color: #721c24; }
    .accent-neutral { background: #f4f6f8; border-bottom-color: #6c757d; }
    .accent-neutral .note-title { color: #343a40; }

    .note-body { padding: 18px 20px; }
    .section { margin-bottom: 14px; }
    .bold { font-weight: 700; }
    .detail-row { margin:6px 0; color:#333; }
    .amount { font-size: 1.75rem; font-weight:800; }

    .amount.positive { color: #28a745; }
    .amount.negative { color: #dc3545; }

    /* Additions / Deletions item styling */
    .items { margin-top:8px; padding-left:0; list-style:none; }
    .item { padding:8px 10px; border-radius:6px; margin-bottom:8px; background:#fff; border:1px solid #e9ecef; }
    .item.add { border-left:6px solid #28a745; }
    .item.del { border-left:6px solid #dc3545; }

    .item .k { color:#6c757d; min-width:140px; display:inline-block; font-weight:600; }
    .item .v { color:#222; font-weight:600; display:inline-block; }

    .small-muted { font-size:0.85rem; color:#6c757d; margin-top:6px; }
</style>

@php
    $type = strtolower($endorsement->endorsement_type ?? '');
    if (str_contains($type, 'add')) {
        $accent = 'accent-addition';
        $noteLabel = 'ADDITIONAL DEBIT NOTE';
    } elseif (str_contains($type, 'delet') || str_contains($type, 'cancel')) {
        $accent = 'accent-deletion';
        $noteLabel = 'CREDIT NOTE';
    } else {
        $accent = 'accent-neutral';
        $noteLabel = strtoupper($endorsement->endorsement_type ?? 'Endorsement');
    }
    // Ensure additions/deletions are iterable if stored as JSON string
    if (is_string($endorsement->additions) && $endorsement->additions !== '') {
        $decodedAdd = json_decode($endorsement->additions, true);
        if (json_last_error() === JSON_ERROR_NONE) $endorsement->additions = $decodedAdd;
    }
    if (is_string($endorsement->deletions) && $endorsement->deletions !== '') {
        $decodedDel = json_decode($endorsement->deletions, true);
        if (json_last_error() === JSON_ERROR_NONE) $endorsement->deletions = $decodedDel;
    }
@endphp

<div class="note-header {{ $accent }}">
    <div>
        <div class="note-title">{{ $noteLabel }}</div>
        <div class="note-meta">File: <span class="bold">{{ $policy->fileno }}</span> &nbsp; | &nbsp; Policy: <span class="bold">{{ $policy->policy_no }}</span></div>
    </div>
    <div style="text-align:right;">
        <div class="small-muted">Effective Date</div>
        <div class="bold">{{ \Carbon\Carbon::parse($endorsement->effective_date ?? now())->format('d M Y') }}</div>
    </div>
</div>

<div class="note-body">
    <div class="section">
        <div class="detail-row"><span class="bold">Customer:</span> {{ $policy->customer_name }}</div>
        <div class="detail-row"><span class="bold">Insurer:</span> {{ $policy->insurer_name }}</div>
    </div>

    <div class="section">
        <div class="detail-row"><span class="bold">Total Amount:</span>
            @php $impact = (float)($endorsement->premium_impact ?? 0); @endphp
            <span class="amount {{ $impact >= 0 ? 'positive' : 'negative' }}">KES {{ number_format($impact, 2) }}</span>
        </div>
    </div>

    <div class="section">
        <div class="bold">Description</div>
        <div class="small-muted">{{ $endorsement->description }}</div>
    </div>

    {{-- Additions --}}
    @if(isset($endorsement->additions) && count((array)$endorsement->additions) > 0)
    <div class="section">
        <div class="bold" style="color:#155724;">Additions</div>
        <ul class="items">
            @foreach($endorsement->additions as $idx => $add)
                <li class="item add">
                    @if(is_array($add) || is_object($add))
                        @foreach((array)$add as $k => $v)
                            <div><span class="k">{{ ucfirst(str_replace('_',' ', $k)) }}:</span> <span class="v">{{ $v }}</span></div>
                        @endforeach
                    @else
                        <div><span class="v">{{ $add }}</span></div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Deletions --}}
    @if(isset($endorsement->deletions) && count((array)$endorsement->deletions) > 0)
    <div class="section">
        <div class="bold" style="color:#721c24;">Deletions</div>
        <ul class="items">
            @foreach($endorsement->deletions as $idx => $del)
                <li class="item del">
                    @if(is_array($del) || is_object($del))
                        @foreach((array)$del as $k => $v)
                            <div><span class="k">{{ ucfirst(str_replace('_',' ', $k)) }}:</span> <span class="v">{{ $v }}</span></div>
                        @endforeach
                    @else
                        <div><span class="v">{{ $del }}</span></div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="section">
        <div class="small-muted">Generated On: {{ now()->format('d-m-Y H:i') }}</div>
    </div>
</div>

@endsection
