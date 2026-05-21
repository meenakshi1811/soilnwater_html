<header class="vendor-store-header">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('store.show', $vendor->slug) }}" class="text-decoration-none text-dark">
                @if($vendor->logo)
                    <img src="{{ asset($vendor->logo) }}" alt="{{ $vendor->publicDisplayName() }}" height="48">
                @else
                    <strong class="fs-4">{{ $vendor->publicDisplayName() }}</strong>
                @endif
            </a>
        </div>
        <nav class="vendor-store-nav d-none d-md-flex align-items-center">
            <a href="{{ route('store.show', $vendor->slug) }}#home">Home</a>
            <a href="{{ route('store.products.index', $vendor->slug) }}" class="{{ ($activeNav ?? '') === 'products' ? 'is-active' : '' }}">Products</a>
            @foreach($vendor->pageSections as $sec)
                <a href="{{ route('store.show', $vendor->slug) }}#section-{{ $sec->id }}">{{ strip_tags($sec->title) }}</a>
            @endforeach
            <a href="{{ route('store.show', $vendor->slug) }}#contact">Contact</a>
        </nav>
    </div>
</header>
