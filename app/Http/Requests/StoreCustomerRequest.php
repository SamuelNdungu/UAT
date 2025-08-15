<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow any user to make this request, implement logic if needed
    }

    public function rules()
    {
        $rules = [
            'customer_type' => 'required|string',
            'kra_pin' => 'required|string|unique:customers,kra_pin',
            'email' => 'required|email',
            'phone' => ['required', 'string', 'regex:/^(\+?254|0)?7\d{8}$/'],
            'city' => 'required|string',
            'address' => 'nullable|string',
            'county' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'dob' => 'nullable|date',
            'occupation' => 'nullable|string',
            'business_no' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'designation' => 'nullable|string',
            'industry_class' => 'nullable|string',
            'industry_segment' => 'nullable|string',
            'documents' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048',
            'notes' => 'nullable|string',
            'status' => 'boolean', // Ensuring it's a boolean
        ];

        // Additional validation for Individual customers
        if ($this->customer_type == 'Individual') {
            $rules['first_name'] = 'required|string';
            $rules['last_name'] = 'required|string';
            $rules['id_number'] = 'required|string|unique:customers,id_number';
        }

        // Additional validation for Corporate customers
        if ($this->customer_type == 'Corporate') {
            $rules['corporate_name'] = 'required|string';
        }

        return $rules;
    }
}
