<aside class="vendor-store-category-sidebar">
    <div class="vendor-store-category-sidebar__head">
        <h2 class="vendor-store-category-sidebar__title">Service Provider categories</h2>
        <span class="vendor-store-category-sidebar__count">{{ $service_providerCategories->count() }} total</span>
    </div>
    <ul class="vendor-store-category-list">
        <li>
            <a href="{{ route('service_provider.public-services.index', $service_provider->slug) }}"
               class="vendor-store-category-link {{ empty($activeCategory) && empty($activeSubcategory) ? 'is-active' : '' }}">
                All services
            </a>
        </li>
        @forelse($service_providerCategories as $category)
            <li class="vendor-store-category-item">
                <a href="{{ route('service_provider.public-services.category', [$service_provider->slug, $category->id]) }}"
                   class="vendor-store-category-link {{ ($activeCategory?->id ?? null) === $category->id && empty($activeSubcategory) ? 'is-active' : '' }}">
                    <span class="vendor-store-category-link__name">{{ $category->name }}</span>
                    <span class="vendor-store-category-link__meta">
                        @if($category->children->isNotEmpty())
                            <span class="vendor-store-pill">{{ $category->children->count() }}</span>
                            <i class="fa-solid fa-chevron-right"></i>
                        @endif
                    </span>
                </a>
                @if(($activeCategory?->id ?? null) === $category->id && $category->children->isNotEmpty())
                    <ul class="vendor-store-subcategory-list">
                        @foreach($category->children as $subcategory)
                            <li>
                                <a href="{{ route('service_provider.public-services.subcategory', [$service_provider->slug, $category->id, $subcategory->id]) }}"
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
