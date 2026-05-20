<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProductInquiry extends Model
{
    protected $fillable = [
        'vendor_id',
        'vendor_product_id',
        'user_id',
        'email',
        'phone_number',
        'preferred_contact',
        'reason',
    ];

    public function product()
    {
        return $this->belongsTo(VendorProduct::class, 'vendor_product_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
