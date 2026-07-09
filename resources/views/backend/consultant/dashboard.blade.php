@extends('backend.layouts.app')

@section('title', 'Consultant Dashboard')

@section('content')
    @include('backend.partials.marketplace-portal-dashboard', [
        'profile' => $consultant,
        'portalType' => 'consultant',
        'portalKicker' => 'Consultant Panel',
        'portalSingular' => 'Consultant',
        'welcomeText' => 'Welcome, '.$consultant->publicDisplayName().'. Manage your branches and public consultant profile.',
        'liveUrl' => route('consultant.show', $consultant->slug),
        'liveLabel' => 'View live consultant',
        'stats' => $stats,
        'analytics' => $analytics,
        'publicPageRoute' => route('consultant.public-page.edit'),
        'publicPageDescription' => 'Edit hero banner, headings, and custom sections for your India consultant profile.',
        'branchesRoute' => route('consultant.branches.index'),
        'profileLinkTitle' => 'Consultant link',
        'profileLinkUrl' => route('consultant.show', $consultant->slug),
    ])
@endsection
