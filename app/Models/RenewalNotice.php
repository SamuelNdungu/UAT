<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RenewalNotice extends Model
{
    protected $table = 'renewal_notices';

    protected $fillable = [
        'fileno',
        'policy_id',
        'customer_code',
        'channel',
        'sent_at',
        'sent_by',
        'message_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'policy_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
