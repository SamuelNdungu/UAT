@php
    // Determine a canonical company logo URL. Preference order:
    // 1. CompanyData.logo_path on the 'public' disk (uploaded via settings)
    // 2. A handful of common public image paths under public/
    // If none found, $companyLogoUrl will be null and callers can fallback to asset('img/logo.png')
    $companyLogoUrl = null;
    try {
        if (class_exists('\App\\Models\\CompanyData')) {
            $company = \App\Models\CompanyData::first();
            $storageLogo = $company->logo_path ?? null;
            if ($storageLogo && \Storage::disk('public')->exists($storageLogo)) {
                $companyLogoUrl = asset('storage/' . ltrim($storageLogo, '/'));
            }
        }
    } catch (\Throwable $e) {
        // ignore and fall back to file checks
        $companyLogoUrl = null;
    }

    if (! $companyLogoUrl) {
        $possibleLogos = [
            'storage/company/logo.png',
            'storage/company/logo.jpg',
            'assets/img/company-logo.png',
            'assets/img/company-logo.jpg',
            'assets/img/logo.png',
            'assets/img/logo.jpg',
            'img/logo.png',
            'img/company-logo.png',
        ];
        foreach ($possibleLogos as $p) {
            if (file_exists(public_path($p))) {
                $companyLogoUrl = asset($p);
                break;
            }
        }
    }
@endphp
