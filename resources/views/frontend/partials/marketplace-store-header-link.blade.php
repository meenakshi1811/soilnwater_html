@php
    $storeUrl = $storeUrl ?? '';
    $linkLabel = $linkLabel ?? 'Store page';
@endphp

@if($storeUrl)
    <div class="vendor-store-header__linkbar">
        <div class="vendor-store-header__link">
            <a href="{{ $storeUrl }}" class="vendor-store-header__link-text" title="{{ $storeUrl }}">{{ $storeUrl }}</a>
            <button
                type="button"
                class="vendor-store-header__link-copy js-copy-store-url"
                data-url="{{ $storeUrl }}"
                aria-label="Copy {{ $linkLabel }} link"
            >
                <i class="fa-regular fa-copy" aria-hidden="true"></i>
                <span class="vendor-store-header__link-copy-label">Copy</span>
            </button>
        </div>
    </div>
@endif
