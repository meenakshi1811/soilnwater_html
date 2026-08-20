@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $visibleMeta = collect($visibleMeta ?? []);
    $formFieldLabels = $formFieldLabels ?? \App\Support\CommunityPostFormFields::labels();
    $resolvedLocation = $resolvedLocation ?? ($post->location ?? data_get($post->meta, 'location'));
    $attachments = $attachments ?? match ($post->content_type) {
        'reports' => data_get($post->meta, 'issue_attachments', []),
        'articles', 'news' => data_get($post->meta, 'news_documents', []),
        'science-technology' => data_get($post->meta, 'science_technology_documents', []),
        default => [],
    };
    $showStructuredLocation = \App\Models\CommunityPost::usesStructuredLocation($post->content_type)
        && ! in_array($post->content_type, ['awareness', 'business', 'womens-world', 'senior-citizens-forum', 'student-corner', 'youth-corner', 'local-voices', 'my-area', 'community-issues', 'agriculture', 'environment', 'science-technology'], true)
        && $post->structuredLocationForDisplay()->isNotEmpty();
    $showLocationType = $post->content_type !== 'poetry' && filled($post->location_type);
    $hasLocation = $showStructuredLocation || $showLocationType || $post->hasMapCoordinates();
    $hasAttachments = is_array($attachments) && $attachments !== [];
    $hasAdditionalDetails = $visibleMeta
        ->except(array_merge(
            \App\Models\CommunityPost::structuredLocationMetaKeys(),
            ['location', 'location_lat', 'location_lng', 'issue_attachments', 'news_documents']
        ))
        ->filter(fn ($value) => filled($value) || $value === false)
        ->reject(fn ($value) => is_array($value) || is_object($value))
        ->isNotEmpty();
    $showReportTrustScore = $post->isReportContent() && $post->reportTrustBreakdown() !== [];
@endphp

@if($showReportTrustScore || $hasAttachments || $hasLocation || $hasAdditionalDetails)
    <div class="community-news-rail__detail-extras" aria-label="Post details sidebar">
        @if($showReportTrustScore)
            <div class="community-news-rail__card community-news-rail__card--detail">
                @include('community.partials.report-trust-score', ['post' => $post, 'compact' => true])
            </div>
        @endif

        @if($hasAttachments)
            <div class="community-news-rail__card community-news-rail__card--detail">
                <h3 class="community-news-rail__title">Attachments</h3>
                <div class="community-news-rail__attachments">
                    @foreach($attachments as $attachment)
                        <a href="{{ data_get($attachment, 'url') }}" target="_blank" rel="noopener" class="community-news-rail__attachment">
                            <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                            <span>{{ data_get($attachment, 'name', 'Attachment') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($hasLocation)
            <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--location community-detail-card--rail">
                <div class="community-detail-card__head">
                    <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <div>
                        <h4 class="community-detail-card__title">Location</h4>
                    </div>
                </div>
                @if($showStructuredLocation)
                    <div class="community-detail-grid community-detail-grid--rail">
                        @foreach($post->structuredLocationForDisplay() as $key => $value)
                            <div class="community-detail-item">
                                <span class="community-detail-item__label">{{ \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type)[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                                <span class="community-detail-item__value">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @elseif($showLocationType)
                    <span class="community-detail-location-type">
                        <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
                        {{ $post->locationTypeLabel() }}
                    </span>
                    @if($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_GLOBAL)
                        <p class="community-detail-location-note mb-0">Global relevance.</p>
                    @elseif($post->location_type === \App\Models\CommunityPost::LOCATION_TYPE_INDIA)
                        <p class="community-detail-location-note mb-0">Applies across India.</p>
                    @elseif($post->usesGpsLocation() && ! $post->hasMapCoordinates())
                        <p class="community-detail-location-note mb-0">No map location provided.</p>
                    @elseif($post->requiresSpecificLocation() && filled($resolvedLocation))
                        <p class="community-detail-location-note mb-0">{{ $resolvedLocation }}</p>
                    @endif
                @endif
                @if($post->hasMapCoordinates())
                    @include('community.partials.location-map-embed', ['post' => $post])
                @endif
            </div>
        @endif

        @if($hasAdditionalDetails)
            <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--meta community-detail-card--rail">
                <div class="community-detail-card__head">
                    <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></span>
                    <div>
                        <h4 class="community-detail-card__title">Additional details</h4>
                    </div>
                </div>
                <dl class="community-detail-list community-detail-list--rail mb-0">
                    @foreach($visibleMeta->except(array_merge(
                        \App\Models\CommunityPost::structuredLocationMetaKeys(),
                        ['location', 'location_lat', 'location_lng', 'issue_attachments', 'news_documents']
                    )) as $key => $value)
                        @if(blank($value) && $value !== false)
                            @continue
                        @endif
                        @if(is_array($value) || is_object($value))
                            @continue
                        @endif
                        <div class="community-detail-list__row">
                            <dt>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                            <dd>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
@endif
