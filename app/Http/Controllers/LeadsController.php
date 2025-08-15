<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LeadsController extends Controller
{

       // Method to generate a unique customer code
private function generateUniqueCustomerCode()
{
    // Get the maximum numeric part of the existing customer codes
    $maxNumber = Customer::max(DB::raw('CAST(SUBSTRING(customer_code, 5) AS INTEGER)'));

    // Increment to generate thse new customer code
    $newNumber = $maxNumber ? $maxNumber + 1 : 100; // Start at 100 if no customer codes exist

    // Format the new customer code
    return sprintf('CUS-%05d', $newNumber);
}
    public function index(Request $request)
    {
        $query = Lead::query();

        // Apply filters if present
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'active':
                    $query->where('deal_status', '!=', 'Lost');
                    break;
                case 'converted':
                    $query->where('deal_status', 'Won');
                    break;
                case 'high_probability':
                    $query->where('probability', '>', 70);
                    break;
                case 'follow_up':
                    $query->where('next_action', 'follow up');
                    break;
                case 'lost':
                    $query->where('deal_status', 'Lost');
                    break;
            }
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        // Calculate metrics
        $metrics = [
            'totalLeads' => Lead::count(),
            'activeLeads' => Lead::where('deal_status', '!=', 'Lost')->count(),
            'convertedLeads' => Lead::where('deal_status', 'Won')->count(),
            'highProbabilityLeads' => Lead::where('probability', '>', 70)->count(),
            'followUpLeads' => Lead::where('next_action', 'follow up')->count(),
            'lostLeads' => Lead::where('deal_status', 'Lost')->count()
        ];

        if ($request->has('export')) {
            if ($request->export === 'pdf') {
                $pdf = PDF::loadView('leads.export_pdf', compact('leads'));
                return $pdf->download('leads.pdf');
            } elseif ($request->export === 'excel') {
                return Excel::download(new LeadsExport, 'leads.xlsx');
            }
        }

        return view('leads.index', compact('leads', 'metrics'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_type' => 'required|string|in:Individual,Corporate',
            'corporate_name' => 'required_if:lead_type,Corporate|string|nullable',
            'first_name' => 'required_if:lead_type,Individual|string|nullable',
            'last_name' => 'required_if:lead_type,Individual|string|nullable',
            'mobile' => 'required',
            'email' => 'required|email',
            'policy_type' => 'required',
            'lead_source' => 'required',
            'follow_up_date' => 'required|date',
            'deal_size' => 'required|numeric',
            'probability' => 'required|numeric|min:0|max:100',
            'deal_stage' => 'required',
            'deal_status' => 'required',
            'date_initiated' => 'required|date',
            'closing_date' => 'required|date',
            'next_action' => 'required',
            'notes' => 'nullable',
            'upload.*' => 'nullable|file|max:10240'
        ]);

        if ($validator->fails()) {
            Log::warning('Lead creation validation failed', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $customerCode = $this->generateUniqueCustomerCode();
        // Create the lead
        $lead = new Lead($request->all());



        // Create the customer record
        $customerData = [
            'customer_code' => $customerCode,
            'customer_type' => $request->lead_type,
            'first_name' => $request->lead_type === 'Individual' ? $request->first_name : null,
            'last_name' => $request->lead_type === 'Individual' ? $request->last_name : null,
            'corporate_name' => $request->lead_type === 'Corporate' ? $request->corporate_name : null,
            'contact_person' => $request->lead_type === 'Corporate' ? $request->contact_name : null,
            'email' => $request->email,
            'phone' => $request->mobile,
            'status' => 'Lead',
            'user_id' => auth()->id()
        ];

        $customer = Customer::create($customerData);
        
        if ($request->hasFile('upload')) {
            $files = [];
            foreach ($request->file('upload') as $file) {
                $path = $file->store('leads', 'public');
                $files[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path
                ];
            }
            $lead->upload = json_encode($files);
        }

        $lead->save();

        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validator = Validator::make($request->all(), [
            
            'corporate_name' => 'required_if:lead_type,Corporate',
            'first_name' => 'required_if:lead_type,Individual',
            'last_name' => 'required_if:lead_type,Individual',
            'mobile' => 'required',
            'email' => 'required|email',
            'policy_type' => 'required', 
            'lead_source' => 'required',
            'follow_up_date' => 'required|date',
            'deal_size' => 'required|numeric',
            'probability' => 'required|numeric|min:0|max:100',
            'deal_stage' => 'required',
            'deal_status' => 'required',
            'date_initiated' => 'required|date',
            'closing_date' => 'required|date',
            'next_action' => 'required',
            'notes' => 'nullable',
            'upload.*' => 'nullable|file|max:10240'
        ]);

        if ($validator->fails()) {
            Log::warning('Lead creation validation failed', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $lead->fill($request->except('upload'));

        if ($request->hasFile('upload')) {
            $files = json_decode($lead->upload ?? '[]', true);
            foreach ($request->file('upload') as $file) {
                $path = $file->store('leads', 'public');
                $files[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path
                ];
            }
            $lead->upload = json_encode($files);
        }

        $lead->save();

        return redirect()->route('leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }
}