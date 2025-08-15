<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; 

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Customer::all(); // Retrieves all customers from the database
    }

    public function headings(): array
    {
        return [
            'Customer Type',            
            'Customer Code',
            'Title',
            'Name',
            'Date of Birth',
            'Occupation',
            'Business No',
            'Contact Person',
            'Designation',
            'Industry Class',
            'Industry Segment',
            'Email',
            'Phone',
            'Address',
            'City',
            'County',
            'Postal Code',
            'Country',
            'ID Number',
            'KRA PIN',
            'Documents',
            'Notes',
            'Created At',
            'Updated At',
            'Status',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->customer_type,
            $customer->customer_code,
            $customer->title,
            $customer->customer_name,
            $customer->dob,
            $customer->occupation,
            $customer->business_no,
            $customer->contact_person,
            $customer->designation,
            $customer->industry_class,
            $customer->industry_segment,
            $customer->email,
            $customer->phone,
            $customer->address,
            $customer->city,
            $customer->county,
            $customer->postal_code,
            $customer->country,
            $customer->id_number,
            $customer->kra_pin,
            $customer->documents,
            $customer->notes,
            $customer->created_at,
            $customer->updated_at,
            $customer->status ? 'Active' : 'Inactive', // Format the boolean status
           
        ];
    }
}
