<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id', 'policy_id', 'allocation_amount', 'allocation_date','user_id',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }
}
