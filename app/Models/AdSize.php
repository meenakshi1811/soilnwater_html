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
        'is_active',
    ];

    protected $casts = [
        'admin_only' => 'bool',
        'is_active' => 'bool',
    ];
}
