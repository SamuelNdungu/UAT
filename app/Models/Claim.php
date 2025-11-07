<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'customer_code',
        'fileno',
        'claim_number',      // Use consistent naming
        'claim_no',          // Keep for backward compatibility if needed
        'loss_date',         // Primary date field
        'date_of_loss',      // Alias if needed
        'reported_date',
        'reported_at',       // Alias if needed
        'followup_date',
        'status',
        'type_of_loss',
        'claimant_name',
        'loss_details',
        'amount_claimed',
        'amount_paid',
        'attachments',
        'created_by',
        'user_id',           // If you have this field
        'description',       // Only include once
    ];

    protected $dates = [
        'loss_date',
        'date_of_loss',
        'reported_date',
        'reported_at',
        'followup_date',
        'created_at',
        'updated_at',
    ];

    // Casts for convenient usage
    protected $casts = [
        'loss_date' => 'date',
        'date_of_loss' => 'date',
        'reported_date' => 'datetime',
        'reported_at' => 'datetime',
        'followup_date' => 'date',
        'amount_claimed' => 'float',
        'amount_paid' => 'float',
        'attachments' => 'array',
    ];

    // Relationships
    public function policy()
    {
        return $this->belongsTo(Policy::class, 'policy_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
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
        return $this->hasMany(Document::class, 'claim_id');
    }

    /**
     * User who created the claim
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    // Accessor to handle both date_of_loss and loss_date
    public function getDateOfLossAttribute($value)
    {
        return $value ?: $this->loss_date;
    }

    public function setDateOfLossAttribute($value)
    {
        $this->attributes['date_of_loss'] = $value;
        $this->attributes['loss_date'] = $value;
    }

    // Accessor to handle both reported_at and reported_date
    public function getReportedAtAttribute($value)
    {
        return $value ?: $this->reported_date;
    }

    public function setReportedAtAttribute($value)
    {
        $this->attributes['reported_at'] = $value;
        $this->attributes['reported_date'] = $value;
    }

    // Accessor to handle both claim_no and claim_number
    public function getClaimNoAttribute($value)
    {
        return $value ?: $this->claim_number;
    }

    public function setClaimNoAttribute($value)
    {
        $this->attributes['claim_no'] = $value;
        $this->attributes['claim_number'] = $value;
    }
}