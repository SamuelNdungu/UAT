<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceCompany extends Model
{
    protected $table = 'insurers';
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'city',
        'country',
        'physical_address',
        'street',
        'user_id'
    ];

    public function policies()
    {
        return $this->hasMany(\App\Models\Policy::class, 'insurer_id');
    }
}