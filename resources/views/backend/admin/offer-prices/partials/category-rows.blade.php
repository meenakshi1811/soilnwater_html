@foreach($categories as $category)
    @include('backend.admin.offer-prices.partials.row', [
        'category' => $category,
        'depth' => $depth,
    ])

    @if(($category->children ?? collect())->isNotEmpty())
        @include('backend.admin.offer-prices.partials.category-rows', [
            'categories' => $category->children,
            'depth' => $depth + 1,
        ])
    @endif
@endforeach
