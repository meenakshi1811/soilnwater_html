@php
    $viewingAsParent = $viewingAsParent ?? false;
@endphp

<header class="child-topbar">
    <div class="child-topbar__left">
        <button type="button" class="child-topbar__menu-btn d-lg-none" id="childSidebarToggle" aria-label="Toggle menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        @if($viewingAsParent)
            <a href="{{ route('parent.dashboard') }}" class="child-topbar__back"><i class="fa-solid fa-arrow-left"></i> Back to Parent Dashboard</a>
        @else
            <a href="{{ route('frontend.index') }}" class="child-topbar__brand" target="_blank">
                <img src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater" height="28">
            </a>
        @endif
    </div>
    <div class="child-topbar__right">
        @auth
            <span class="child-topbar__user">{{ auth()->user()->full_name ?: auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="child-btn child-btn--ghost child-btn--sm">Logout</button>
            </form>
        @endauth
    </div>
</header>
