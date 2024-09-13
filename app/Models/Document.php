<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 
        'name', 
        'path', 
        'document_type'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}


