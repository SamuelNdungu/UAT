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
        'logo_path' // This matches your database column
    ];

    public $timestamps = true;

    public function getLogoUrl()
    {
        if ($this->logo_path) { // Use logo_path to match your database
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }
}