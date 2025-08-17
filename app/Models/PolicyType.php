<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyType extends Model
{
    use HasFactory;
    protected $table = 'policy_types';
        protected $fillable = [
            'type_name',
            'user_id'
        ];

        public function policies()
        {
            return $this->hasMany(\App\Models\Policy::class, 'policy_type_id');
        }

    public function policy_type()
    {
        return $this->belongsTo(PolicyType::class, 'policy_type_id');
    }
}
