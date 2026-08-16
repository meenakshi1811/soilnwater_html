@php
    $navContext = $navContext ?? 'listing';
    $portalKey = $portalKey ?? $portalType ?? null;
    $resolvedType = $resolvedType ?? $activeType ?? $portalType ?? '';
    $types = $types ?? \App\Support\CommunityContentTaxonomy::formTypes();
    $hubSections = $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
    $isHubPortalView = \App\Support\CommunityContentTaxonomy::isHubPortalKey((string) $portalKey);
    $typeKey = $isHubPortalView && $resolvedType === '' ? '' : ($resolvedType ?: ($isHubPortalView ? '' : (string) $portalKey));
    $activeHub = $activeHub ?? (
        \App\Support\CommunityContentTaxonomy::isHubPortalKey((string) $portalKey)
            ? $portalKey
            : \App\Support\CommunityContentTaxonomy::hubSectionForType($typeKey ?: (string) $portalKey)
    );
    $hubSection = ($activeHub && isset($hubSections[$activeHub])) ? $hubSections[$activeHub] : null;
    $hubLabel = $hubSection['label'] ?? null;
    $hubUrl = $activeHub ? route('community.index', ['hub' => $activeHub]) : null;
    $typeLabel = ($typeKey !== '' && isset($types[$typeKey])) ? $types[$typeKey]['label'] : null;
    $typeListingUrl = $typeKey !== ''
        ? route('community.index', array_filter(['type' => $typeKey, 'hub' => $activeHub]))
        : null;
    $homeUrl = route('home');
    $communityUrl = route('community.index');
    $currentLabel = $currentLabel ?? null;
    $navTheme = $navTheme ?? 'light';
    $showHubCrumb = filled($hubLabel) && $hubUrl !== $communityUrl;
    $listingLinkLabel = $typeLabel ? ('All '.$typeLabel) : ($hubLabel ?: null);
    $listingLinkUrl = $typeListingUrl ?: $hubUrl;
@endphp

<div class="community-portal-nav{{ $navTheme === 'dark' ? ' community-portal-nav--on-dark' : '' }}">
    <nav class="community-portal-nav__breadcrumb news-detail-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ $homeUrl }}">Home</a><span aria-hidden="true">›</span>
        <a href="{{ $communityUrl }}">Community</a>
        @if($showHubCrumb)
            <span aria-hidden="true">›</span>
            @if($navContext === 'listing' && $typeKey === '')
                <span aria-current="page">{{ $hubLabel }}</span>
            @else
                <a href="{{ $hubUrl }}">{{ $hubLabel }}</a>
            @endif
        @endif
        @if($typeLabel)
            <span aria-hidden="true">›</span>
            @if($navContext === 'listing')
                <span aria-current="page">{{ $typeLabel }}</span>
            @else
                <a href="{{ $typeListingUrl }}">{{ $typeLabel }}</a>
            @endif
        @endif
        @if($navContext === 'detail' && filled($currentLabel))
            <span aria-hidden="true">›</span>
            <span aria-current="page">{{ \Illuminate\Support\Str::limit($currentLabel, 48) }}</span>
        @endif
    </nav>

    <div class="community-portal-nav__links" aria-label="Page navigation">
        <a href="{{ $homeUrl }}" class="community-portal-nav__link">
            <i class="fa-solid fa-house" aria-hidden="true"></i>
            <span>Homepage</span>
        </a>
        <a href="{{ $communityUrl }}" class="community-portal-nav__link">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <span>Community</span>
        </a>
        @if($listingLinkUrl && $listingLinkLabel)
            <a href="{{ $listingLinkUrl }}" class="community-portal-nav__link">
                <i class="fa-solid fa-list" aria-hidden="true"></i>
                <span>{{ $listingLinkLabel }}</span>
            </a>
        @endif
    </div>
</div>
