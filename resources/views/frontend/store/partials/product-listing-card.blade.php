{{--
  A product on the store's "All products" page, in the listing pages' card style.
  $product, $slug, $categoryNames (category id => name)
--}}
@php
  $image = is_array($product->images) ? collect($product->images)->filter()->first() : null;
  $productUrl = route('store.products.show', ['slug' => $slug, 'product' => $product->id]);
  $finalPrice = (float) $product->final_price;
  $basePrice = (float) $product->base_price;
  $discount = (float) $product->discount_percent;
  $categoryName = $categoryNames[$product->category_id] ?? null;
@endphp
<article class="vendors-compact-card store-product-card">
  <a href="{{ $productUrl }}" class="vendors-compact-card__media store-product-card__media" aria-label="View {{ $product->name }}">
    @if($discount > 0)
      <span class="vendors-compact-card__badge store-product-card__deal">{{ rtrim(rtrim(number_format($discount, 1), '0'), '.') }}% off</span>
    @endif
    @if($image)
      <img src="{{ asset($image) }}" alt="{{ $product->name }}" class="store-product-card__image" loading="lazy" decoding="async">
    @else
      <span class="store-product-card__placeholder" aria-hidden="true"><i class="fa-solid fa-box-open"></i></span>
    @endif
  </a>
  <div class="vendors-compact-card__body">
    @if($product->brand || $categoryName)
      <p class="store-product-card__eyebrow">{{ $product->brand ?: $categoryName }}</p>
    @endif
    <h3 class="vendors-compact-card__name">
      <a href="{{ $productUrl }}">{{ $product->name }}</a>
    </h3>
    @if($finalPrice > 0)
      <p class="store-product-card__price">
        ₹{{ number_format($finalPrice, 2) }}
        @if($basePrice > $finalPrice)
          <del>₹{{ number_format($basePrice, 2) }}</del>
        @endif
      </p>
    @else
      <p class="store-product-card__price store-product-card__price--ask">Price on enquiry</p>
    @endif
    <a href="{{ $productUrl }}" class="store-product-card__btn">View details</a>
  </div>
</article>
