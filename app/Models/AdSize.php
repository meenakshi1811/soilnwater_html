<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'size_key',
        'name',
        'width',
        'height',
        'module_key',
        'module_price',
        'admin_only',
        'is_paid',
        'amount',
        'is_active',
    ];

    protected $casts = [
        'admin_only' => 'bool',
        'module_price' => 'decimal:2',
        'is_paid' => 'bool',
        'amount' => 'decimal:2',
        'is_active' => 'bool',
    ];

    public function categoryPrices(): HasMany
    {
        return $this->hasMany(AdSizeCategoryPrice::class);
    }

    public function modulePrices(): HasMany
    {
        return $this->hasMany(AdSizeModulePrice::class);
    }
}
