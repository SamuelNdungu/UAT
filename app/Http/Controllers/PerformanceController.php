<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer; 
use App\Models\PolicyTypes;
use App\Models\Insurer; 
use Illuminate\Support\Facades\Auth;

class PerformanceController extends Controller
{
    public function index(Request $request)
{
    // Initialize query builder for Performance
    $query = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
        ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
        ->join('insurers', 'policies.insurer_id', '=', 'insurers.id');

    // Execute the query and get the results
    $policies = $query->get();

    // Pass policies and metrics to the view
    $policies = Policy::paginate(10);
    return view('Performance.index', compact('policies'));
}
}