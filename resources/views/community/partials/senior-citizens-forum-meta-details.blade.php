@php
    $includeAdmin = $includeAdmin ?? false;
    $orderedMeta = \App\Support\CommunityPostFormFields::orderedSeniorCitizensForumMetaForDisplay($post);
    $metaLabels = \App\Support\CommunityPostFormFields::seniorCitizensForumDetailMetaOrder();
    $textareaKeys = [
        'senior_citizens_forum_advice_to_youth',
        'senior_citizens_forum_ask_community',
        'senior_citizens_forum_family_background',
        'senior_citizens_forum_traditions',
        'senior_citizens_forum_cultural_practices',
        'senior_citizens_forum_family_values',
    ];
    $pillKeys = [
        'senior_citizens_forum_life_journey_categories',
        'senior_citizens_forum_themes',
        'senior_citizens_forum_community_contributions',
        'senior_citizens_forum_intergenerational_connections',
        'senior_citizens_forum_key_lessons',
    ];
    $achievements = array_values((array) data_get($post->meta, 'senior_citizens_forum_achievements', []));
@endphp

@if($post->isSeniorCitizensForumPost() && ($orderedMeta->isNotEmpty() || $includeAdmin || $achievements !== []))
    <div class="about-box mt-4 business-meta-grid">
        <h4>{{ $heading ?? ($includeAdmin ? 'Saved Senior Citizens Forum metadata' : 'Senior Citizens Forum details') }}</h4>

        @if($includeAdmin)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Visibility</span>
                        <span>{{ $post->seniorCitizensForumVisibilityLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Digital legacy</span>
                        <span>{{ data_get($post->meta, 'senior_citizens_forum_preserve_digital_legacy') ? 'Preserved' : 'Not enabled' }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Achievements</span>
                        <span>{{ count($achievements) }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if($orderedMeta->isNotEmpty())
            <div class="row g-3">
                @foreach($orderedMeta as $key => $value)
                    @continue($includeAdmin && $key === 'senior_citizens_forum_visibility')
                    <div class="{{ in_array($key, $textareaKeys, true) ? 'col-12' : 'col-md-6' }}">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true))
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    @foreach(array_filter(array_map('trim', preg_split('/[,;]/', (string) $value))) as $item)
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

        @if($achievements !== [])
            <div class="mt-4">
                <span class="business-meta-item__label d-block mb-2">Achievement records</span>
                <div class="row g-3">
                    @foreach($achievements as $achievement)
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light h-100 small">
                                <strong class="d-block">{{ data_get($achievement, 'award_name', 'Achievement') }}</strong>
                                @if(filled(data_get($achievement, 'year')))
                                    <div class="text-muted">{{ data_get($achievement, 'year') }}</div>
                                @endif
                                @if(filled(data_get($achievement, 'description')))
                                    <p class="mb-2 mt-2">{{ data_get($achievement, 'description') }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-2">
                                    @if(filled(data_get($achievement, 'photo.url')))
                                        <a href="{{ data_get($achievement, 'photo.url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Photo</a>
                                    @endif
                                    @if(filled(data_get($achievement, 'certificate.url')))
                                        <a href="{{ data_get($achievement, 'certificate.url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Certificate</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($includeAdmin && $post->requiresSeniorCitizensForumPrivateLink() && filled($post->seniorCitizensForumPrivateLinkUrl()))
            <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                <strong>Private link:</strong> {{ $post->seniorCitizensForumPrivateLinkUrl() }}
            </div>
        @endif

        @if($includeAdmin)
            <div class="mt-3">
                <span class="business-meta-item__label d-block mb-2">Allowed reactions</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumReactionOptions() as $reaction => $icon)
                        <span class="badge bg-light text-dark border">
                            <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
