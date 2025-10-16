<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Renewal extends Model
{
    protected $table = 'renewals';

    protected $fillable = [
        'fileno',
        'original_policy_id',
        'renewed_policy_id',
        'renewal_date',
        'renewal_type',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'renewal_date' => 'datetime'
    ];

    /**
     * Get the original policy that was renewed.
     */
    public function originalPolicy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'original_policy_id');
    }

    /**
     * Get the renewed policy.
     */
    public function renewedPolicy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'renewed_policy_id');
    }

    /**
     * Get the user who created the renewal.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Create a new renewal from an existing policy.
     *
     * @param Policy $originalPolicy
     * @param array $attributes
     * @return Renewal
     */
    public static function createFromPolicy(Policy $originalPolicy, array $attributes = [])
    {
        $renewal = new static();
        $renewal->fileno = $originalPolicy->fileno;
        $renewal->original_policy_id = $originalPolicy->id;
        $renewal->renewal_type = 'standard';
        $renewal->renewal_date = now();
        
        // Fill other attributes
        $renewal->fill($attributes);
        
        return $renewal;
    }
}
