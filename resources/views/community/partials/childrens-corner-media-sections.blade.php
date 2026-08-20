@if($post->isChildrensCornerPost())
    @php
        $shareType = $post->childrensCornerShareType();
        $contentMode = $post->childrensCornerContentMode();
        $projectDescription = data_get($post->meta, 'childrens_corner_project_description');
        $art = $post->childrensCornerArtData();
        $gallery = $post->childrensCornerGalleryImages();
        $projectFiles = $post->childrensCornerProjectFiles();
        $quizQuestions = $post->childrensCornerQuizQuestions();
        $certificate = $post->childrensCornerCertificateData();
        $showQuizAnswers = $showQuizAnswers ?? false;
        $railLayout = $railLayout ?? false;
        $panelClass = $railLayout ? 'community-news-rail__card community-news-rail__card--detail mb-3' : 'cc-section-panel about-box mb-4';
    @endphp

    @if($contentMode === 'image' && $art)
        <div class="{{ $panelClass }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">{{ $shareType ?: 'Creative work' }}</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-palette" aria-hidden="true"></i>
                    <h4 class="mb-0">{{ $shareType ?: 'Creative work' }}</h4>
                </div>
            @endif
            <div class="cc-art-frame">
                <img src="{{ $art['url'] }}" alt="{{ $post->title }} — {{ $shareType ?: 'artwork' }}" loading="lazy">
            </div>
        </div>
    @endif

    @if($contentMode === 'project' && filled($projectDescription))
        <div class="{{ $panelClass }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">Project description</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-flask" aria-hidden="true"></i>
                    <h4 class="mb-0">Project description</h4>
                </div>
            @endif
            <div class="community-post-body">{!! $projectDescription !!}</div>
        </div>
    @endif

    @if($projectFiles->isNotEmpty())
        <div class="{{ $panelClass }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">Project files</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                    <h4 class="mb-0">Project files</h4>
                </div>
            @endif
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
        <div class="{{ $panelClass }}" @unless($railLayout) id="childrens-corner-quiz" @endunless>
            @if($railLayout)
                <h3 class="community-news-rail__title">{{ $shareType === 'Puzzle' ? 'Puzzle' : 'Quiz' }}</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                    <h4 class="mb-0">{{ $shareType === 'Puzzle' ? 'Puzzle' : 'Quiz' }}</h4>
                </div>
            @endif
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
        <div class="{{ $panelClass }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">Gallery</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-images" aria-hidden="true"></i>
                    <h4 class="mb-0">Gallery</h4>
                </div>
            @endif
            <div class="cc-gallery-grid cc-gallery-grid--rail">
                @foreach($gallery as $index => $image)
                    <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="cc-gallery-grid__item">
                        <img src="{{ data_get($image, 'url') }}" alt="{{ $post->title }} — gallery {{ $index + 1 }}" loading="lazy">
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->childrensCornerYoutubeEmbedUrl() || $post->childrensCornerVideoFileUrl())
        <div class="{{ $panelClass }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">Video submission</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-video" aria-hidden="true"></i>
                    <h4 class="mb-0">Video submission</h4>
                </div>
            @endif
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
            @endif
        </div>
    @endif

    @if($post->childrensCornerAudioUrl())
        <div class="{{ $panelClass }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">Audio submission</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-microphone-lines" aria-hidden="true"></i>
                    <h4 class="mb-0">Audio submission</h4>
                </div>
            @endif
            <audio controls class="w-100" preload="metadata" src="{{ $post->childrensCornerAudioUrl() }}"></audio>
        </div>
    @endif

    @if($certificate)
        <div class="{{ $panelClass }}">
            @if($railLayout)
                <h3 class="community-news-rail__title">Certificate</h3>
            @else
                <div class="cc-section-panel__header">
                    <i class="fa-solid fa-certificate" aria-hidden="true"></i>
                    <h4 class="mb-0">Certificate</h4>
                </div>
            @endif
            <div class="cc-certificate-card">
                <strong>{{ $certificate['name'] ?? 'Award / participation certificate' }}</strong>
                @if(filled($certificate['url'] ?? null))
                    <div class="mt-2">
                        <a href="{{ $certificate['url'] }}" class="btn btn-outline-success btn-sm" target="_blank" rel="noopener">View certificate</a>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
