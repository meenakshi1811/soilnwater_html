<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantBranch extends Model
{
    protected $fillable = [
        'consultant_id',
        'branch_name',
        'contact_person',
        'occupation',
        'logo',
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

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }
}
