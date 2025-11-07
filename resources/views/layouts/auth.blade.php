<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // Prefer company name from CompanyData for page title and alt text
        $companyName = null;
        try {
            if (class_exists('\App\\Models\\CompanyData')) {
                $__c = \App\Models\CompanyData::first();
                if ($__c && !empty($__c->company_name)) {
                    $companyName = $__c->company_name;
                }
            }
        } catch (\Throwable $__e) {
            $companyName = null;
        }
    @endphp
    <title>{{ $companyName ?? config('app.name', 'Emely Insurance') }}</title>

    <!-- Tailwind & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap"
        rel="stylesheet" />

    <script>
       tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#00529B",
                    "background-light": "#F2F4F7",
                    "background-dark": "#101c22",
                },
                fontFamily: {
                    "display": ["Inter", "sans-serif"]
                },
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
            },
        },
    }
    </script>
    <style>
    .material-symbols-outlined {
        font-variation-settings:
            'FILL'0,
            'wght'400,
            'GRAD'0,
            'opsz'24
    }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
            @yield('content')
</body>
</html>
