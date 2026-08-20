@php
    $includeAdmin = $includeAdmin ?? false;
    $orderedWomensWorldMeta = $includeAdmin
        ? \App\Support\CommunityPostFormFields::orderedWomensWorldAdminMetaForDisplay($post)
        : \App\Support\CommunityPostFormFields::orderedWomensWorldMetaForDisplay($post);
    $womensWorldMetaLabels = $includeAdmin
        ? \App\Support\CommunityPostFormFields::womensWorldAdminMetaOrder()
        : \App\Support\CommunityPostFormFields::womensWorldDetailMetaOrder();
    $textareaKeys = [
        'womens_world_ask_community',
        'womens_world_useful_websites',
        'womens_world_government_schemes',
        'womens_world_training_programs',
        'womens_world_scholarships',
        'womens_world_support_organizations',
    ];
    $urlKeys = [
        'womens_world_website_url',
        'womens_world_vendor_profile_url',
    ];
    $pillKeys = [
        'womens_world_target_audience',
        'womens_world_featured_topics',
        'womens_world_themes',
        'womens_world_support_requests',
        'womens_world_community_groups',
        'womens_world_poll_options',
    ];
    $sidebarLayout = $sidebarLayout ?? false;
@endphp

@if($post->isWomensWorldPost() && ($orderedWomensWorldMeta->isNotEmpty() || $includeAdmin))
    <div @class([
        'about-box mt-4 business-meta-grid' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--womens-details' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? ($includeAdmin ? "Saved Women's World metadata" : "Women's World details") }}</p>
        @else
            <h4>{{ $heading ?? ($includeAdmin ? "Saved Women's World metadata" : "Women's World details") }}</h4>
        @endif

        @if($includeAdmin)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Publish as</span>
                        <span>{{ \App\Support\CommunityContentTaxonomy::womensWorldPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Visibility</span>
                        <span>{{ $post->womensWorldVisibilityLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Tags</span>
                        <span>{{ !empty($post->tags) ? implode(', ', $post->tags) : '—' }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if($orderedWomensWorldMeta->isNotEmpty())
            <div @class([
                'row g-3' => ! $sidebarLayout,
                'news-sidebar-meta-grid' => $sidebarLayout,
            ])>
                @foreach($orderedWomensWorldMeta as $key => $value)
                    @continue($includeAdmin && $key === 'womens_world_visibility')
                    <div @class([
                        in_array($key, $textareaKeys, true) ? 'col-12' : 'col-md-6' => ! $sidebarLayout,
                        'news-sidebar-meta-grid__item' => $sidebarLayout,
                        'news-sidebar-meta-grid__item--wide' => $sidebarLayout && in_array($key, $textareaKeys, true),
                    ])>
                        <div @class(['business-meta-item' => ! $sidebarLayout, 'border rounded p-3 h-100 bg-light' => $sidebarLayout])>
                            <span class="business-meta-item__label">{{ $womensWorldMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true))
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    @foreach(array_filter(array_map('trim', explode(',', (string) $value))) as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @elseif(in_array($key, $urlKeys, true))
                                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer">{{ $value }}</a>
                            @elseif(in_array($key, $textareaKeys, true))
                                <span>{!! nl2br(e($value)) !!}</span>
                            @else
                                <span>{{ $value }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($includeAdmin && $post->requiresWomensWorldPrivateLink() && filled($post->womensWorldPrivateLinkUrl()))
            <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                <strong>Private link:</strong> {{ $post->womensWorldPrivateLinkUrl() }}
            </div>
        @endif

        @if($includeAdmin)
            <div class="mt-3">
                <span class="business-meta-item__label d-block mb-2">Allowed reactions</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(\App\Support\CommunityContentTaxonomy::womensWorldReactionOptions() as $reaction => $icon)
                        <span class="badge bg-light text-dark border">
                            <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
