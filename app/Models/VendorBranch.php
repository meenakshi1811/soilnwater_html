<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBranch extends Model
{
    protected $fillable = [
        'vendor_id',
        'branch_name',
        'contact_person',
        'phone',
        'alt_mobile_number',
        'whatsapp',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'pan_number',
        'gst_number',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
