<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducatorEnquiry extends Model
{
    protected $fillable = [
        'educator_id',
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'answer',
        'answered_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
        ];
    }

    public function isAnswered(): bool
    {
        return filled($this->answer) && $this->answered_at !== null;
    }

    public function profileUrl(): string
    {
        $this->loadMissing('educator');

        return route('educator.show', $this->educator->slug).'#edu-question';
    }

    public function educator(): BelongsTo
    {
        return $this->belongsTo(Educator::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
