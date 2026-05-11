@extends('backend.layouts.app')

@section('title', $title)

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Module</p>
            <h2 class="admin-title mb-1">{{ $title }}</h2>
            <p class="mb-0 text-secondary">You have <strong>read</strong> access to this module via Spatie permissions.</p>
        </div>
    </div>

    @if($module === 'user_application_tracking')
        <div class="chart-card">
            <h5 class="mb-3">Application Tracking</h5>
            <div class="d-flex flex-wrap align-items-center gap-2 application-tracking-flow" aria-label="Application status flow">
                <span class="tracking-step tracking-step--pending">Submitted</span>
                <i class="fa-solid fa-arrow-right tracking-arrow" aria-hidden="true"></i>
                <span class="tracking-step tracking-step--review">Under Review</span>
                <i class="fa-solid fa-arrow-right tracking-arrow" aria-hidden="true"></i>
                <span class="tracking-step tracking-step--approved">Approved</span>
            </div>
        </div>
    @else
        <div class="chart-card">
            <h5 class="mb-2">Placeholder workspace</h5>
            <p class="mb-0 text-secondary">Wire this view to your domain logic (CRUD screens, approvals, etc.). Route: <code>/modules/{{ $module }}</code></p>
        </div>
    @endif
</div>

<style>
    .application-tracking-flow {
        row-gap: 0.5rem;
    }

    .tracking-step {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 92px;
        height: 92px;
        border-radius: 50%;
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        text-align: center;
        line-height: 1.2;
        padding: 0.5rem;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
    }

    .tracking-step--pending { background: #f59e0b; }
    .tracking-step--review { background: #3b82f6; }
    .tracking-step--approved { background: #22c55e; }

    .tracking-arrow {
        color: #0d6efd;
        font-size: 1.05rem;
    }
</style>
@endsection
