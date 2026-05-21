<aside class="vendor-store-category-sidebar">
    <h2 class="vendor-store-category-sidebar__title">Product categories</h2>
    <ul class="vendor-store-category-list">
        <li>
            <a href="{{ route('store.products.index', $vendor->slug) }}"
               class="vendor-store-category-link {{ empty($activeCategory) && empty($activeSubcategory) ? 'is-active' : '' }}">
                All products
            </a>
        </li>
        @forelse($vendorCategories as $category)
            <li class="vendor-store-category-item">
                <a href="{{ route('store.products.category', [$vendor->slug, $category->id]) }}"
                   class="vendor-store-category-link {{ ($activeCategory?->id ?? null) === $category->id && empty($activeSubcategory) ? 'is-active' : '' }}">
                    <span>{{ $category->name }}</span>
                    @if($category->children->isNotEmpty())
                        <i class="fa-solid fa-chevron-right"></i>
                    @endif
                </a>
                @if(($activeCategory?->id ?? null) === $category->id && $category->children->isNotEmpty())
                    <ul class="vendor-store-subcategory-list">
                        @foreach($category->children as $subcategory)
                            <li>
                                <a href="{{ route('store.products.subcategory', [$vendor->slug, $category->id, $subcategory->id]) }}"
                                   class="vendor-store-subcategory-link {{ ($activeSubcategory?->id ?? null) === $subcategory->id ? 'is-active' : '' }}">
                                    {{ $subcategory->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @empty
            <li><span class="text-muted small px-2">No categories yet</span></li>
        @endforelse
    </ul>
</aside>
