<?php

namespace App\Http\Controllers;

use App\Models\PolicyType;
use Illuminate\Http\Request;

class PolicyTypeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $types = PolicyType::when($search, function($query, $search) {
                return $query->where('type_name', 'ILIKE', "%$search%");
            })
            ->orderBy('type_name', 'asc')
            ->get();
        return view('policy_types.index', compact('types', 'search'));
    }

    public function create()
    {
        return view('policy_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:255',
        ]);
        PolicyType::create($request->all());
        return redirect()->route('policy_types.index')->with('success', 'Policy type added successfully.');
    }

    public function show($id)
    {
        $type = PolicyType::findOrFail($id);
        return view('policy_types.show', compact('type'));
    }

    public function edit($id)
    {
        $type = PolicyType::findOrFail($id);
        return view('policy_types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = PolicyType::findOrFail($id);
        $type->update($request->all());
        return redirect()->route('policy_types.index')->with('success', 'Policy type updated successfully.');
    }

    public function destroy($id)
    {
        $type = PolicyType::findOrFail($id);
            if ($type->policies()->exists()) {
                return redirect()->route('policy_types.index')
                    ->with('error', 'This policy type cannot be deleted because it is assigned to one or more active policies.');
            }
            $type->delete();
        return redirect()->route('policy_types.index')->with('success', 'Policy type deleted successfully.');
    }
}
