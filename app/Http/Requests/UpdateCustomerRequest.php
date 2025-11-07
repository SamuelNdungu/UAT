<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Keep true, or implement your Authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Get the ID of the customer being updated from the route parameters.
        $customer_id = $this->route('customer'); 

        // Determine the customer type from the request input
        $customerType = $this->input('customer_type');
        
        // Define the base rules
        $rules = [
            'customer_type' => ['required', 'in:Individual,Corporate'],

            // =========================================================================
            // CORE REQUIRED/FORMAT FIELDS (MANDATORY FOR ALL)
            // =========================================================================
            
            // Email: Required, valid format, unique (ignoring current customer)
            'email'         => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer_id)],
            
            // Phone: Required, simple length checks
            'phone'         => ['required', 'string', 'min:9', 'max:15'],

            // KRA PIN: NOW REQUIRED FOR ALL CUSTOMER TYPES
            // Regex enforces the KRA PIN format: Starts with a letter, followed by 9 digits, and ends with a letter or digit.
            'kra_pin'       => [ 
                'required', 
                'string', 
                'max:11', 
                'regex:/^[A-Z]\d{9}[A-Z0-9]$/i', // Pattern must be correct
                Rule::unique('customers', 'kra_pin')->ignore($customer_id)
            ],

            // ID Number: Unique (ignoring current customer)
            'id_number'     => ['nullable', 'string', Rule::unique('customers', 'id_number')->ignore($customer_id)],

            // Status: Required, and must be one of the expected values.
            'status'        => ['sometimes', 'in:0,1'], 

            // =========================================================================
            // STANDARD FIELDS
            // =========================================================================
            'title'             => ['nullable', 'string', 'max:10'],
            'surname'           => ['nullable', 'string', 'max:100'],
            'dob'               => ['nullable', 'date'],
            'occupation'        => ['nullable', 'string', 'max:255'],
            'business_no'       => ['nullable', 'string', 'max:50'],
            'contact_person'    => ['nullable', 'string', 'max:255'],
            'designation'       => ['nullable', 'string', 'max:100'],
            'industry_class'    => ['nullable', 'string', 'max:100'],
            'industry_segment'  => ['nullable', 'string', 'max:100'],
            'address'           => ['nullable', 'string', 'max:255'],
            'city'              => ['nullable', 'string', 'max:100'],
            'county'            => ['nullable', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'string', 'max:20'],
            'country'           => ['nullable', 'string', 'max:100'],
            'documents.*'       => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,jpg,jpeg,png', 'max:2048'],
            'notes'             => ['nullable', 'string'],
            'agent_id'          => ['nullable', 'exists:agents,id'],
        ];

        // =========================================================================
        // CONDITIONAL FIELDS (Individual vs Corporate)
        // =========================================================================
        if ($customerType === 'Individual') {
            // First and Last Name are required for Individual customers.
            $rules['first_name']     = ['required', 'string', 'max:100'];
            $rules['last_name']      = ['required', 'string', 'max:100'];
            
            // Corporate fields are optional/nullable
            $rules['corporate_name'] = ['nullable', 'string', 'max:255'];
            
        } elseif ($customerType === 'Corporate') {
            // Company Name is required for Corporate customers.
            $rules['corporate_name'] = ['required', 'string', 'max:255'];
            
            // Individual fields are optional/nullable
            $rules['first_name']     = ['nullable', 'string', 'max:100'];
            $rules['last_name']      = ['nullable', 'string', 'max:100'];
        }
        
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom messages for conditional required fields
            'first_name.required'     => 'First Name is required for Individual customers.',
            'last_name.required'      => 'Last Name is required for Individual customers.',
            'corporate_name.required' => 'Company Name is required for Corporate customers.',
            
            // KRA PIN messages (Updated to reflect mandatory status)
            'kra_pin.required'        => 'KRA PIN is mandatory for all customers.',
            'kra_pin.regex'           => 'The KRA PIN format is invalid. It must follow the structure: 1 letter, 9 digits, and end with a letter or digit (e.g., P051365947X).',
            'kra_pin.unique'          => 'This KRA PIN is already registered to another customer.',
            
            // Standard messages
            'email.required'          => 'The email address is required.',
            'email.email'             => 'Please enter a valid email address format.',
            'email.unique'            => 'This email address is already registered.',
            'phone.required'          => 'A contact phone number is required.',
        ];
    }
}