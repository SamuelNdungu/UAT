<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Lead extends Model
{
    protected $fillable = [
        'lead_type', 'corporate_name', 'contact_name', 'first_name', 'last_name', 
        'mobile', 'email', 'policy_type', 'estimated_premium', 'follow_up_date', 
        'upload', 'lead_source', 'notes', 'deal_size', 'probability', 
        'weighted_revenue_forecast', 'deal_stage', 'deal_status', 'date_initiated', 
        'closing_date', 'next_action'
    ];

    protected $casts = [
        'date_initiated' => 'date',
        'closing_date' => 'date',
        'follow_up_date' => 'date'
    ];
    /**
     * Normalize access to various historical column names so views can
     * read a stable attribute regardless of which migration created the table.
     */
    public function getLeadTypeAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        // older schema used company_name / company -> treat as Corporate
        if (!empty($this->attributes['lead_type'])) {
            return $this->attributes['lead_type'];
        }

        if (!empty($this->attributes['company_name']) || !empty($this->attributes['corporate_name'])) {
            return 'Corporate';
        }

        if (!empty($this->attributes['first_name']) || !empty($this->attributes['last_name'])) {
            return 'Individual';
        }

        return null;
    }

    public function getCorporateNameAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        return $this->attributes['company_name'] ?? null;
    }

    public function getEmailAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        return $this->attributes['email_address'] ?? null;
    }

    public function getMobileAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        return $this->attributes['phone'] ?? null;
    }

    public function getFirstNameAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        // Try to derive from contact_name if present (naive split)
        if (!empty($this->attributes['contact_name'])) {
            $parts = preg_split('/\s+/', trim($this->attributes['contact_name']));
            return $parts[0] ?? null;
        }

        return null;
    }

    public function getLastNameAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        if (!empty($this->attributes['contact_name'])) {
            $parts = preg_split('/\s+/', trim($this->attributes['contact_name']));
            array_shift($parts);
            return $parts ? implode(' ', $parts) : null;
        }

        return null;
    }

}

