@php
    use App\Models\CompanyData;
    // Ensure company is available to the header
    $headerCompany = $company ?? CompanyData::first();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($headerCompany->company_name ?? config('app.name', 'Company')) }} - PDF</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', 'Arial', sans-serif; 
            font-size: 12px; 
            color: #222; 
            margin: 0;
            padding: 0;
        }

        .content-wrapper {
            padding: 10mm;
        }
    </style>
</head>
<body>
    <div class="a4-container">
        <!-- Header constrained to A4 width -->
        <div class="content-wrapper">
            @include('layouts.pdf_header', ['company' => $headerCompany])
        </div>
        
        <!-- Content area -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>
</body>
</html>