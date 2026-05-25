<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $fillable = [
        'hero_banner_image',
        'offers_market_banner_image',
        'ads_market_banner_image',
        'hero_button_text',
        'hero_button_link',
        'section_toggles',
        'vendor_enquiry_send_to',
    ];

    protected $casts = [
        'section_toggles' => 'array',
    ];
}
