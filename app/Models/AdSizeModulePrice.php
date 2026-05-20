<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdSizeModulePrice extends Model
{
    use HasFactory;

    protected $fillable = ['ad_size_id', 'module_key', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    public function adSize(): BelongsTo
    {
        return $this->belongsTo(AdSize::class);
    }
}
