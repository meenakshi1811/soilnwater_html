@php
    $parentProfile = $user->parentProfile ?? null;
    $hasActiveParentProfile = $parentProfile && $parentProfile->isApproved() && $parentProfile->is_enabled;
    $hasPendingOrApprovedRequest = $parentProfile && in_array($parentProfile->status, ['pending', 'approved'], true);
@endphp

@if(! $user->canShowAccountGrowthOptions())
@elseif($hasActiveParentProfile)
    <div class="card admin-table-card mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">Parent / Guardian Profile</h5>
                <p class="mb-0 text-secondary">Your parent profile is active. Manage child profiles from your parent dashboard.</p>
            </div>
            <a href="{{ route('parent.dashboard', absolute: false) }}" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-children me-1"></i>Open Parent Dashboard
            </a>
        </div>
    </div>
@elseif(! $hasPendingOrApprovedRequest)
    <div class="card admin-table-card mb-4">
        <div class="card-body">
            <div class="mb-3">
                <h5 class="mb-1">Parent / Guardian Profile</h5>
                <p class="mb-0 text-secondary">
                    Create a dedicated parent profile to add and manage child accounts. Your request will be sent to the admin team for approval before the parent dashboard is unlocked.
                </p>
            </div>
            <form method="POST" action="{{ route('user.convert-to-parent') }}" class="js-become-account-form"
                data-title="Create as Parent Profile?"
                data-text="Create a parent profile with your current account details and send it to admin for approval?"
                data-success="Your parent profile request has been sent for approval.">
                @csrf
                <button type="submit" class="btn btn-outline-primary">Create as Parent Profile</button>
            </form>
        </div>
    </div>
@endif
