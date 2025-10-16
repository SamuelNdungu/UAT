<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

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
        'status',
        'risk_details',
        'renewal_count',
         'paid_amount', 
        'balance', 
    ];

    // Specify the attributes that should be cast to native types
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'document_description' => 'array',
        'documents' => 'array',
        'risk_details' => 'array',
        // Add numeric casts for financial fields
        'excess' => 'float',
        'courtesy_car' => 'float',
        'ppl' => 'float',
        'road_rescue' => 'float',
        'other_charges' => 'float',
        'gross_premium' => 'float',
        'net_premium' => 'float',
        'sum_insured' => 'float',
        'rate' => 'float',
        'premium' => 'float',
        'c_rate' => 'float',
        'commission' => 'float',
        'wht' => 'float',
        's_duty' => 'float',
        't_levy' => 'float',
        'pcf_levy' => 'float',
        'policy_charge' => 'float',
        'pvt' => 'float',
        'aa_charges' => 'float',
        'paid_amount'=> 'float', 
        'balance'=> 'float',  
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

    // Relationship to renewals where this policy is the original
    public function renewalsAsOriginal()
    {
        return $this->hasMany(\App\Models\Renewal::class, 'original_policy_id');
    }

    // Relationship where this policy is the renewed child
    public function renewalsAsRenewed()
    {
        return $this->hasMany(\App\Models\Renewal::class, 'renewed_policy_id');
    }

    // Convenience: get direct parent policy (if this policy is a renewal)
    public function originalPolicy()
    {
        return $this->belongsTo(\App\Models\Policy::class, 'original_policy_id');
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

    public function latestRenewal()
    {
        return $this->hasOne(Renewal::class, 'original_policy_id')
            ->latest('renewal_date');
    }

    public function isRenewed()
    {
        return $this->renewalsAsOriginal()->exists();
    }

    // Increase renewal count (safe: only if column exists)
    public function incrementRenewalCount()
    {
        $policiesTable = $this->getTable();

        // Only attempt to update if the column exists to avoid SQL errors
        if (! Schema::hasColumn($policiesTable, 'renewal_count')) {
            return;
        }

        $this->renewal_count = ($this->renewal_count ?? 0) + 1;

        // Use saveQuietly to avoid firing events if not needed
        $this->saveQuietly();
    }
}