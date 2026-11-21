@extends('frontend.business-profile.layout')

@php
    use App\Models\Category;
    use App\Support\BusinessProfilePage;

    // (Older callers pass only a few featured products)
    $products = collect($products ?? $featuredProducts ?? []);
    $categoryNames = Category::query()
        ->whereIn('id', $products->pluck('category_id')->filter()->unique())
        ->pluck('name', 'id')
        ->all();

    $biz = BusinessProfilePage::for('vendor', $vendor);
    $bizPremiumType = 'vendor';
    $bizSections = $vendor->pageSections;
    $bizOffer = [
        'id' => 'biz-products',
        'navLabel' => 'Products',
        'title' => 'Products',
        'icon' => 'fa-box-open',
        'lead' => 'Products available from '.$biz['name'],
        'count' => $products->count(),
        'chips' => BusinessProfilePage::categoryChips($products, fn ($product) => [$product->category_id, $categoryNames[$product->category_id] ?? null]),
        'partial' => 'frontend.business-profile.partials.product-cards',
        // One sliding row of the newest products; "View all products" opens the full list
        'data' => ['products' => $products->take(12), 'slug' => $vendor->slug, 'categoryNames' => $categoryNames],
        'empty' => 'No products listed yet.',
        'limit' => 12,
        'row' => true,
        'viewAllUrl' => route('store.products.index', $vendor->slug),
        'viewAllLabel' => 'View all products',
    ];
    $bizEnquiry = [
        'action' => route('store.enquiry', $vendor->slug),
    ];
    $bizAds = [
        'fullPage' => $fullPageAds ?? collect(),
        'supporting' => $supportingAds ?? collect(),
        'compactCard' => 'frontend.store.partials.compact-ad-card',
        'recent' => $vendorRecentAds ?? collect(),
        'recentCategoryNames' => $selectedCategoryNamesByVendorAdId ?? [],
        'recentLabel' => 'Vendors',
    ];
@endphp
