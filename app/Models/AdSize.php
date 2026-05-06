<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'size_key',
        'name',
        'width',
        'height',
        'admin_only',
        'is_paid',
        'amount',
        'is_active',
    ];

    protected $casts = [
        'admin_only' => 'bool',
        'is_paid' => 'bool',
        'amount' => 'decimal:2',
        'is_active' => 'bool',
    ];
}
