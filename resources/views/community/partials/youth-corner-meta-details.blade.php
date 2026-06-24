@php
    $includeAdmin = $includeAdmin ?? false;
    $introMetaKeys = \App\Support\CommunityPostFormFields::youthCornerIntroMetaKeys();
    $orderedMeta = $includeAdmin
        ? \App\Support\CommunityPostFormFields::orderedYouthCornerAdminMetaForDisplay($post)
        : \App\Support\CommunityPostFormFields::orderedYouthCornerMetaForDisplay($post)
            ->except($introMetaKeys);
    $metaLabels = $includeAdmin
        ? \App\Support\CommunityPostFormFields::youthCornerAdminMetaOrder()
        : \App\Support\CommunityPostFormFields::youthCornerDetailMetaOrder();
    $pillKeys = [
        'youth_corner_target_audience',
        'youth_corner_opportunity_types',
        'youth_corner_skills',
        'youth_corner_themes',
        'youth_corner_community_service',
        'youth_corner_networking_options',
        'youth_corner_mentorship_requests',
        'youth_corner_poll_options',
    ];
    $textareaKeys = [
        'youth_corner_business_idea',
        'youth_corner_startup_challenges',
        'youth_corner_startup_lessons',
        'youth_corner_project_description',
        'youth_corner_project_outcome',
        'youth_corner_ask_community',
    ];
    $documents = $post->youthCornerDocuments();
    $achievements = $post->youthCornerAchievements();
@endphp

@if($post->isYouthCornerPost() && ($orderedMeta->isNotEmpty() || $documents !== [] || $achievements !== [] || $includeAdmin))
    <div class="about-box mt-4 business-meta-grid">
        <h4>{{ $heading ?? ($includeAdmin ? 'Saved Youth Corner metadata' : 'Youth Corner details') }}</h4>

        @if($includeAdmin)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Publish as</span>
                        <span>{{ \App\Support\CommunityContentTaxonomy::youthCornerPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Visibility</span>
                        <span>{{ $post->youthCornerVisibilityLabel() }}</span>
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

        @if($orderedMeta->isNotEmpty())
            <div class="row g-3">
                @foreach($orderedMeta as $key => $value)
                    @continue(! $includeAdmin && $key === 'youth_corner_ask_community')
                    @continue($includeAdmin && $key === 'youth_corner_visibility')
                    <div class="{{ in_array($key, $textareaKeys, true) ? 'col-12' : 'col-md-6' }}">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true))
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    @foreach(array_filter(array_map('trim', explode(',', (string) $value))) as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
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

        @if($documents !== [] && $includeAdmin)
            <div class="mt-4">
                <span class="business-meta-item__label d-block mb-2">Documents</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($documents as $document)
                        <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-file-lines me-1" aria-hidden="true"></i>{{ data_get($document, 'name', 'Document') }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($achievements !== [] && $includeAdmin)
            <div class="mt-4">
                <span class="business-meta-item__label d-block mb-2">Certificates &amp; achievements</span>
                <div class="row g-3">
                    @foreach($achievements as $achievement)
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light h-100">
                                <strong>{{ data_get($achievement, 'achievement_title', data_get($achievement, 'title', 'Achievement')) }}</strong>
                                @if(filled(data_get($achievement, 'year')))
                                    <span class="text-muted"> — {{ data_get($achievement, 'year') }}</span>
                                @endif
                                @if(filled(data_get($achievement, 'certificate.url')))
                                    <div class="mt-2">
                                        <a href="{{ data_get($achievement, 'certificate.url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-certificate me-1" aria-hidden="true"></i>View certificate
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($includeAdmin && $post->requiresYouthCornerPrivateLink() && filled($post->youthCornerPrivateLinkUrl()))
            <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                <strong>Private link:</strong> {{ $post->youthCornerPrivateLinkUrl() }}
            </div>
        @endif

        @if($includeAdmin)
            <div class="mt-3">
                <span class="business-meta-item__label d-block mb-2">Allowed reactions</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(\App\Support\CommunityContentTaxonomy::youthCornerReactionOptions() as $reaction => $icon)
                        <span class="badge bg-light text-dark border">
                            <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
