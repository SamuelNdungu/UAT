<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileno', 
        'claim_number',
        'customer_code',
        'policy_id',
        'reported_date',
        'type_of_loss',
        'loss_details',
        'loss_date',
        'followup_date',
        'claimant_name',
        'amount_claimed',
        'amount_paid',
        'status',
        'upload_file',
    ];
    protected $dates = [
        'reported_date',
        'loss_date',
        'followup_date',
        // Add any other date fields here
    ];

    // Relationships
    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }

    public function events()
    {
        return $this->hasMany(ClaimEvent::class, 'claim_id');
    }

    
    public function customer()
{
    return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
}

 

}
