@if($post->isChildrensCornerPost())
    @php
        $shareType = $post->childrensCornerShareType();
        $contentMode = $post->childrensCornerContentMode();
        $childFirstName = data_get($post->meta, 'child_first_name');
        $themes = array_values(array_filter((array) data_get($post->meta, 'childrens_corner_themes', [])));
        $talents = array_values(array_filter((array) data_get($post->meta, 'childrens_corner_talent_categories', [])));
        $achievement = data_get($post->meta, 'childrens_corner_achievement');
        $projectDescription = data_get($post->meta, 'childrens_corner_project_description');
        $art = $post->childrensCornerArtData();
        $gallery = $post->childrensCornerGalleryImages();
        $projectFiles = $post->childrensCornerProjectFiles();
        $quizQuestions = $post->childrensCornerQuizQuestions();
        $certificate = $post->childrensCornerCertificateData();
        $showQuizAnswers = $showQuizAnswers ?? false;
        $placement = $placement ?? 'full';
        $limitedChildInfo = $limitedChildInfo ?? $post->showsLimitedChildInformationTo(auth()->user());
        $sidebarLayout = $sidebarLayout ?? false;
        $railLayout = $railLayout ?? false;
        $portalSidebarLayout = $portalSidebarLayout ?? false;
    @endphp

    @if($sidebarLayout && in_array($placement, ['full', 'intro'], true))
        <div class="community-news-sidebar__card community-news-sidebar__card--childrens-intro">
            <p class="community-news-sidebar__label">Submission overview</p>
            @if(filled($shareType))
                <p class="small mb-2"><strong>Share type:</strong> {{ $shareType }}</p>
            @endif
            @if(filled(data_get($post->meta, 'child_age_group')))
                <p class="small mb-2"><strong>Age group:</strong> {{ data_get($post->meta, 'child_age_group') }}</p>
            @endif
            @if($themes !== [] || $talents !== [])
                <div class="community-news-sidebar__pill-groups">
                    @if($themes !== [])
                        <div class="community-news-sidebar__pill-group">
                            <span class="community-news-sidebar__pill-label">Themes</span>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($themes as $theme)
                                    <span class="badge bg-light text-dark border">{{ $theme }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @elseif($railLayout && in_array($placement, ['full', 'media'], true))
        @include('community.partials.childrens-corner-media-sections', [
            'post' => $post,
            'railLayout' => true,
        ])
    @elseif(! $portalSidebarLayout)

    @if(in_array($placement, ['full', 'intro'], true))
        @if(!$limitedChildInfo && (filled($shareType) || filled(data_get($post->meta, 'child_age_group')) || filled(data_get($post->meta, 'child_grade_level')) || filled(data_get($post->meta, 'child_school_name'))))
            <div class="cc-hero-strip mb-4">
                @if(filled($shareType))
                    <div class="cc-hero-strip__item">
                        <span class="cc-hero-strip__label">Share type</span>
                        <span class="cc-hero-strip__value">{{ $shareType }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'child_age_group')))
                    <div class="cc-hero-strip__item">
                        <span class="cc-hero-strip__label">Age group</span>
                        <span class="cc-hero-strip__value">{{ data_get($post->meta, 'child_age_group') }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'child_grade_level')))
                    <div class="cc-hero-strip__item">
                        <span class="cc-hero-strip__label">Grade</span>
                        <span class="cc-hero-strip__value">{{ data_get($post->meta, 'child_grade_level') }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'child_school_name')))
                    <div class="cc-hero-strip__item">
                        <span class="cc-hero-strip__label">School</span>
                        <span class="cc-hero-strip__value">{{ data_get($post->meta, 'child_school_name') }}</span>
                    </div>
                @endif
            </div>
        @elseif($limitedChildInfo && (filled($shareType) || filled(data_get($post->meta, 'child_age_group'))))
            <div class="cc-hero-strip mb-4">
                @if(filled($shareType))
                    <div class="cc-hero-strip__item">
                        <span class="cc-hero-strip__label">Share type</span>
                        <span class="cc-hero-strip__value">{{ $shareType }}</span>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'child_age_group')))
                    <div class="cc-hero-strip__item">
                        <span class="cc-hero-strip__label">Age group</span>
                        <span class="cc-hero-strip__value">{{ data_get($post->meta, 'child_age_group') }}</span>
                    </div>
                @endif
            </div>
            <div class="alert alert-light border mb-4 py-2 px-3 small">
                <i class="fa-solid fa-shield-halved text-success me-1" aria-hidden="true"></i>
                Privacy protected — limited child information is shown on this public page.
            </div>
        @endif

        @if(!$limitedChildInfo && filled($childFirstName))
            <div class="cc-child-spotlight about-box mb-4">
                <div class="cc-child-spotlight__name">{{ $childFirstName }}'s submission</div>
                <p class="text-muted mb-0 small">A Children's Corner contribution shared with parent/guardian consent.</p>
            </div>
        @elseif($limitedChildInfo)
            <div class="cc-child-spotlight about-box mb-4">
                <div class="cc-child-spotlight__name">Young contributor's submission</div>
                <p class="text-muted mb-0 small">Shared with parent/guardian consent. Personal details are limited for child safety.</p>
            </div>
        @endif

        @if($themes !== [] || $talents !== [])
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-tags" aria-hidden="true"></i>
                    <h4 class="mb-0">Themes &amp; talents</h4>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($themes as $theme)
                        <span class="cc-pill cc-pill--theme">{{ $theme }}</span>
                    @endforeach
                    @foreach($talents as $talent)
                        <span class="cc-pill cc-pill--talent">{{ $talent }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(filled($achievement))
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                    <h4 class="mb-0">Achievement / recognition</h4>
                </div>
                <p class="mb-0">{!! nl2br(e($achievement)) !!}</p>
            </div>
        @endif
    @endif

    @if(in_array($placement, ['full', 'media'], true))
        @include('community.partials.childrens-corner-media-sections', ['post' => $post])
    @endif
    @endif
@endif
