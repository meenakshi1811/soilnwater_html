@php
    $includeAdmin = $includeAdmin ?? false;
    $orderedMeta = \App\Support\CommunityPostFormFields::orderedChildrensCornerMetaForDisplay($post, $includeAdmin);
    if (! $includeAdmin) {
        $orderedMeta = $orderedMeta->except([
            'child_share_type',
            'child_first_name',
            'child_age_group',
            'child_grade_level',
            'child_school_name',
            'childrens_corner_themes',
            'childrens_corner_talent_categories',
            'childrens_corner_achievement',
            'childrens_corner_privacy_setting',
        ]);
    }

    if (! $includeAdmin && ($post->showsLimitedChildInformationTo(auth()->user()))) {
        $orderedMeta = $orderedMeta->except([
            'childrens_corner_city',
            'childrens_corner_district',
            'childrens_corner_state',
            'childrens_corner_submitted_through',
            'childrens_corner_school_competition_entry',
        ]);
    }
    $metaLabels = \App\Support\CommunityPostFormFields::childrensCornerPublicMetaOrder()
        + ($includeAdmin ? \App\Support\CommunityPostFormFields::childrensCornerAdminMetaOrder() : []);
    $locationParts = array_values(array_filter([
        data_get($post->meta, 'childrens_corner_city'),
        data_get($post->meta, 'childrens_corner_district'),
        data_get($post->meta, 'childrens_corner_state'),
    ]));
    if (! $includeAdmin && $post->showsLimitedChildInformationTo(auth()->user())) {
        $locationParts = [];
    }
    $hasLocationInMeta = $orderedMeta->hasAny(['childrens_corner_city', 'childrens_corner_district', 'childrens_corner_state']);
    $sidebarLayout = $sidebarLayout ?? false;
@endphp

@if($post->isChildrensCornerPost() && ($orderedMeta->isNotEmpty() || ($locationParts !== [] && ! $sidebarLayout)))
    <div @class([
        'about-box mt-4 cc-meta-grid' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--childrens-details' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? "Children's Corner details" }}</p>
        @else
            <h4>{{ $heading ?? "Children's Corner details" }}</h4>
        @endif

        @if($orderedMeta->isNotEmpty())
            <div @class([
                'row g-3 mb-0' => ! $sidebarLayout,
                'news-sidebar-meta-grid' => $sidebarLayout,
            ])>
                @foreach($orderedMeta as $key => $value)
                    @continue(in_array($key, ['childrens_corner_city', 'childrens_corner_district', 'childrens_corner_state'], true) && ($locationParts !== [] || $sidebarLayout))
                    <div @class([
                        in_array($key, ['childrens_corner_achievement', 'childrens_corner_themes', 'childrens_corner_talent_categories'], true) ? 'col-12' : 'col-md-6' => ! $sidebarLayout,
                        'news-sidebar-meta-grid__item' => $sidebarLayout,
                        'news-sidebar-meta-grid__item--wide' => $sidebarLayout && in_array($key, ['childrens_corner_achievement', 'childrens_corner_themes', 'childrens_corner_talent_categories'], true),
                    ])>
                        <div class="cc-meta-item">
                            <span class="cc-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, ['childrens_corner_themes', 'childrens_corner_talent_categories'], true))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(array_filter(array_map('trim', explode(',', (string) $value))) as $item)
                                        <span class="cc-pill {{ $key === 'childrens_corner_themes' ? 'cc-pill--theme' : 'cc-pill--talent' }}">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @elseif(is_bool($value))
                                <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">{{ $value ? 'Yes' : 'No' }}</span>
                            @else
                                <span>{!! nl2br(e($value)) !!}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($locationParts !== [] && ! $sidebarLayout)
            <div class="cc-meta-item mt-3">
                <span class="cc-meta-item__label">Broad location</span>
                <span>{{ implode(', ', $locationParts) }}</span>
            </div>
        @endif

        @if($includeAdmin && $post->usesChildFriendlyReactions())
            <div class="mt-3 cc-reaction-preview">
                <span class="cc-meta-item__label d-block mb-2">Allowed reactions</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(\App\Support\CommunityContentTaxonomy::childrensCornerReactionOptions() as $reaction => $icon)
                        <span class="badge bg-light text-dark border">
                            <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
