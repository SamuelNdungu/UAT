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
        'fileno',
        'bus_type',
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
        'pvt',
        'excess',
        'courtesy_car',
        'ppl',
        'road_rescue',
    ];

    // Specify the attributes that should be cast to native types
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'document_description' => 'array',
        'documents' => 'json',
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
        return $this->belongsTo(Insurer::class, 'insurer_id');
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

    // Accessor to get insurer_name
    public function getInsurerNameAttribute()
    {
        return $this->insurer->name ?? 'N/A'; // Adjust 'name' to the actual column name in the Insurer model that contains the insurer's name
    }

    public function renewalsAsOriginal()
    {
        return $this->hasMany(Renewal::class, 'original_policy_id');
    }

    public function renewalsAsRenewed()
    {
        return $this->hasMany(Renewal::class, 'renewed_policy_id');
    }

    public function latestRenewal()
    {
        return $this->hasOne(Renewal::class, 'original_policy_id')
            ->latest('renewal_date');
    }

    public function isRenewed()
    {
        return $this->renewalsAsOriginal()->exists();
    }
}
