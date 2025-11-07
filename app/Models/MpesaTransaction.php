<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    protected $table = 'mpesa_transactions';

    protected $fillable = [
        'provider',
        'transaction_code',
        'payment_id',
        'receipt_id',
        'amount',
        'phone_number',
        'status',
        'raw_payload',
        'processed_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(\App\Models\Payment::class);
    }

    public function receipt()
    {
        return $this->belongsTo(\App\Models\Receipt::class);
    }
}
