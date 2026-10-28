{{--
  A vendor's products on its profile page: a card each, linking to the product's page.
  $products, $slug, $categoryNames (category id => name)
--}}
@foreach($products as $product)
    @php
        $image = is_array($product->images) ? collect($product->images)->filter()->first() : null;
        $productUrl = route('store.products.show', ['slug' => $slug, 'product' => $product->id]);
        $finalPrice = (float) $product->final_price;
        $basePrice = (float) $product->base_price;
        $discount = (float) $product->discount_percent;
        $eyebrow = $product->brand ?: ($categoryNames[$product->category_id] ?? null);
    @endphp

    <article class="biz-item biz-item--product" data-biz-item data-biz-category="{{ $product->category_id }}">
        <a href="{{ $productUrl }}" class="biz-item__media biz-item__media--contain" aria-label="View {{ $product->name }}">
            @if($image)
                <img src="{{ asset($image) }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
            @else
                <span class="biz-item__placeholder" aria-hidden="true"><i class="fa-solid fa-box-open"></i></span>
            @endif
            @if($discount > 0)
                <span class="biz-item__badge biz-item__badge--deal">{{ rtrim(rtrim(number_format($discount, 1), '0'), '.') }}% off</span>
            @endif
        </a>
        <div class="biz-item__body">
            @if($eyebrow)
                <p class="biz-item__eyebrow">{{ $eyebrow }}</p>
            @endif
            <h3 class="biz-item__title"><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
            @if($finalPrice > 0)
                <p class="biz-item__price">
                    ₹{{ number_format($finalPrice, 2) }}
                    @if($basePrice > $finalPrice)
                        <del>₹{{ number_format($basePrice, 2) }}</del>
                    @endif
                </p>
            @endif
            <a href="{{ $productUrl }}" class="biz-item__btn">View details</a>
        </div>
    </article>
@endforeach
