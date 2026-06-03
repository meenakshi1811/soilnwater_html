<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProviderServiceInquiry extends Model
{
    protected $fillable = [
        'service_provider_id',
        'service_provider_service_id',
        'user_id',
        'client_name',
        'phone_number',
        'email',
        'occupation',
        'date_of_birth',
        'question',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function service_provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderService::class, 'service_provider_service_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
