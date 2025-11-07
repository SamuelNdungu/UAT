<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'documentable_id',      // Add this
        'documentable_type',    // Add this  
        'description',          // Add this
        'path', 
        'original_name', 
        'mime', 
        'size', 
        'uploaded_by', 
        'tag', 
        'notes'
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }

    /**
     * Get the parent documentable model (Customer, Claim, Policy, etc.)
     */
    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}