@php
    // --- Setup Variables ---
    $asData = $as_data ?? false;
    $maxWidth = $max_width ?? 220;
    $logoHtml = null;

    // --- 1. Load Company Data ---
    $company = $company ?? \App\Models\CompanyData::first();
    
    if (!$company) {
        // No company found - use text fallback
        $logoHtml = '<div style="font-size:16px; font-weight:700; color:#222;">' . config('app.name', 'Company') . '</div>';
    } else {
        $storageLogoPath = $company->logo_path;
        
        // --- 2. Check for logo files ---
        $storageLogoExists = false;
        $publicLogoExists = file_exists(public_path('img/logo.png'));
        
        if ($storageLogoPath) {
            $normalizedPath = ltrim($storageLogoPath, '/\\');
            $storageLogoExists = \Storage::disk('public')->exists($normalizedPath);
        }

        // --- 3. Rendering Logic ---
        if ($storageLogoExists && $asData) {
            // PDF MODE: Use storage logo as base64
            try {
                $fileContents = \Storage::disk('public')->get($normalizedPath);
                $mimeType = \Storage::disk('public')->mimeType($normalizedPath);
                
                // Validate mime type
                if (empty($mimeType) || $mimeType === 'application/octet-stream') {
                    $extension = pathinfo($normalizedPath, PATHINFO_EXTENSION);
                    $mimeType = $extension === 'svg' ? 'image/svg+xml' : 'image/png';
                }
                
                $base64 = base64_encode($fileContents);
                $logoHtml = '<img src="data:' . $mimeType . ';base64,' . $base64 . '" alt="' . ($company->company_name ?? 'Company Logo') . '" style="max-width:' . $maxWidth . 'px; height:auto; display:block;">';
            } catch (Exception $e) {
                // Fall through to public logo or text
                $storageLogoExists = false;
            }
        } elseif ($storageLogoExists && !$asData) {
            // WEB MODE: Use storage URL
            $logoUrl = \Storage::url($normalizedPath);
            $logoHtml = '<img src="' . $logoUrl . '" alt="' . ($company->company_name ?? 'Company Logo') . '" style="max-width:' . $maxWidth . 'px; height:auto; display:block;">';
        } elseif ($publicLogoExists && $asData) {
            // PDF MODE: Use public logo as base64
            $publicLogoPath = public_path('img/logo.png');
            $imgData = file_get_contents($publicLogoPath);
            $base64 = base64_encode($imgData);
            $logoHtml = '<img src="data:image/png;base64,' . $base64 . '" alt="' . ($company->company_name ?? 'Company Logo') . '" style="max-width:' . $maxWidth . 'px; height:auto; display:block;">';
        } elseif ($publicLogoExists && !$asData) {
            // WEB MODE: Use public asset
            $logoHtml = '<img src="' . asset('img/logo.png') . '" alt="' . ($company->company_name ?? 'Company Logo') . '" style="max-width:' . $maxWidth . 'px; height:auto; display:block;">';
        } else {
            // Text fallback
            $logoHtml = '<div style="font-size:16px; font-weight:700; color:#222;">' . ($company->company_name ?? config('app.name', 'Company')) . '</div>';
        }
    }
@endphp

<div style="text-align:{{ $align ?? 'left' }};">
    {!! $logoHtml !!}
</div>