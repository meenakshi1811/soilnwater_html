@php
    use App\Support\AccountCreation;
    use App\Support\AuthActor;

    $portalActor = AuthActor::user();
    $portalIsAdmin = AccountCreation::portalActorIsAdmin($portalActor);
    $canCreate = AccountCreation::canCreateRole($portalActor, $portalIsAdmin, $role ?? null)
        || (! isset($role) && AccountCreation::canCreateAny($portalActor, $portalIsAdmin));
@endphp

@if($canCreate)
    <button
        type="button"
        class="btn btn-primary ems-btn-primary {{ $class ?? '' }} js-open-create-account"
        @if(! empty($role)) data-role="{{ $role }}" data-lock-role="1" @endif
        data-modal-title="{{ $modalTitle ?? ($label ?? 'Add User') }}"
    >
        <i class="fa-solid fa-user-plus me-2"></i> {{ $label ?? 'Add User' }}
    </button>
@endif
