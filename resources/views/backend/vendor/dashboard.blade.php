@extends('backend.layouts.app')

@section('title', 'Vendor Dashboard')

@section('content')
    @include('backend.partials.marketplace-portal-dashboard', [
        'profile' => $vendor,
        'portalType' => 'vendor',
        'portalKicker' => 'Vendor Panel',
        'portalSingular' => 'Vendor',
        'welcomeText' => 'Welcome, '.$vendor->publicDisplayName().'. Manage your branches and public storefront.',
        'liveUrl' => route('store.show', $vendor->slug),
        'liveLabel' => 'View live store',
        'stats' => $stats,
        'analytics' => $analytics,
        'publicPageRoute' => route('vendor.public-page.edit'),
        'publicPageDescription' => 'Edit hero banner, headings, and custom sections for your India storefront.',
        'branchesRoute' => route('vendor.branches.index'),
        'profileLinkTitle' => 'Store link',
        'profileLinkUrl' => route('store.show', $vendor->slug),
    ])
@endsection
