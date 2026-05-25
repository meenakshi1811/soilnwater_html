<header class="vendor-store-header">
    <div class="container">
        <div class="vendor-store-header__inner">
            <a href="{{ route('store.show', $vendor->slug) }}" class="vendor-store-brand">
                @if($vendor->logo)
                    <img src="{{ asset($vendor->logo) }}" alt="{{ $vendor->publicDisplayName() }}" height="44">
                @else
                    <span class="vendor-store-brand__text">{{ $vendor->publicDisplayName() }}</span>
                @endif
            </a>

            <button class="vendor-store-mobile-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#vendorStoreNav" aria-controls="vendorStoreNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="vendor-store-nav collapse d-lg-flex" id="vendorStoreNav">
                <a href="{{ route('store.show', $vendor->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'home' ? 'is-active' : '' }}">Home</a>

                @include('frontend.store.partials.products-mega-menu', [
                    'vendor' => $vendor,
                    'vendorCategories' => $vendorCategories ?? collect(),
                    'activeNav' => $activeNav ?? '',
                ])

                <a href="{{ route('store.about', $vendor->slug) }}" target="_blank" rel="noopener" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'about' ? 'is-active' : '' }}">About Us</a>

                <a href="{{ route('store.contact', $vendor->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'contact' ? 'is-active' : '' }}">Contact</a>
            </nav>
        </div>
    </div>
</header>
