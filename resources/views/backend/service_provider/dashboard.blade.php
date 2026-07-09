@extends('backend.layouts.app')

@section('title', 'Service Dashboard')

@section('content')
    @include('backend.partials.marketplace-portal-dashboard', [
        'profile' => $service_provider,
        'portalType' => 'service',
        'portalKicker' => 'Service Panel',
        'portalSingular' => 'Service',
        'welcomeText' => 'Welcome, '.$service_provider->publicDisplayName().'. Manage your branches and public service profile.',
        'liveUrl' => route('service_provider.show', $service_provider->slug),
        'liveLabel' => 'View live service',
        'stats' => $stats,
        'analytics' => $analytics,
        'publicPageRoute' => route('service_provider.public-page.edit'),
        'publicPageDescription' => 'Edit hero banner, headings, and custom sections for your India service profile.',
        'branchesRoute' => route('service_provider.branches.index'),
        'profileLinkTitle' => 'Service link',
        'profileLinkUrl' => route('service_provider.show', $service_provider->slug),
    ])
@endsection
