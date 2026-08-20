@if($post->isStudentCornerPost())
    @php
        $mainCategory = data_get($post->meta, 'student_corner_category') ?: $post->category;
        $contentType = data_get($post->meta, 'student_corner_content_type');
        $profileName = data_get($post->meta, 'student_corner_profile_name');
        $classCourse = data_get($post->meta, 'student_corner_class_course');
        $stream = data_get($post->meta, 'student_corner_stream');
        $institution = data_get($post->meta, 'student_corner_institution_name');
        $audiences = array_values(array_filter((array) data_get($post->meta, 'student_corner_target_audience', [])));
        $studyMaterials = array_values(array_filter((array) data_get($post->meta, 'student_corner_study_material_types', [])));
        $careerTopics = array_values(array_filter((array) data_get($post->meta, 'student_corner_career_guidance_topics', [])));
        $skills = array_values(array_filter((array) data_get($post->meta, 'student_corner_skills', [])));
        $socialImpact = array_values(array_filter((array) data_get($post->meta, 'student_corner_social_impact_categories', [])));
        $mentorshipRequests = array_values(array_filter((array) data_get($post->meta, 'student_corner_mentorship_requests', [])));
        $competitionCategories = array_values(array_filter((array) data_get($post->meta, 'student_corner_competition_categories', [])));
        $submitToCompetition = (bool) data_get($post->meta, 'student_corner_submit_to_competition', false);
        $askCommunity = data_get($post->meta, 'student_corner_ask_community');
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $visibilityLabel = $post->studentCornerVisibilityLabel();
        $publishAsLabel = \App\Support\CommunityContentTaxonomy::studentCornerPublishAsOptions()[$post->resolvedPublishAs()]
            ?? $post->publishAsLabel();
        $sidebarLayout = $sidebarLayout ?? false;
        $railLayout = $railLayout ?? false;
    @endphp

    @if($sidebarLayout || ($overviewRailLayout ?? false))
        @if(filled($mainCategory) || filled($contentType) || filled($classCourse) || filled($stream) || filled($profileName) || filled($institution) || $audiences !== [] || $studyMaterials !== [] || $careerTopics !== [] || $skills !== [])
            <div @class([
                'community-news-sidebar__card community-news-sidebar__card--student-intro' => $sidebarLayout,
                'community-news-rail__card community-news-rail__card--detail' => $overviewRailLayout ?? false,
            ])>
                @if($overviewRailLayout ?? false)
                    <h3 class="community-news-rail__title">Overview</h3>
                @else
                    <p class="community-news-sidebar__label">Overview</p>
                @endif
                <dl class="community-detail-list community-detail-list--rail mb-0">
                    @if(filled($mainCategory))
                        <div class="community-detail-list__row"><dt>Main category</dt><dd>{{ $mainCategory }}</dd></div>
                    @endif
                    @if(filled($contentType))
                        <div class="community-detail-list__row"><dt>Content type</dt><dd>{{ $contentType }}</dd></div>
                    @endif
                    @if(filled($classCourse))
                        <div class="community-detail-list__row"><dt>Class / course</dt><dd>{{ $classCourse }}</dd></div>
                    @endif
                    @if(filled($stream))
                        <div class="community-detail-list__row"><dt>Stream</dt><dd>{{ $stream }}</dd></div>
                    @endif
                    @if(filled($profileName))
                        <div class="community-detail-list__row"><dt>Student</dt><dd>{{ $profileName }}</dd></div>
                    @endif
                    @if(filled($institution))
                        <div class="community-detail-list__row"><dt>Institution</dt><dd>{{ $institution }}</dd></div>
                    @endif
                </dl>
                @if($submitToCompetition)
                    <p class="small text-success mb-0 mt-2"><i class="fa-solid fa-trophy me-1" aria-hidden="true"></i>Competition entry</p>
                @endif
                @if($audiences !== [] || $studyMaterials !== [] || $careerTopics !== [] || $skills !== [])
                    <div class="community-news-sidebar__pill-groups mt-3">
                        @if($audiences !== [])
                            <div class="community-news-sidebar__pill-group">
                                <span class="community-news-sidebar__pill-label">Target audience</span>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($audiences as $audience)
                                        <span class="badge bg-light text-dark border">{{ $audience }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($skills !== [])
                            <div class="community-news-sidebar__pill-group">
                                <span class="community-news-sidebar__pill-label">Skills</span>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($skills as $skill)
                                        <span class="badge bg-light text-dark border">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    @elseif($railLayout)
        @include('community.partials.student-corner-media-sections', ['post' => $post, 'railLayout' => true])
        @include('community.partials.life-learning-portal-rail-engagement', ['post' => $post])
    @elseif(! ($portalSidebarLayout ?? false))

    @if(filled($mainCategory) || filled($contentType) || filled($classCourse) || filled($stream))
        <div class="business-hero-strip mb-4">
            @if(filled($mainCategory))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Main category</span>
                    <span class="business-hero-strip__value">{{ $mainCategory }}</span>
                </div>
            @endif
            @if(filled($contentType))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Content type</span>
                    <span class="business-hero-strip__value">{{ $contentType }}</span>
                </div>
            @endif
            @if(filled($classCourse))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Class / course</span>
                    <span class="business-hero-strip__value">{{ $classCourse }}</span>
                </div>
            @endif
            @if(filled($stream))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Stream</span>
                    <span class="business-hero-strip__value">{{ $stream }}</span>
                </div>
            @endif
            @if($post->studentCornerVisibilitySetting() !== 'public')
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Visibility</span>
                    <span class="business-hero-strip__value">{{ $visibilityLabel }}</span>
                </div>
            @endif
        </div>
    @endif

    @if(filled($profileName) || filled($institution))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-user-graduate" aria-hidden="true"></i>
                <h4 class="mb-0">Student profile</h4>
            </div>
            <div class="row g-3">
                @if(filled($profileName))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Name</span>
                            <span>{{ $profileName }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($institution))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Institution</span>
                            <span>{{ $institution }}</span>
                        </div>
                    </div>
                @endif
                <div class="col-md-6">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Published as</span>
                        <span>{{ $publishAsLabel }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($submitToCompetition)
        <div class="alert alert-success border-success-subtle mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="fa-solid fa-trophy mt-1" aria-hidden="true"></i>
                <div>
                    <strong>Competition entry</strong>
                    <p class="mb-0 small">This post was submitted to a SoilnWater student competition.</p>
                    @if($competitionCategories !== [])
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach($competitionCategories as $category)
                                <span class="badge bg-success text-white">{{ $category }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($audiences !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <h4 class="mb-0">Target audience</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($audiences as $audience)
                    <span class="business-pill">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($studyMaterials !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                <h4 class="mb-0">Study material type</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($studyMaterials as $material)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $material }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($careerTopics !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-compass" aria-hidden="true"></i>
                <h4 class="mb-0">Career guidance</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($careerTopics as $topic)
                    <span class="badge bg-warning-subtle text-dark border">{{ $topic }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($skills !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                <h4 class="mb-0">Skills</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($skills as $skill)
                    <span class="badge bg-light text-dark border">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($socialImpact !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-leaf" aria-hidden="true"></i>
                <h4 class="mb-0">Social impact</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($socialImpact as $category)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $category }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($mentorshipRequests !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-hands-helping" aria-hidden="true"></i>
                <h4 class="mb-0">Mentorship requests</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($mentorshipRequests as $request)
                    <span class="badge bg-info-subtle text-info-emphasis border">{{ $request }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty())
        @unless($portalSidebarLayout ?? false)
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Location</h4>
                    <p class="text-muted small mb-0">Useful for local scholarships, events, and opportunities.</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($structuredLocation as $key => $value)
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endunless
    @endif

    @include('community.partials.student-corner-media-sections', ['post' => $post])

    @if(filled($askCommunity))
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-comments text-primary" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Ask the community</h4>
                    <p class="text-muted small mb-0">Share your experience or advice in the comments below.</p>
                </div>
            </div>
            <blockquote class="mb-0 ps-3 border-start border-primary border-3 fst-italic">"{{ $askCommunity }}"</blockquote>
        </div>
    @endif

    @if(!empty($post->tags))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                <h4 class="mb-0">Tags</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                    <span class="badge bg-light text-dark border">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->allowsPoll() || $post->allow_comments || $post->allow_questions || $post->allow_feedback || $post->allow_sharing)
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                <h4 class="mb-0">Reader engagement</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($post->allowsPoll())
                    <span class="badge bg-primary text-white">Poll open</span>
                @endif
                @if($post->allow_comments)
                    <span class="badge bg-success">Comments</span>
                @endif
                @if($post->allow_questions)
                    <span class="badge bg-success">Questions</span>
                @endif
                @if($post->allow_feedback)
                    <span class="badge bg-success">Peer discussion</span>
                @endif
                @if($post->allow_sharing)
                    <span class="badge bg-success">Sharing</span>
                @endif
            </div>
            @if($post->allowsPoll() && filled(data_get($post->meta, 'student_corner_poll_question')))
                <p class="small text-muted mb-0 mt-3">
                    <strong>Poll:</strong> {{ data_get($post->meta, 'student_corner_poll_question') }}
                </p>
            @endif
        </div>
    @endif

    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-heart" aria-hidden="true"></i>
            <h4 class="mb-0">Community reactions</h4>
        </div>
        <p class="text-muted small mb-2">Positive reactions only.</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::studentCornerReactionOptions() as $reaction => $icon)
                <span class="badge bg-light text-dark border">
                    <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                </span>
            @endforeach
        </div>
    </div>
    @endif
@endif
