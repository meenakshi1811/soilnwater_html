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

                @foreach($vendor->pageSections as $sec)
                    <a href="{{ route('store.show', $vendor->slug) }}#section-{{ $sec->id }}" class="vendor-store-nav-link">{{ strip_tags($sec->title) }}</a>
                @endforeach

                <a href="{{ route('store.show', $vendor->slug) }}#contact" class="vendor-store-nav-link">Contact</a>
            </nav>
        </div>
    </div>
</header>
