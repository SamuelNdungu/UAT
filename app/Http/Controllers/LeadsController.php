<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use App\Enums\CustomerStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LeadsController extends Controller
{
    // Method to generate a unique customer code
    private function generateUniqueCustomerCode()
    {
        $prefix = 'CUS-';
        $pad = 5;
        $max = 0;

        // Scan customer_code values that start with the prefix in chunks to avoid heavy memory usage
        DB::table('customers')
            ->select('customer_code')
            ->where('customer_code', 'like', $prefix . '%')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$max, $prefix) {
                foreach ($rows as $row) {
                    $code = (string) ($row->customer_code ?? '');
                    if ($code === '') {
                        continue;
                    }

                    // remove prefix if present
                    if (Str::startsWith($code, $prefix)) {
                        $suffix = substr($code, strlen($prefix));
                    } else {
                        $suffix = $code;
                    }

                    // Extract the last contiguous group of digits (ignore malformed parts)
                    if (preg_match('/(\d+)(?!.*\d)/', $suffix, $m)) {
                        $num = (int)$m[1];
                        if ($num > $max) {
                            $max = $num;
                        }
                        continue;
                    }

                    // If no contiguous group at end, try to find any digits
                    if (preg_match_all('/\d+/', $suffix, $matches)) {
                        $lastMatch = end($matches[0]);
                        $num = (int)$lastMatch;
                        if ($num > $max) {
                            $max = $num;
                        }
                    }
                }
            });

        if ($max > 0) {
            $next = $max + 1;
            return sprintf($prefix . '%0' . $pad . 'd', $next);
        }

        // Fallback when no numeric codes found: use prefix + random token
        return $prefix . strtoupper(Str::random(6));
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
        // Create the lead but only set attributes for columns that actually exist in the DB.
        // This avoids QueryException when migrations/schema differ between environments.
        $lead = new Lead();

        // Conditionally map request fields to actual DB columns
        if (Schema::hasColumn('leads', 'lead_type')) {
            $lead->lead_type = $request->lead_type;
        }

        if (Schema::hasColumn('leads', 'corporate_name')) {
            $lead->corporate_name = $request->corporate_name;
        } elseif (Schema::hasColumn('leads', 'company_name')) {
            // older schema used company_name
            $lead->company_name = $request->corporate_name;
        }

        if (Schema::hasColumn('leads', 'contact_name')) {
            $lead->contact_name = $request->contact_name;
        }

        if (Schema::hasColumn('leads', 'first_name')) {
            $lead->first_name = $request->first_name;
        }

        if (Schema::hasColumn('leads', 'last_name')) {
            $lead->last_name = $request->last_name;
        }

        // phone/email column may be named differently across migrations
        if (Schema::hasColumn('leads', 'mobile')) {
            $lead->mobile = $request->mobile;
        }

        if (Schema::hasColumn('leads', 'phone')) {
            $lead->phone = $request->mobile;
        }

        if (Schema::hasColumn('leads', 'email')) {
            $lead->email = $request->email;
        } elseif (Schema::hasColumn('leads', 'email_address')) {
            $lead->email_address = $request->email;
        }

        if (Schema::hasColumn('leads', 'policy_type')) {
            $lead->policy_type = $request->policy_type;
        }

        if (Schema::hasColumn('leads', 'estimated_premium')) {
            $lead->estimated_premium = $request->estimated_premium ?? null;
        }

        if (Schema::hasColumn('leads', 'follow_up_date')) {
            $lead->follow_up_date = $request->follow_up_date;
        }

        if (Schema::hasColumn('leads', 'upload')) {
            // We'll set upload later after storing files
        }

        if (Schema::hasColumn('leads', 'lead_source')) {
            $lead->lead_source = $request->lead_source;
        }

        if (Schema::hasColumn('leads', 'notes')) {
            $lead->notes = $request->notes;
        }

        if (Schema::hasColumn('leads', 'deal_size')) {
            $lead->deal_size = $request->deal_size;
        }

        if (Schema::hasColumn('leads', 'probability')) {
            $lead->probability = $request->probability;
        }

        if (Schema::hasColumn('leads', 'weighted_revenue_forecast')) {
            $lead->weighted_revenue_forecast = $request->weighted_revenue_forecast;
        }

        if (Schema::hasColumn('leads', 'deal_stage')) {
            $lead->deal_stage = $request->deal_stage;
        }

        if (Schema::hasColumn('leads', 'deal_status')) {
            $lead->deal_status = $request->deal_status;
        }

        if (Schema::hasColumn('leads', 'date_initiated')) {
            $lead->date_initiated = $request->date_initiated;
        }

        if (Schema::hasColumn('leads', 'closing_date')) {
            $lead->closing_date = $request->closing_date;
        }

        if (Schema::hasColumn('leads', 'next_action')) {
            $lead->next_action = $request->next_action;
        }



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
            // The customers table stores a boolean/integer status (active/inactive).
            // Use `true` to mark a newly created customer as active by default.
            // Use enum to indicate active customer
            'status' => CustomerStatus::ACTIVE,
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