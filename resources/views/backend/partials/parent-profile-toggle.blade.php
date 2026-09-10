@php
    $parentProfile = $user->parentProfile ?? null;
    $parentEnabled = (bool) ($parentProfile?->is_enabled ?? false);
    $toggleUrl = route('parent.profile.toggle');
    $dashboardUrl = route('parent.dashboard');
@endphp

<div class="card admin-table-card mb-4" id="parentProfileToggleCard">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h5 class="mb-1">Parent / Guardian Profile</h5>
                <p class="mb-0 text-secondary">
                    Enable a parent profile alongside your current role. You can add child profiles with login credentials and manage them from your parent dashboard after admin approval.
                </p>
            </div>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="parentProfileEnabledSwitch"
                    @checked($parentEnabled) data-toggle-url="{{ $toggleUrl }}" data-dashboard-url="{{ $dashboardUrl }}">
                <label class="form-check-label" for="parentProfileEnabledSwitch">Enable parent profile</label>
            </div>
        </div>

        @if($parentEnabled)
            <div class="mt-3 d-flex flex-wrap gap-2">
                <a href="{{ $dashboardUrl }}" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-children me-1"></i>Open Parent Dashboard
                </a>
                <span class="badge text-bg-success-subtle text-success align-self-center">
                    Parent profile active · {{ $parentProfile?->profile_completion ?? 0 }}% complete
                </span>
            </div>
        @endif
    </div>
</div>
