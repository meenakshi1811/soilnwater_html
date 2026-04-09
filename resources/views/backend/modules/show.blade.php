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

    <div class="chart-card">
        <h5 class="mb-2">Placeholder workspace</h5>
        <p class="mb-0 text-secondary">Wire this view to your domain logic (CRUD screens, approvals, etc.). Route: <code>/modules/{{ $module }}</code></p>
    </div>
</div>
@endsection
