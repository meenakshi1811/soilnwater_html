@php
    $isStoreHome = ($activeNav ?? '') === 'home';
    $storeHomeUrl = $storeHomeUrl ?? url('/');
    $mainHomeUrl = route('home');
    $backFallbackUrl = $isStoreHome ? $mainHomeUrl : $storeHomeUrl;
@endphp

<nav class="vendor-store-quicknav" aria-label="Store quick navigation">
    <div class="container">
        <div class="vendor-store-quicknav__inner">
            <button
                type="button"
                class="vendor-store-quicknav__btn js-store-nav-back"
                data-fallback-url="{{ $backFallbackUrl }}"
            >
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back</span>
            </button>
            <a
                href="{{ $storeHomeUrl }}"
                class="vendor-store-quicknav__btn{{ $isStoreHome ? ' is-active' : '' }}"
                @if($isStoreHome) aria-current="page" @endif
            >
                <i class="fa-solid fa-store" aria-hidden="true"></i>
                <span>{{ $storeHomeLabel ?? 'Store Home' }}</span>
            </a>
            <a href="{{ $mainHomeUrl }}" class="vendor-store-quicknav__btn">
                <i class="fa-solid fa-house" aria-hidden="true"></i>
                <span>Homepage</span>
            </a>
        </div>
    </div>
</nav>
