<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultantService extends Model
{
    protected $fillable = [
        'consultant_id',
        'name',
        'slug',
        'category_id',
        'subcategory_id',
        'category',
        'short_description',
        'description',
        'price',
        'duration',
        'location',
        'is_online',
        'images',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'images' => 'array',
        'is_online' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
