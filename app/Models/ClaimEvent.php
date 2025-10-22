<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimEvent extends Model
{
    use HasFactory;

    protected $table = 'claim_events'; // Set the table name to claim_events

    protected $fillable = [
        'claim_id', 'event_date', 'event_type', 'description'
    ];

    // Relationship to Claim
    /**
     * The claim this event belongs to.
     */
    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }

}
