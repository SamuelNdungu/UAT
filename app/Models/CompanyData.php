<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyData extends Model
{
    protected $table = 'company_data';

    protected $fillable = [
        'company_name',
        'email',
        'phone',
        'website',
        'address',
        'logo_path'
    ];

    public $timestamps = true;
}
