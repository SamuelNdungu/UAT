@php
    use App\Models\CompanyData;
    $headerCompany = $company ?? CompanyData::first();
@endphp

<div style="width:100%; margin-bottom:8px;">
    <table style="width:100%; border:none; border-collapse:collapse;">
        <tr>
            <!-- Left: Logo -->
            <td style="vertical-align:top; width:40%; padding-right:10px;">
                @if($headerCompany)
                    {{-- Use the already-loaded $headerCompany and ensure as_data=true for PDF --}}
                    @include('partials.company_logo', [
                        'company' => $headerCompany, 
                        'max_width' => 220, 
                        'as_data' => true  // CRITICAL: This must be true for PDF
                    ])
                @else
                    <div style="font-size:16px; font-weight:700; color:#222;">{{ config('app.name', 'Company') }}</div>
                @endif
            </td>

            <!-- Right: Company contact details -->
            <td style="vertical-align:top; text-align:right; width:60%; padding-left:10px;">
                <div style="text-align:right; font-size:12px; color:#222;">
                    @if($headerCompany)
                        <div style="font-weight:700; font-size:16px;">{{ $headerCompany->company_name }}</div>
                        @if($headerCompany->address)
                            <div style="margin-top:6px;">{!! nl2br(e($headerCompany->address)) !!}</div>
                        @endif
                        <div style="margin-top:6px;">
                            @if($headerCompany->phone) <div><strong>Phone:</strong> {{ $headerCompany->phone }}</div> @endif
                            @if($headerCompany->email) <div><strong>Email:</strong> {{ $headerCompany->email }}</div> @endif
                            @if($headerCompany->website) <div><strong>Website:</strong> {{ $headerCompany->website }}</div> @endif
                        </div>
                    @else
                        <div style="font-weight:700; font-size:16px;">{{ config('app.name', 'Company') }}</div>
                    @endif

                    {{-- Optional extra header content --}}
                    @hasSection('pdf_header_extra')
                        <div style="margin-top:8px; font-size:14px;">@yield('pdf_header_extra')</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
    <div style="border-top:1px solid #ccc; margin-top:8px;"></div>
</div>