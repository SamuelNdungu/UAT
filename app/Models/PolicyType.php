<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyType extends Model
{
    use HasFactory;
    protected $table = 'policy_types';
    protected $fillable = ['type_name'];

    public function policy_type()
    {
        return $this->belongsTo(PolicyType::class, 'policy_type_id');
    }
}
