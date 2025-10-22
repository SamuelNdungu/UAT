<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    // ...existing code...

    public function store(Request $request)
    {
        $policy = Policy::findOrFail($request->input('policy_id'));
        if ($policy->isCancelled()) {
            return redirect()->back()->with('error', 'Cannot receipt a canceled policy.');
        }
        // ...existing code...
    }

    // ...existing code...
}