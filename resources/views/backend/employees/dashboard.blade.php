@extends('backend.layouts.app')

@section('title', 'Employee Portal')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Employee portal</p>
            <h2 class="admin-title mb-1">Welcome, {{ $employee->name }}</h2>
            <p class="mb-0 text-secondary">
                You can only open modules granted by your assigned role.
                @if ($roleName)
                    Current role: <strong>{{ $roleName }}</strong>.
                @else
                    No role is assigned yet. Ask an admin to assign one.
                @endif
            </p>
        </div>
    </div>

    <div class="chart-card">
        @if ($firstModuleSlug)
            <p class="mb-3">Open your first assigned module to start work.</p>
            <a class="btn btn-primary" href="{{ route('modules.show', $firstModuleSlug) }}">Go to assigned modules</a>
        @else
            <h5 class="mb-2">No module access yet</h5>
            <p class="mb-0 text-secondary">Your account is active, but the assigned role does not include any module permissions. An admin can update your role under Employees.</p>
        @endif
    </div>
</div>
@endsection
