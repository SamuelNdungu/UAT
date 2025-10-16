<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    // Define the attributes that are mass assignable
    protected $fillable = [
        'customer_code',
        'customer_type',
        'title',
        'first_name',
        'last_name',
        'surname',
        'dob',
        'occupation',
        'corporate_name',
        'business_no',
        'contact_person',
        'designation',
        'industry_class',
        'industry_segment',
        'email',
        'phone',
        'address',
        'city',
        'county',
        'postal_code',
        'country',
        'id_number',
        'kra_pin',
        'documents',
        'notes',
        'status',
        'user_id',
    ];
    protected $casts = [
        'status' => 'string',
    ];

    // Relationship with Document
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // Relationship with Claim - linked with customer_code
    public function claims()
    {
        return $this->hasMany(Claim::class, 'customer_code', 'customer_code');
    }

    // Relationship with Policy - linked with customer_code
    public function policies()
    {
        return $this->hasMany(Policy::class, 'customer_code', 'customer_code'); // Specify keys correctly
    }

    // Accessor to get formatted customer name
    public function getCustomerNameAttribute()
{
    if ($this->customer_type == 'Individual') {
        return $this->first_name . ' ' . $this->last_name;
    } elseif ($this->customer_type == 'Corporate') {
        return $this->corporate_name;
    }

    return 'Unknown'; // Fallback in case customer type is neither
}

}
