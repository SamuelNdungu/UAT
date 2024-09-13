<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code', 'customer_name', 
        'policy_type_id', 'coverage', 'start_date', 'days','end_date', 'insurer_id', 'policy_no', 'insured', 
        'reg_no','make','model',  'yom', 'cc', 'body_type','chassisno', 'engine_no', 'description', 
        'sum_insured', 'rate', 'premium', 'c_rate', 'commission', 'wht', 's_duty', 't_levy', 'pcf_levy', 'policy_charge', 
        'aa_charges', 'other_charges', 'gross_premium', 'net_premium', 
         'cover_details', 'notes', 'document_description','documents'
    ];
    

    protected $casts = [
        'start_date' => 'date', 
        'end_date' => 'date',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function insurer()
    {
        return $this->belongsTo(Insurer::class);
    }

    public function policyType()
    {
        return $this->belongsTo(PolicyType::class);
    }

    public function policy_type()
    {
        return $this->belongsTo(PolicyType::class, 'policy_type_id');
    }
        // Define the relationship with the Claim model
        public function claims()
        {
            return $this->hasMany(Claim::class);
        }
}
