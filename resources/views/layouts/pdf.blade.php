<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Emely Insurance') }} - PDF</title>
    <style>
        /* Basic PDF-friendly styles */
        body { font-family: 'DejaVu Sans', 'Arial', sans-serif; font-size: 12px; color: #222; }
        .container { width: 100%; padding: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .small { font-size: 0.85em; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 6px 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        @include('layouts.pdf_header')
        @yield('content')
    </div>
</body>
</html>
