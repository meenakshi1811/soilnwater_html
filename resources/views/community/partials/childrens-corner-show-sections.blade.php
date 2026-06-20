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
    @endphp

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

        @if($contentMode === 'image' && $art)
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-palette" aria-hidden="true"></i>
                    <h4 class="mb-0">{{ $shareType ?: 'Creative work' }}</h4>
                </div>
                <div class="cc-art-frame">
                    <img src="{{ $art['url'] }}" alt="{{ $post->title }} — {{ $shareType ?: 'artwork' }}" loading="lazy">
                </div>
                @if(filled($art['name'] ?? null))
                    <small class="text-muted d-block mt-2">{{ $art['name'] }}</small>
                @endif
            </div>
        @endif

        @if($contentMode === 'project' && filled($projectDescription))
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-flask" aria-hidden="true"></i>
                    <h4 class="mb-0">Project description</h4>
                </div>
                <div class="community-post-body">{!! $projectDescription !!}</div>
            </div>
        @endif
    @endif

    @if(in_array($placement, ['full', 'media'], true))
        @if($projectFiles->isNotEmpty())
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                    <h4 class="mb-0">Project files</h4>
                </div>
                <ul class="list-unstyled mb-0">
                    @foreach($projectFiles as $file)
                        <li class="mb-2">
                            <a href="{{ data_get($file, 'url') }}" class="text-success fw-semibold" target="_blank" rel="noopener">
                                <i class="fa-solid fa-paperclip me-1" aria-hidden="true"></i>{{ data_get($file, 'name', 'Download file') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($quizQuestions->isNotEmpty())
            <div class="cc-section-panel about-box mb-4" id="childrens-corner-quiz">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                    <h4 class="mb-0">{{ $shareType === 'Puzzle' ? 'Puzzle' : 'Quiz' }}</h4>
                </div>
                @foreach($quizQuestions as $qIndex => $question)
                    <div class="cc-quiz-card mb-3" data-cc-quiz-card>
                        <p class="fw-semibold mb-2">{{ $qIndex + 1 }}. {{ data_get($question, 'question') }}</p>
                        @foreach((array) data_get($question, 'options', []) as $option)
                            @php
                                $optionId = 'cc-quiz-'.$post->id.'-'.$qIndex.'-'.md5((string) $option);
                                $isCorrect = $showQuizAnswers && (string) data_get($question, 'correct_answer') === (string) $option;
                            @endphp
                            @if($showQuizAnswers)
                                <div class="cc-quiz-option {{ $isCorrect ? 'is-correct' : '' }}">
                                    <i class="fa-solid {{ $isCorrect ? 'fa-check-circle text-success' : 'fa-circle text-muted' }}" aria-hidden="true"></i>
                                    <span>{{ $option }}</span>
                                </div>
                            @else
                                <label class="cc-quiz-option mb-0" for="{{ $optionId }}">
                                    <input type="radio" name="cc_quiz_{{ $post->id }}_{{ $qIndex }}" id="{{ $optionId }}" value="{{ $option }}" data-correct="{{ (string) data_get($question, 'correct_answer') === (string) $option ? '1' : '0' }}">
                                    <span>{{ $option }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                @endforeach
                @unless($showQuizAnswers)
                    <button type="button" class="btn btn-success btn-sm" id="ccQuizCheckBtn">Check my answers</button>
                    <p class="small text-muted mt-2 mb-0" id="ccQuizResult" hidden></p>
                @endunless
            </div>
        @endif

        @if($gallery->isNotEmpty())
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-images" aria-hidden="true"></i>
                    <h4 class="mb-0">Gallery</h4>
                </div>
                <div class="cc-gallery-grid">
                    @foreach($gallery as $index => $image)
                        <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="cc-gallery-grid__item">
                            <img src="{{ data_get($image, 'url') }}" alt="{{ $post->title }} — gallery {{ $index + 1 }}" loading="lazy">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($post->childrensCornerYoutubeEmbedUrl() || $post->childrensCornerVideoFileUrl())
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-video" aria-hidden="true"></i>
                    <h4 class="mb-0">Video submission</h4>
                </div>
                @if($post->childrensCornerYoutubeEmbedUrl())
                    <div class="ratio ratio-16x9 rounded overflow-hidden">
                        <iframe
                            src="{{ $post->childrensCornerYoutubeEmbedUrl() }}"
                            title="Video for {{ $post->title }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                @elseif($post->childrensCornerVideoFileUrl())
                    <video controls class="w-100 rounded" preload="metadata">
                        <source src="{{ $post->childrensCornerVideoFileUrl() }}">
                    </video>
                    @if(filled(data_get($post->childrensCornerVideoData(), 'name')))
                        <small class="text-muted d-block mt-2">{{ data_get($post->childrensCornerVideoData(), 'name') }}</small>
                    @endif
                @endif
            </div>
        @endif

        @if($post->childrensCornerAudioUrl())
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-microphone-lines" aria-hidden="true"></i>
                    <h4 class="mb-0">Audio submission</h4>
                </div>
                <p class="text-muted small mb-2">
                    {{ data_get($post->childrensCornerAudioData(), 'type') === 'recording' ? 'Voice recording' : 'Uploaded audio' }}
                    @if(filled(data_get($post->childrensCornerAudioData(), 'name')))
                        — {{ data_get($post->childrensCornerAudioData(), 'name') }}
                    @endif
                </p>
                <audio controls class="w-100" preload="metadata" src="{{ $post->childrensCornerAudioUrl() }}">
                    Your browser does not support embedded audio playback.
                </audio>
            </div>
        @endif

        @if($certificate)
            <div class="cc-section-panel about-box mb-4">
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-certificate" aria-hidden="true"></i>
                    <h4 class="mb-0">Certificate</h4>
                </div>
                <div class="cc-certificate-card">
                    <div>
                        <strong>{{ $certificate['name'] ?? 'Award / participation certificate' }}</strong>
                        <p class="text-muted small mb-0 mt-1">Uploaded certificate for this submission.</p>
                    </div>
                    @if(filled($certificate['url'] ?? null))
                        @if(str_starts_with((string) ($certificate['type'] ?? ''), 'image/'))
                            <img src="{{ $certificate['url'] }}" alt="Certificate" class="rounded border" style="max-height:180px;">
                        @else
                            <a href="{{ $certificate['url'] }}" class="btn btn-outline-success btn-sm" target="_blank" rel="noopener">View certificate</a>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    @endif
@endif
