<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurer extends Model
{
    use HasFactory;

    // Define the table if it does not follow Laravel's convention
    protected $table = 'insurers';

    // Define the fillable fields
    protected $fillable = [
        'name',
        // other fields
    ];
}
