@extends('layouts.appPages')


@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Company Information</h4>
            <a href="{{ route('settings.company-data.edit') }}" class="btn btn-primary btn-sm">Edit</a>
        </div>
        <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        @php
                            $storageLogo = $company->logo_path ?? null;
                            $storageHasLogo = $storageLogo && \Storage::disk('public')->exists($storageLogo);
                            $publicLogo = public_path('img/logo.png');
                            $publicHasLogo = file_exists($publicLogo);
                        @endphp

                        @if($storageHasLogo)
                            <img src="{{ asset('storage/' . $storageLogo) }}" alt="Logo" class="img-fluid mb-2" style="max-height:120px;">
                        @elseif($publicHasLogo)
                            @include('partials.company_logo')
                            @if(!empty($companyLogoUrl))
                                <img src="{{ $companyLogoUrl }}" alt="Logo" class="img-fluid mb-2" style="max-height:120px;">
                            @else
                                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="img-fluid mb-2" style="max-height:120px;">
                            @endif
                        @endif
                    </div>
                    <div class="col-md-9">
                        <dl class="row">
                            <dt class="col-sm-3">Company Name</dt>
                            <dd class="col-sm-9">{{ $company->company_name }}</dd>

                            <dt class="col-sm-3">Email</dt>
                            <dd class="col-sm-9">{{ $company->email ?? '-' }}</dd>

                            <dt class="col-sm-3">Phone</dt>
                            <dd class="col-sm-9">{{ $company->phone ?? '-' }}</dd>

                            <dt class="col-sm-3">Website</dt>
                            <dd class="col-sm-9">{{ $company->website ?? '-' }}</dd>

                            <dt class="col-sm-3">Address</dt>
                            <dd class="col-sm-9">{!! nl2br(e($company->address ?? '-')) !!}</dd>
                        </dl>
                    </div>
                </div>
        </div>
    </div>
</div>

@endsection
