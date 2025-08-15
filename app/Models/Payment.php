<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code', 'payment_date', 'payment_amount', 
        'payment_method', 'payment_reference', 'payment_status', 'notes','user_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code','customer_code');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function allocations()
    {
        return $this->hasMany(Allocation::class);
    }
}
