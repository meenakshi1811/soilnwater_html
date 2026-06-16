<?php

namespace App\Models;

use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityCategorySubscription extends Model
{
    protected $fillable = [
        'user_id',
        'content_type',
        'category',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        $typeLabel = CommunityContentTaxonomy::labels()[$this->content_type]
            ?? \Illuminate\Support\Str::headline($this->content_type);

        return $typeLabel.' · '.$this->category;
    }
}
