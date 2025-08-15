<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow any user to make this request, implement logic if needed
    }

    public function rules()
    {
        $id = $this->route('customer'); // Get the customer ID from the route

        return [
            'customer_type' => 'required|string',
            'title' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'surname' => 'nullable|string',
            'dob' => 'nullable|date',
            'occupation' => 'nullable|string',
            'corporate_name' => 'nullable|string',
            'business_no' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'designation' => 'nullable|string',
            'industry_class' => 'nullable|string',
            'industry_segment' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'county' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'id_number' => 'nullable|string|unique:customers,id_number,' . $id, // Ensure ID is present
            'kra_pin' => 'nullable|string|unique:customers,kra_pin,' . $id, // Ensure ID is present
            'documents' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048',
            'notes' => 'nullable|string',
            'status' => 'required|in:0,1', // Validate as 0 or 1
        ];
    }
}
