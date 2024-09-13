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
    public function events()
{
    return $this->hasMany(ClaimEvent::class);
}

}
