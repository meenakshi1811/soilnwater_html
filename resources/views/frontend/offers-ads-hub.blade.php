@extends('frontend.layouts.app')
@section('meta_title', 'Offers & Ads near you | SoilnWater')
@section('meta_description', 'Deals, discounts and coupons from local businesses, plus classified ads and promotions near you on SoilnWater.')

@section('content')
@include('frontend.partials.directory-hub', [
    'modules' => $modules,
    'hub' => [
        'crumb' => 'Offers & Ads',
        'eyebrow' => 'Deals & Classifieds',
        'eyebrow_icon' => 'fa-tags',
        'title' => 'Grab the best deals',
        'highlight' => 'and local ads near you',
        'text' => 'Discounts and coupons from local businesses, plus classified ads and promotions, all in one place.',
        'stats' => [
            ['label' => 'Live offers', 'count' => $totals['offers'], 'icon' => 'fa-gift', 'tone' => 'orange'],
            ['label' => 'Live ads', 'count' => $totals['ads'], 'icon' => 'fa-rectangle-ad', 'tone' => 'cyan'],
            ['label' => 'Ending this week', 'count' => $totals['ending'], 'icon' => 'fa-hourglass-half', 'tone' => 'purple'],
            ['label' => 'Categories', 'count' => $totals['categories'], 'icon' => 'fa-layer-group', 'tone' => 'green'],
        ],
        'modules_lead' => 'Only live offers and approved ads are shown.',
        'steps' => [
            ['title' => 'Browse & filter', 'text' => 'Narrow down by category, location and more.', 'icon' => 'fa-magnifying-glass'],
            ['title' => 'Check the details', 'text' => 'See the discount, coupon code, validity and location.', 'icon' => 'fa-ticket'],
            ['title' => 'Visit or get in touch', 'text' => 'Go to the business or contact the advertiser directly.', 'icon' => 'fa-store'],
            ['title' => 'Report problems', 'text' => 'Flag a misleading offer or ad and our team will review it.', 'icon' => 'fa-flag'],
        ],
        'cta' => [
            'icon' => 'fa-bullhorn',
            'title' => 'Running a promotion? Post it on SoilnWater',
            'text' => 'Share an offer or put up an ad and reach customers near you.',
            'actions' => [
                ['label' => 'Post an offer', 'url' => route('post-offer')],
                ['label' => 'Post an ad', 'url' => route('ads.create.size')],
            ],
        ],
    ],
])
@endsection
