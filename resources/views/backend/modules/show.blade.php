@extends('backend.layouts.app')

@section('title', $title)

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Module</p>
            <h2 class="admin-title mb-1">{{ $title }}</h2>
            <p class="mb-0 text-secondary">
                Your access is limited to the actions granted by your role:
                @forelse($allowedActions as $action)
                    <span class="badge text-bg-success text-uppercase">{{ $action }}</span>
                @empty
                    <span class="text-muted">none</span>
                @endforelse
            </p>
        </div>
    </div>

    <div class="chart-card">
        <h5 class="mb-2">{{ $title }} workspace</h5>
        <p class="mb-0 text-secondary">
            @if(!empty($isEmployee))
                You can only view or change this area if your assigned role includes the matching permission.
            @else
                Admin accounts have full access to every module action.
            @endif
        </p>
    </div>
</div>
@endsection
