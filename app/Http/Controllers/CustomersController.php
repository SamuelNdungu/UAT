<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomersController extends Controller
{
	// ...existing code...

	public function update(Request $request, $id)
	{
		$customer = Customer::findOrFail($id);

		// Validate inputs (add other rules as needed)
		$data = $request->validate([
			'first_name' => 'nullable|string|max:255',
			'last_name' => 'nullable|string|max:255',
			'email' => 'nullable|email|max:255',
			'phone' => 'nullable|string|max:50',
			// ...other validation rules...
			'status' => 'required', // ensure status is present
		]);

		// Map incoming status values to stored format if needed
		$incoming = (string) $request->input('status');
		$incomingLower = strtolower($incoming);

		if (in_array($incomingLower, ['1','true','yes','active','activated'], true)) {
			$storedStatus = '1';
		} elseif (in_array($incomingLower, ['0','false','no','inactive','deactivated'], true)) {
			$storedStatus = '0';
		} elseif ($incoming === 'Blacklisted' || $incomingLower === 'blacklisted') {
			$storedStatus = 'Blacklisted';
		} else {
			$storedStatus = $incoming;
		}

		// Remove status from $data to avoid double handling if you prefer explicit set
		unset($data['status']);

		// Fill other fields
		$customer->fill($data);

		// Set status explicitly and save
		$customer->status = $storedStatus;
		$customer->save();

		return redirect()->route('customers.edit', $customer->id)
			->with('success', 'Customer updated successfully.');
	}

	// ...existing code...
}