@php
    $price = number_format((float) ($category->offer_price ?? 0), 2, '.', '');
    $isFree = (float) $price <= 0;
    $depth = (int) ($depth ?? 0);
    $isSubcategory = $depth > 0;
    $typeLabel = match (true) {
        $depth === 0 => 'Category',
        $depth === 1 => 'Subcategory',
        default => 'Child category',
    };
@endphp

<div class="offer-price-row {{ $isSubcategory ? 'is-subcategory' : '' }}" data-category-id="{{ $category->id }}" data-depth="{{ $depth }}" style="{{ $isSubcategory ? 'padding-left: ' . (1.2 + ($depth * 1)) . 'rem;' : '' }}">
    <div class="offer-price-row-label">
        @if($isSubcategory)
            <i class="fa-solid fa-turn-up fa-rotate-90 text-muted"></i>
        @else
            <i class="fa-solid fa-folder-tree text-primary"></i>
        @endif
        <div>
            <div class="offer-price-row-name">{{ $category->name }}</div>
            <span class="offer-price-row-type {{ $isSubcategory ? 'is-sub' : '' }}">
                {{ $typeLabel }}
            </span>
        </div>
    </div>

    <div class="offer-price-current">
        <span class="offer-price-current-label">Current</span>
        <span class="offer-price-current-value {{ $isFree ? 'is-free' : '' }}" data-role="display-price">
            {{ $isFree ? 'Free' : '₹'.number_format((float) $price, 2) }}
        </span>
        <span class="offer-price-current-period">per day</span>
    </div>

    <form id="offerPriceForm{{ $category->id }}" class="offer-price-form js-offer-price-form" method="POST" action="{{ route('admin.offer-prices.update', $category) }}">
        @csrf
        @method('PUT')
        <label class="form-label" for="offerPrice{{ $category->id }}">New per-day price</label>
        <div class="input-group">
            <span class="input-group-text">₹</span>
            <input
                type="number"
                min="0"
                step="0.01"
                class="form-control"
                id="offerPrice{{ $category->id }}"
                name="offer_price"
                value="{{ $price }}"
                required
            >
        </div>
    </form>

    <button type="submit" form="offerPriceForm{{ $category->id }}" class="btn btn-primary ems-btn-primary js-offer-price-save">
        <span class="btn-text"><i class="fa-solid fa-floppy-disk me-1"></i> Save</span>
        <span class="btn-loader d-none" aria-hidden="true"></span>
    </button>
</div>
