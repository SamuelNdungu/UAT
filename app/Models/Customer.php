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
    ];

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

        // Relationship with Claim
        public function claims()
        {
            return $this->hasMany(Claim::class, 'customer_code', 'customer_code');
        }
}
