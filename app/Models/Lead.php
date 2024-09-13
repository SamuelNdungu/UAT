<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'lead_type', 'corporate_name', 'contact_name', 'first_name', 'last_name',
        'mobile', 'email', 'policy_type', 'estimated_premium', 'follow_up_date',
        'upload', 'lead_source', 'notes'
    ];
}
