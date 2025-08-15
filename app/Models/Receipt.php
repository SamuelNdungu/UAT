<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id', 'receipt_date', 'receipt_number', 
        'allocated_amount', 'remaining_amount', 'notes','user_id',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
