<?php

namespace App\Http\Controllers;


use App\Models\Lead;
use Illuminate\Http\Request;

class LeadsController extends Controller
{
    // Display a listing of the leads
    public function index()
    {
        $leads = Lead::all();
        return view('leads.index', compact('leads'));
    }

    // Show the form for creating a new lead
    public function create()
    {
        return view('leads.create');
    }

    // Store a newly created lead in storage
    public function store(Request $request)
    {
        $data = $request->validate([
            'lead_type' => 'required|string',
            'corporate_name' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'mobile' => 'required|string',
            'email' => 'required|email',
            'policy_type' => 'required|string',
            'estimated_premium' => 'required|numeric',
            'follow_up_date' => 'required|date',
            'upload' => 'nullable|json',
            'lead_source' => 'required|string',
            'notes' => 'nullable|string'
        ]);
        if ($request->hasFile('upload')) {
            $uploads = [];
            foreach ($request->file('upload') as $file) {
                $path = $file->store('uploads');
                $uploads[] = $path;
            }
            $data['upload'] = json_encode($uploads);
        }
        
        Lead::create($data);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully');
    }

    // Show the form for editing the specified lead
    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    // Update the specified lead in storage
    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'lead_type' => 'required|string',
            'corporate_name' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'mobile' => 'required|string',
            'email' => 'required|email',
            'policy_type' => 'required|string',
            'estimated_premium' => 'required|numeric',
            'follow_up_date' => 'required|date',
            'upload' => 'nullable|json',
            'lead_source' => 'required|string',
            'notes' => 'nullable|string'
        ]);
        if ($request->hasFile('upload')) {
            $uploads = [];
            foreach ($request->file('upload') as $file) {
                $path = $file->store('uploads');
                $uploads[] = $path;
            }
            $data['upload'] = json_encode($uploads);
        }
        
        $lead->update($data);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully');
    }

    // Remove the specified lead from storage
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully');
    }
}
