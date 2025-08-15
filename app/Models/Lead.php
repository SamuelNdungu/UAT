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
}
