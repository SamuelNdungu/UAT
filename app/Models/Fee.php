<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fee extends Model
{
    protected $fillable = [
         
        'customer_code',
        'amount',
        'description',
        'date',
        'invoice_number',
        'status',
        'due_date',
        'created_by',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }
}