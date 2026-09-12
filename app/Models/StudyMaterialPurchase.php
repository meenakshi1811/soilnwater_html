<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyMaterialPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'study_material_id',
        'listing_payment_submission_id',
        'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyMaterial(): BelongsTo
    {
        return $this->belongsTo(StudyMaterial::class);
    }

    public function paymentSubmission(): BelongsTo
    {
        return $this->belongsTo(ListingPaymentSubmission::class, 'listing_payment_submission_id');
    }
}
