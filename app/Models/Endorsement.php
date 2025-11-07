<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endorsement extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'user_id',
        // compatible field names for older and newer migrations
        'type',
        'endorsement_type',
        'reason',
        'description',
        'effective_date',
        'premium_impact',
        // delta fields (newer migration)
        'delta_sum_insured',
        'delta_premium',
        'delta_commission',
        'delta_wht',
        'delta_s_duty',
        'delta_t_levy',
        'delta_pcf_levy',
        'delta_policy_charge',
        'delta_aa_charges',
        'delta_other_charges',
        'delta_gross_premium',
        'delta_net_premium',
        'delta_excess',
        'delta_courtesy_car',
        'delta_ppl',
        'delta_road_rescue',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'delta_sum_insured' => 'decimal:2',
        'delta_premium' => 'decimal:2',
        'delta_commission' => 'decimal:2',
        'delta_wht' => 'decimal:2',
        'delta_s_duty' => 'decimal:2',
        'delta_t_levy' => 'decimal:2',
        'delta_pcf_levy' => 'decimal:2',
        'delta_policy_charge' => 'decimal:2',
        'delta_aa_charges' => 'decimal:2',
        'delta_other_charges' => 'decimal:2',
        'delta_gross_premium' => 'decimal:2',
        'delta_net_premium' => 'decimal:2',
        'delta_excess' => 'decimal:2',
        'delta_courtesy_car' => 'decimal:2',
        'delta_ppl' => 'decimal:2',
        'delta_road_rescue' => 'decimal:2',
        'premium_impact' => 'decimal:2',
        'additions' => 'array',
        'deletions' => 'array',
    ];

    // expose a normalized net impact attribute
    protected $appends = ['net_impact'];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getNetImpactAttribute()
    {
        // Prefer delta_net_premium (newer migrations), then delta_gross_premium, then premium_impact
        if (! is_null($this->delta_net_premium) && $this->delta_net_premium !== '') {
            return (float) $this->delta_net_premium;
        }

        if (! is_null($this->delta_gross_premium) && $this->delta_gross_premium !== '') {
            return (float) $this->delta_gross_premium;
        }

        if (! is_null($this->premium_impact) && $this->premium_impact !== '') {
            return (float) $this->premium_impact;
        }

        // fallback 0.0
        return 0.0;
    }
}
