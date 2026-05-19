<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    protected $fillable = [
        'vendor_id', 'name', 'brand', 'sku', 'description', 'category', 'colors', 'sizes',
        'base_price', 'discount_percent', 'final_price', 'stock_quantity', 'shipping_charges',
        'specs', 'bulk_tiers', 'images', 'video_file', 'youtube_link', 'is_online_sale',
    ];

    protected $casts = [
        'specs' => 'array',
        'bulk_tiers' => 'array',
        'images' => 'array',
        'is_online_sale' => 'boolean',
    ];
}
