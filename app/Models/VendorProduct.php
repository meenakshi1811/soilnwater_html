<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    protected $fillable = [
        'vendor_id', 'name', 'brand', 'sku', 'description', 'category_id', 'subcategory_id', 'category', 'colors', 'sizes',
        'base_price', 'discount_percent', 'final_price', 'stock_quantity', 'shipping_charges', 'location', 'latitude', 'longitude',
        'specs', 'bulk_tiers', 'images', 'video_file', 'youtube_link', 'is_online_sale','status', 'approved_at', 'approved_by', 
    ];

    protected $casts = [
        'specs' => 'array',
        'bulk_tiers' => 'array',
        'images' => 'array',
        'is_online_sale' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }
}
