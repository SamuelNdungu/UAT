@php
    use App\Models\CompanyData;
    $company = CompanyData::first();
    $storageLogo = $company->logo_path ?? null;
    $storageHasLogo = $storageLogo && \Storage::disk('public')->exists($storageLogo);
    $publicLogoPath = public_path('img/logo.png');
    $publicHasLogo = file_exists($publicLogoPath);
@endphp

<div style="width:100%; margin-bottom:8px;">
    <table style="width:100%; border:none; border-collapse:collapse;">
        <tr>
            <!-- Left: Logo -->
            <td style="vertical-align:top; width:40%; padding-right:10px;">
                <div class="logo" aria-hidden="true" style="text-align:left;">
                    @if($storageHasLogo)
                        @php $full = storage_path('app/public/' . $storageLogo); @endphp
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($full)) }}" alt="Company logo" style="max-width:220px; height:auto; display:block;">
                    @elseif($publicHasLogo)
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($publicLogoPath)) }}" alt="Company logo" style="max-width:220px; height:auto; display:block;">
                    @else
                        @if($company && $company->company_name)
                            <div style="font-size:20px; font-weight:700; color:#222;">{{ $company->company_name }}</div>
                        @else
                            <div style="font-size:20px; font-weight:700; color:#222;">{{ config('app.name', 'Company') }}</div>
                        @endif
                    @endif
                </div>
            </td>

            <!-- Right: Company contact details -->
            <td style="vertical-align:top; text-align:right; width:60%; padding-left:10px;">
                <div style="text-align:right; font-size:12px; color:#222;">
                    @if($company)
                        <div style="font-weight:700; font-size:16px;">{{ $company->company_name }}</div>
                        @if($company->address)
                            <div style="margin-top:6px;">{!! nl2br(e($company->address)) !!}</div>
                        @endif
                        <div style="margin-top:6px;">
                            @if($company->phone) <div><strong>Phone:</strong> {{ $company->phone }}</div> @endif
                            @if($company->email) <div><strong>Email:</strong> {{ $company->email }}</div> @endif
                            @if($company->website) <div><strong>Website:</strong> {{ $company->website }}</div> @endif
                        </div>
                    @else
                        <div style="font-weight:700; font-size:16px;">{{ config('app.name', 'Company') }}</div>
                    @endif

                    {{-- Optional extra header content (e.g., document title, invoice no) --}}
                    @hasSection('pdf_header_extra')
                        <div style="margin-top:8px; font-size:14px;">@yield('pdf_header_extra')</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
    <div style="border-top:1px solid #ccc; margin-top:8px;"></div>
</div>
