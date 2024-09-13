<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    // Define any necessary properties or relationships here, e.g.:
    protected $fillable = ['name', 'file_path'];
}
