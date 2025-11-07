<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'kra_pin', 'commission_rate', 'status', 'user_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($agent) {
            // Generate AGN-XXXXX code
            $latest = self::max('id') + 1;
            $agent->agent_code = 'AGN-' . str_pad($latest, 5, '0', STR_PAD_LEFT);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get all policies associated with this agent
     */
    public function policies()
    {
        return $this->hasMany(Policy::class, 'agent_id');
    }
}
