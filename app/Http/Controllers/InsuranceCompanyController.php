<?php

namespace App\Http\Controllers;

use App\Models\InsuranceCompany;
use Illuminate\Http\Request;

class InsuranceCompanyController extends Controller
{
    public function index()
    {
        $companies = InsuranceCompany::all();
        return view('insurance_companies.index', compact('companies'));
    }

    public function create()
    {
        return view('insurance_companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
        ]);
        InsuranceCompany::create($request->all());
        return redirect()->route('insurance_companies.index')->with('success', 'Insurance company added successfully.');
    }

    public function show($id)
    {
        $company = InsuranceCompany::findOrFail($id);
        return view('insurance_companies.show', compact('company'));
    }

    public function edit($id)
    {
        $company = InsuranceCompany::findOrFail($id);
        return view('insurance_companies.edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $company = InsuranceCompany::findOrFail($id);
        $company->update($request->all());
        return redirect()->route('insurance_companies.index')->with('success', 'Insurance company updated successfully.');
    }

    public function destroy($id)
    {
        $company = InsuranceCompany::findOrFail($id);
        if ($company->policies()->exists()) {
            return redirect()->route('insurance_companies.index')
                ->with('error', 'Cannot delete insurer with existing policies.');
        }
        $company->delete();
        return redirect()->route('insurance_companies.index')->with('success', 'Insurance company deleted successfully.');
    }
}
