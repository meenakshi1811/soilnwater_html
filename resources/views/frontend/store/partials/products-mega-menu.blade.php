@php
    $vendorCategories = $vendorCategories ?? collect();
    $isProductsSection = in_array($activeNav ?? '', ['products', 'category', 'subcategory'], true);
@endphp

<div class="vendor-store-nav-item vendor-store-products-mega {{ $isProductsSection ? 'is-active-nav' : '' }}">
    <a href="{{ route('store.products.index', $vendor->slug) }}"
       class="vendor-store-nav-link vendor-store-products-trigger"
       aria-haspopup="true"
       aria-expanded="false">
        Products <i class="fa-solid fa-caret-down ms-1" aria-hidden="true"></i>
    </a>

    <div class="vendor-store-mega-panel" role="menu" aria-label="Product categories">
        <div class="vendor-store-mega-panel__head">
            <span>Browse categories</span>
            <a href="{{ route('store.products.index', $vendor->slug) }}">View all products</a>
        </div>
        <div class="vendor-store-mega-panel__body">
            <div class="vendor-store-mega-categories">
                <ul class="list-unstyled mb-0">
                    @forelse($vendorCategories as $category)
                        <li class="vendor-store-mega-cat-item {{ $category->children->isNotEmpty() ? 'has-children' : '' }}"
                            data-mega-cat="{{ $category->id }}">
                            <a href="{{ route('store.products.category', [$vendor->slug, $category->id]) }}"
                               class="vendor-store-mega-cat-link">
                                <span>{{ $category->name }}</span>
                                @if($category->children->isNotEmpty())
                                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li class="px-3 py-2 text-muted small">No categories available</li>
                    @endforelse
                </ul>
            </div>

            <div class="vendor-store-mega-subpanels">
                @foreach($vendorCategories as $category)
                    @if($category->children->isNotEmpty())
                        <div class="vendor-store-mega-subpanel" data-mega-panel="{{ $category->id }}" hidden>
                            <h3 class="vendor-store-mega-subpanel__title">{{ $category->name }}</h3>
                            <ul class="list-unstyled mb-0">
                                @foreach($category->children as $subcategory)
                                    <li>
                                        <a href="{{ route('store.products.subcategory', [$vendor->slug, $category->id, $subcategory->id]) }}">
                                            {{ $subcategory->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('store.products.category', [$vendor->slug, $category->id]) }}" class="vendor-store-mega-subpanel__all">
                                View all in {{ $category->name }}
                            </a>
                        </div>
                    @endif
                @endforeach
                <div class="vendor-store-mega-subpanel vendor-store-mega-subpanel--placeholder is-visible" data-mega-panel="placeholder">
                    <p class="mb-0 text-muted">Select a category to see subcategories, or choose <strong>View all products</strong>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
