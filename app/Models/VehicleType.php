<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $table = 'vehicle_types';

    protected $fillable = [
        'make',
        'model',
        'user_id',
    ];

    public function policies()
    {
        return $this->hasMany(\App\Models\Policy::class, 'make', 'make')->where('model', $this->model);
    }
}
