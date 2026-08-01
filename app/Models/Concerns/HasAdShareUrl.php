<?php

namespace App\Models\Concerns;

use App\Support\SocialShare;

trait HasAdShareUrl
{
    public function shareUrl(): string
    {
        return SocialShare::normalizeUrl(
            route('frontend.ads.show', ['ad' => $this->getRouteKey()])
        );
    }
}
