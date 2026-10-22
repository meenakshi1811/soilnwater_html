@extends('frontend.layouts.app')
@section('meta_title', 'Businesses near you | SoilnWater')
@section('meta_description', 'Find trusted vendors, consultants and service providers near you on SoilnWater.')

@section('content')
@include('frontend.partials.directory-hub', [
    'modules' => $modules,
    'hub' => [
        'crumb' => 'Businesses',
        'eyebrow' => 'Business Directory',
        'eyebrow_icon' => 'fa-briefcase',
        'title' => 'Find trusted businesses,',
        'highlight' => 'experts and services near you',
        'text' => 'Vendors, consultants and service providers on SoilnWater, all in one place. Choose where you want to start.',
        'stats' => [
            ['label' => 'Businesses listed', 'count' => $totals['businesses'], 'icon' => 'fa-briefcase', 'tone' => 'green'],
            ['label' => 'Premium partners', 'count' => $totals['premium'], 'icon' => 'fa-crown', 'tone' => 'orange'],
            ['label' => 'Categories', 'count' => $totals['categories'], 'icon' => 'fa-layer-group', 'tone' => 'purple'],
            ['label' => 'Members', 'count' => $totals['members'], 'icon' => 'fa-users', 'tone' => 'blue'],
        ],
        'modules_lead' => 'Only approved listings are shown.',
        'steps' => [
            ['title' => 'Search & filter', 'text' => 'Narrow down by category, location, distance and ratings.', 'icon' => 'fa-magnifying-glass'],
            ['title' => 'Check the profile', 'text' => 'See photos, services, reviews and verified badges.', 'icon' => 'fa-id-card'],
            ['title' => 'Contact directly', 'text' => 'Call or send an enquiry straight to the business.', 'icon' => 'fa-phone'],
            ['title' => 'Rate & review', 'text' => 'Share your experience to help others choose well.', 'icon' => 'fa-star'],
        ],
        'cta' => [
            'icon' => 'fa-rocket',
            'title' => 'Own a business? Get listed on SoilnWater',
            'text' => 'Create your vendor, consultant or service profile and reach customers near you.',
            'actions' => [
                ['label' => 'List your business', 'url' => auth()->check() ? route('user.profile.edit') : route('login')],
                ['label' => 'See premium plans', 'url' => route('frontend.premium.show', 'vendor')],
            ],
        ],
    ],
])
@endsection
