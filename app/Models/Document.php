<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id', 'path', 'original_name', 'mime', 'size', 'uploaded_by', 'tag', 'notes'
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}


