<?php
namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = VehicleType::orderBy('make')->get();
        return view('vehicle_types.index', compact('vehicleTypes'));
    }

    public function create()
    {
        return view('vehicle_types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ]);
        VehicleType::create([
            'make' => $request->make,
            'model' => $request->model,
            'user_id' => auth()->id(),
        ]);
        return redirect()->route('vehicle_types.index')->with('success', 'Vehicle type added successfully.');
    }

    public function show($id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        return view('vehicle_types.show', compact('vehicleType'));
    }

    public function edit($id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        return view('vehicle_types.edit', compact('vehicleType'));
    }

    public function update(Request $request, $id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ]);
        $vehicleType->update([
            'make' => $request->make,
            'model' => $request->model,
        ]);
        return redirect()->route('vehicle_types.index')->with('success', 'Vehicle type updated successfully.');
    }

    public function destroy($id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        // Prevent deletion if policies exist for this vehicle type
        if ($vehicleType->policies()->exists()) {
            return redirect()->route('vehicle_types.index')
                ->with('error', 'This vehicle type cannot be deleted because it is assigned to one or more policies.');
        }
        $vehicleType->delete();
        return redirect()->route('vehicle_types.index')->with('success', 'Vehicle type deleted successfully.');
    }
}
