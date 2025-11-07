<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'kra_pin' => 'nullable|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:1',
            'status' => 'nullable|string|max:50',
        ];
    }
}
