<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Policy extends Model
{
    use HasFactory;

    // Define the attributes that are mass assignable
    protected $fillable = [
        'customer_code',
        'customer_name',
        'policy_type_id',
        'coverage',
        'start_date',
        'days',
        'end_date',
        'insurer_id',
        'policy_no',
        'insured',
        'reg_no',
        'make',
        'model',
        'yom',
        'cc',
        'body_type',
        'chassisno',
        'engine_no',
        'description',
        'sum_insured',
        'rate',
        'premium',
        'c_rate',
        'commission',
        'wht',
        's_duty',
        't_levy',
        'pcf_levy',
        'policy_charge',
        'aa_charges',
        'other_charges',
        'gross_premium',
        'net_premium',
        'cover_details',
        'notes',
        'document_description',
        'documents',
        'user_id',
    ];

    // Specify the attributes that should be cast to native types
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships

    // A policy belongs to a customer.
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }

    // A policy belongs to an insurer.
    public function insurer()
    {
        return $this->belongsTo(Insurer::class);
    }

    // A policy belongs to a policy type.
    public function policyType()
    {
        return $this->belongsTo(PolicyType::class, 'policy_type_id');
    }

    // A policy can have many claims associated with it.
    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    // Accessor to get policy_type_name
    public function getPolicyTypeNameAttribute()
    {
        return $this->policyType->type_name ?? 'N/A';
    }
}
