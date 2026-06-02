<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantServiceInquiry extends Model
{
    protected $fillable = [
        'consultant_id',
        'consultant_service_id',
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

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ConsultantService::class, 'consultant_service_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
