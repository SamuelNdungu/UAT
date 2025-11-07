<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyData;
use Illuminate\Support\Facades\Redirect;

class CompanyDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Show the single CompanyData record or redirect to edit if none exists
    public function show()
    {
        $company = CompanyData::first();

        if (!$company) {
            return redirect()->route('settings.company-data.edit');
        }

        return view('settings.company_data.show', compact('company'));
    }

    // Display the edit form (create if none)
    public function edit()
    {
        $company = CompanyData::first() ?? new CompanyData();
        return view('settings.company_data.edit', compact('company'));
    }

    // Upsert the singleton record
    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,gif|max:2048',
        ]);

        // Use upsert pattern: update first record or create new with id=1
        $company = CompanyData::first();

        if ($company) {
            $company->update($data);
        } else {
            $company = CompanyData::create($data);
        }

        // Handle logo upload separately
       // In CompanyDataController update method
if ($request->hasFile('logo')) {
    $file = $request->file('logo');
    
    // Generate a clean filename
    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $cleanName = \Illuminate\Support\Str::slug($originalName) . '.' . $file->getClientOriginalExtension();
    
    $path = $file->storeAs('company_logos', $cleanName, 'public');

    // Delete previous logo if exists
    if ($company->logo_path && \Storage::disk('public')->exists($company->logo_path)) {
        \Storage::disk('public')->delete($company->logo_path);
    }

    $company->logo_path = $path;
    $company->save();
}

        return redirect()->route('settings.company-data.show')
            ->with('success', 'Company data saved successfully.');
    }
}
