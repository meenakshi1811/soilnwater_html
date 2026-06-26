@extends('frontend.layouts.app')

@section('meta_title', 'My Area | SoilnWater Community')
@section('meta_description', 'Report issues, suggest improvements, recognize heroes, share achievements, raise awareness, and track resolutions in your local area.')
@section('meta_url', route('community.my-area.index'))
@section('meta_canonical', route('community.my-area.index'))

@push('styles')
<style>
    .my-area-hero {
        background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 45%, #40916c 100%);
        color: #fff;
        padding: clamp(48px, 6vw, 72px) 24px;
    }
    .my-area-hero__inner { max-width: min(1720px, calc(100vw - 48px)); margin: 0 auto; }
    .my-area-hero__title { font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; margin-bottom: 0.75rem; }
    .my-area-hero__text { max-width: 760px; opacity: 0.92; margin-bottom: 1.5rem; }
    .my-area-feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; margin-top: 1.5rem; }
    .my-area-feature { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 0.75rem; padding: 0.85rem 1rem; font-size: 0.9rem; }
    .my-area-filters { background: #fff; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 8px 24px rgba(15,47,85,0.06); }
    .my-area-shell { max-width: min(1720px, calc(100vw - 48px)); margin: 0 auto; padding: 2rem 24px 4rem; }
    .my-area-breadcrumb { margin-bottom: 1rem; }
    .my-area-breadcrumb a { color: #1b4332; text-decoration: none; font-weight: 600; }
    .my-area-breadcrumb a:hover { text-decoration: underline; }
    .my-area-posts-grid { margin-top: 0.5rem; }
</style>
@endpush

@section('content')
<section class="my-area-hero">
    <div class="my-area-hero__inner">
        <p class="text-uppercase small fw-bold mb-2" style="letter-spacing:.08em;opacity:.85;">SoilnWater Civic Hub</p>
        <h1 class="my-area-hero__title">My Area</h1>
        <p class="my-area-hero__text">
            Your dedicated local community section — connect directly with neighbours and authorities through
            location-based feeds, area discussions, community voting, issue tracking, authority tagging, and resolution monitoring.
        </p>
        @auth
            <a href="{{ route('community.posts.create', ['type' => 'my-area']) }}" class="btn btn-light btn-lg">Share with your area</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-light btn-lg">Login to participate</a>
        @endauth
        <div class="my-area-feature-grid">
            @foreach($activityTypes as $activity)
                <div class="my-area-feature"><i class="fa-solid fa-location-dot me-2"></i>{{ $activity }}</div>
            @endforeach
        </div>
    </div>
</section>

<div class="my-area-shell">
    <nav class="my-area-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('community.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Community Hub</a>
        <span class="text-muted mx-2">/</span>
        <span class="text-muted">My Area</span>
    </nav>

    <form method="get" action="{{ route('community.my-area.index') }}" class="my-area-filters">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Activity</label>
                <select name="activity" class="form-select form-select-sm">
                    <option value="">All activities</option>
                    @foreach($activityTypes as $activity)
                        <option value="{{ $activity }}" @selected($filters['activity'] === $activity)>{{ $activity }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Topic</label>
                <select name="topic" class="form-select form-select-sm">
                    <option value="">All topics</option>
                    @foreach($topicCategories as $topic)
                        <option value="{{ $topic }}" @selected($filters['topic'] === $topic)>{{ $topic }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Any status</option>
                    @foreach($statusSteps as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">State</label>
                <input type="text" name="state" class="form-control form-control-sm" value="{{ $filters['state'] }}" placeholder="e.g. Uttarakhand">
            </div>
            <div class="col-md-2">
                <label class="form-label small">District</label>
                <input type="text" name="district" class="form-control form-control-sm" value="{{ $filters['district'] }}" placeholder="e.g. Dehradun">
            </div>
            <div class="col-md-2">
                <label class="form-label small">City</label>
                <input type="text" name="city" class="form-control form-control-sm" value="{{ $filters['city'] }}" placeholder="e.g. Dehradun">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success btn-sm w-100">Filter feed</button>
            </div>
        </div>
    </form>

    @if($posts->isEmpty())
        <div class="alert alert-light border text-center py-5">
            <h4 class="mb-2">No My Area posts yet</h4>
            <p class="text-muted mb-3">Be the first to report an issue, recognize a hero, or share a local achievement.</p>
            @auth
                <a href="{{ route('community.posts.create', ['type' => 'my-area']) }}" class="btn btn-success">Create My Area post</a>
            @endauth
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 g-lg-4 my-area-posts-grid">
            @include('community.partials.post-cards', ['posts' => $posts, 'engagement' => $engagement])
        </div>
        <div class="mt-4">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
