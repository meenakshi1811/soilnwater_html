@php
    $moduleConfig = match ($module ?? 'vendors') {
        'consultants' => [
            'label' => 'Consultants',
            'route' => 'frontend.consultants.index',
        ],
        'services' => [
            'label' => 'Services',
            'route' => 'frontend.service_providers.index',
        ],
        default => [
            'label' => 'Vendors',
            'route' => 'frontend.vendors.index',
        ],
    };
@endphp

<nav class="marketplace-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('frontend.index') }}" class="marketplace-breadcrumb__link">
        <i class="fa-solid fa-house" aria-hidden="true"></i>
        <span>Home</span>
    </a>
    <span class="marketplace-breadcrumb__sep" aria-hidden="true">›</span>
    @if (filled($current ?? null))
        <a href="{{ route($moduleConfig['route']) }}" class="marketplace-breadcrumb__link">{{ $moduleConfig['label'] }}</a>
        <span class="marketplace-breadcrumb__sep" aria-hidden="true">›</span>
        <span class="marketplace-breadcrumb__current" aria-current="page">{{ $current }}</span>
    @else
        <span class="marketplace-breadcrumb__current" aria-current="page">{{ $moduleConfig['label'] }}</span>
    @endif
</nav>
