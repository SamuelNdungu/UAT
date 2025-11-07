<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    // Define the attributes that are mass assignable
    protected $fillable = [
        'customer_code',
        'customer_type',
        'title',
        'first_name',
        'last_name',
        'surname',
        'dob',
        'occupation',
        'corporate_name',
        'business_no',
        'contact_person',
        'designation',
        'industry_class',
        'industry_segment',
        'email',
        'phone',
        'address',
        'city',
        'county',
        'postal_code',
        'country',
        'id_number',
        'kra_pin',
        'documents',
        'notes',
        'status',
        'user_id',
        'agent_id', // <-- Add this line
    ];
    protected $casts = [
        // Cast status to an integer
        'status' => 'integer', // ensure status is stored/read as integer (1 = Active, 0 = Inactive)
    ];
public function documentable()
{
    return $this->morphTo();
}
    // Relationship with Document
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // Relationship with Claim - linked with customer_code
    public function claims()
    {
        return $this->hasMany(Claim::class, 'customer_code', 'customer_code');
    }

    // Relationship with Policy - linked with customer_code
    public function policies()
    {
        return $this->hasMany(Policy::class, 'customer_code', 'customer_code'); // Specify keys correctly
    }

    // Accessor to get formatted customer name
    public function getCustomerNameAttribute()
{
    if ($this->customer_type == 'Individual') {
        return $this->first_name . ' ' . $this->last_name;
    } elseif ($this->customer_type == 'Corporate') {
        return $this->corporate_name;
    }

    return 'Unknown'; // Fallback in case customer type is neither
}

/**
 * Generate the next customer code - recommended simple approach
 */
public static function generateNextCustomerCode(string $prefix = 'CUST-', int $pad = 4): string
{
    // Get the highest numeric suffix
    $lastCustomer = DB::table('customers')
        ->select('customer_code')
        ->where('customer_code', 'like', $prefix . '%')
        ->orderByRaw('CAST(SUBSTRING(customer_code FROM \'' . strlen($prefix) + 1 . '\') AS INTEGER) DESC')
        ->first();

    if ($lastCustomer) {
        $lastCode = $lastCustomer->customer_code;
        $suffix = substr($lastCode, strlen($prefix));
        
        // Extract numeric part
        if (preg_match('/^\d+$/', $suffix)) {
            $nextNumber = (int)$suffix + 1;
        } else {
            // If not purely numeric, find the next available number
            $maxNumber = DB::table('customers')
                ->where('customer_code', 'like', $prefix . '%')
                ->get()
                ->map(function ($customer) use ($prefix) {
                    $suffix = substr($customer->customer_code, strlen($prefix));
                    preg_match('/\d+/', $suffix, $matches);
                    return $matches ? (int)$matches[0] : 0;
                })
                ->max();
            
            $nextNumber = $maxNumber + 1;
        }
    } else {
        $nextNumber = 1;
    }

    return $prefix . str_pad((string)$nextNumber, $pad, '0', STR_PAD_LEFT);
}

/**
 * Boot method to ensure new customers get a safe customer_code if not provided.
 */
protected static function booted()
{
    static::creating(function ($customer) {
        if (empty($customer->customer_code)) {
            // generate next safe code (you can adjust prefix/pad as needed)
            $customer->customer_code = self::generateNextCustomerCode('CUST-', 6);
        }
    });

    // ...existing boot logic...
}

public function agent()
{
    return $this->belongsTo(\App\Models\Agent::class);
}

}
