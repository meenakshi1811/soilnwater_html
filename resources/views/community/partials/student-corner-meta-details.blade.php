@php
    $includeAdmin = $includeAdmin ?? false;
    $introMetaKeys = \App\Support\CommunityPostFormFields::studentCornerIntroMetaKeys();
    $orderedMeta = $includeAdmin
        ? \App\Support\CommunityPostFormFields::orderedStudentCornerAdminMetaForDisplay($post)
        : \App\Support\CommunityPostFormFields::orderedStudentCornerMetaForDisplay($post)
            ->except($introMetaKeys);
    $metaLabels = $includeAdmin
        ? \App\Support\CommunityPostFormFields::studentCornerAdminMetaOrder()
        : \App\Support\CommunityPostFormFields::studentCornerDetailMetaOrder();
    $pillKeys = [
        'student_corner_target_audience',
        'student_corner_study_material_types',
        'student_corner_career_guidance_topics',
        'student_corner_skills',
        'student_corner_social_impact_categories',
        'student_corner_mentorship_requests',
        'student_corner_competition_categories',
        'student_corner_poll_options',
    ];
    $textareaKeys = [
        'student_corner_project_description',
        'student_corner_project_outcome',
        'student_corner_eligibility',
        'student_corner_preparation_strategy',
        'student_corner_resources_used',
        'student_corner_lessons_learned',
        'student_corner_ask_community',
    ];
    $urlKeys = ['student_corner_official_website'];
    $documents = $post->studentCornerDocuments();
    $achievements = $post->studentCornerAchievements();
    $sidebarLayout = $sidebarLayout ?? false;
@endphp

@if($post->isStudentCornerPost() && ($orderedMeta->isNotEmpty() || (! $sidebarLayout && ($documents !== [] || $achievements !== [])) || $includeAdmin))
    <div @class([
        'about-box mt-4 business-meta-grid' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--student-details' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? ($includeAdmin ? 'Saved Student Corner metadata' : 'Student Corner details') }}</p>
        @else
            <h4>{{ $heading ?? ($includeAdmin ? 'Saved Student Corner metadata' : 'Student Corner details') }}</h4>
        @endif

        @if($includeAdmin)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Publish as</span>
                        <span>{{ \App\Support\CommunityContentTaxonomy::studentCornerPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Visibility</span>
                        <span>{{ $post->studentCornerVisibilityLabel() }}</span>
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
            <div @class([
                'row g-3' => ! $sidebarLayout,
                'news-sidebar-meta-grid' => $sidebarLayout,
            ])>
                @foreach($orderedMeta as $key => $value)
                    @continue(! $includeAdmin && $key === 'student_corner_ask_community')
                    @continue($includeAdmin && $key === 'student_corner_visibility')
                    <div @class([
                        in_array($key, $textareaKeys, true) ? 'col-12' : 'col-md-6' => ! $sidebarLayout,
                        'news-sidebar-meta-grid__item' => $sidebarLayout,
                        'news-sidebar-meta-grid__item--wide' => $sidebarLayout && in_array($key, $textareaKeys, true),
                    ])>
                        <div @class(['business-meta-item' => ! $sidebarLayout, 'border rounded p-3 h-100 bg-light' => $sidebarLayout])>
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true))
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    @foreach(array_filter(array_map('trim', explode(',', (string) $value))) as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @elseif(in_array($key, $textareaKeys, true))
                                <span>{!! nl2br(e($value)) !!}</span>
                            @elseif(in_array($key, $urlKeys, true))
                                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer">{{ $value }}</a>
                            @else
                                <span>{{ $value }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if(! $sidebarLayout && $documents !== [])
            <div class="mt-4">
                <span class="business-meta-item__label d-block mb-2">Project documents</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($documents as $document)
                        <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-file-lines me-1" aria-hidden="true"></i>{{ data_get($document, 'name', 'Document') }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if(! $sidebarLayout && $achievements !== [])
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

        @if($includeAdmin && $post->requiresStudentCornerPrivateLink() && filled($post->studentCornerPrivateLinkUrl()))
            <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                <strong>Private link:</strong> {{ $post->studentCornerPrivateLinkUrl() }}
            </div>
        @endif

        @if($includeAdmin)
            <div class="mt-3">
                <span class="business-meta-item__label d-block mb-2">Allowed reactions</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(\App\Support\CommunityContentTaxonomy::studentCornerReactionOptions() as $reaction => $icon)
                        <span class="badge bg-light text-dark border">
                            <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
