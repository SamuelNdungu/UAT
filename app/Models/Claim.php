<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClaimEvent;

class Claim extends Model
{
    use HasFactory;

    // Standardized statuses
    public const STATUS_OPEN = 'open';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_SETTLED = 'settled';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CLOSED = 'closed';

    // Add/ensure these attributes are mass assignable
    protected $fillable = [
        'policy_id',
        'customer_id',
        'claim_no',
        'date_of_loss',
        'reported_at',
        'status',
        'description',
        'amount',
        'attachments', // JSON metadata for uploaded files
        'created_by',
        // ...other existing fields...
    ];
    protected $dates = [
        'reported_at',
        'date_of_loss',
        // Add any other date fields here
    ];
    // Casts for convenient usage
    protected $casts = [
        'date_of_loss' => 'date',
        'reported_at' => 'datetime',
        'amount' => 'float',
        'attachments' => 'array',
    ];

    // Relationships
    public function policy()
    {
        return $this->belongsTo(Policy::class, 'policy_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    /**
     * Events related to this claim (notes, timeline entries, etc.)
     */
    public function events()
    {
        return $this->hasMany(ClaimEvent::class, 'claim_id');
    }

    /**
     * Documents (attachments) for this claim.
     */
    public function documents()
    {
        return $this->hasMany(\App\Models\Document::class, 'claim_id');
    }

    // Human-readable status list
    public static function statusList(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_INVESTIGATING => 'Investigating',
            self::STATUS_SETTLED => 'Settled',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    // Accessor for status label
    public function getStatusLabelAttribute()
    {
        return self::statusList()[$this->status] ?? ucfirst($this->status);
    }
}
