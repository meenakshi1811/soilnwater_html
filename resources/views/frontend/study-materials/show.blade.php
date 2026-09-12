@extends('frontend.layouts.app')
@section('meta_title', $material->title.' | Study Materials')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $material->description), 150) ?: 'Study material on SoilnWater')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/study-materials.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
    $userReview = $userReview ?? null;
    $selectedRating = (int) old('rating', $userReview?->rating ?: 5);
    $fileMeta = $material->fileTypeMeta();
    $contentPages = $material->contentPages();
    $totalPages = max((int) $material->pages, count($contentPages), 1);
    $shareUrl = $material->publicUrl();
    $shareText = urlencode('Check out this study material: '.$shareUrl);
    $tags = collect($material->tags ?? [])->filter()->values();
    $plainDescription = trim(strip_tags((string) $material->description));
    $typeMeta = \App\Models\StudyMaterial::materialTypeMeta($material->material_type);
    $canAccessContent = $canAccessContent ?? true;
    $paymentState = $paymentState ?? ['mode' => 'free', 'submitted_at' => null, 'last_rejected_note' => null];
    $downloadUrl = auth()->check() ? route('study-materials.download', $material->slug) : route('login');
    $readOnlineUrl = $canAccessContent && $material->canPreviewInline() && $material->fileUrl()
        ? $material->fileUrl()
        : $downloadUrl;
    $avgRating = (float) $material->average_rating;
    $filledStars = (int) round($avgRating);
    $hasBoardSolution = $material->hasBoardSolution();
    $hasSolvedWorksheet = $material->hasSolvedWorksheet();
    $solutionDownloadUrl = auth()->check() ? route('study-materials.solution-download', $material->slug) : route('login');
    $solvedWorksheetDownloadUrl = auth()->check() ? route('study-materials.solved-worksheet-download', $material->slug) : route('login');
@endphp

<div
    class="sm-page sm-show-page"
    id="studyMaterialShowPage"
    data-bookmark-url="{{ route('study-materials.bookmark', $material->slug) }}"
    data-review-url="{{ route('study-materials.review', $material->slug) }}"
    data-total-pages="{{ $totalPages }}"
    data-can-preview="{{ $material->canPreviewInline() ? '1' : '0' }}"
    data-file-url="{{ $material->fileUrl() ?: '' }}"
    data-share-url="{{ $shareUrl }}"
>
    <div class="container-fluid sm-show-container">
        @if(session('status'))
            <div class="alert alert-success sm-show-alert">{{ session('status') }}</div>
        @endif

        <nav class="sm-breadcrumb sm-show-breadcrumb" aria-label="Breadcrumb">
            @foreach($material->breadcrumbTrail() as $crumb)
                @if($loop->last)
                    <span aria-current="page">{{ $crumb['label'] }}</span>
                @else
                    <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    <span aria-hidden="true">›</span>
                @endif
            @endforeach
        </nav>

        <div class="sm-show-layout">
            {{-- Left: Material Contents --}}
            <aside class="sm-show-contents">
                <div class="sm-show-contents__head">
                    <h2><i class="fa-solid fa-bars-staggered" aria-hidden="true"></i> Material Contents</h2>
                    <span class="sm-show-contents__count">{{ number_format($totalPages) }} Pages</span>
                </div>

                <div class="sm-show-contents__list" id="smContentList">
                    @foreach($contentPages as $pageItem)
                        <button
                            type="button"
                            class="sm-show-contents__item js-sm-content-item {{ $loop->first ? 'is-active' : '' }}"
                            data-index="{{ $pageItem['index'] }}"
                            data-page-start="{{ $pageItem['page_start'] }}"
                            data-page-end="{{ $pageItem['page_end'] }}"
                            data-title="{{ $pageItem['title'] }}"
                        >
                            <span class="sm-show-contents__thumb">
                                @if($material->thumbnailUrl())
                                    <img src="{{ $material->thumbnailUrl() }}" alt="">
                                @else
                                    <i class="fa-solid {{ $fileMeta['icon'] }}"></i>
                                @endif
                            </span>
                            <span class="sm-show-contents__meta">
                                <strong>{{ $pageItem['title'] }}</strong>
                                <small>{{ $pageItem['page_label'] }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>

                @if($canAccessContent && $material->fileUrl())
                    <a href="{{ $downloadUrl }}" class="sm-show-contents__download">
                        <i class="fa-solid fa-download" aria-hidden="true"></i> Download Full Notes
                    </a>
                @elseif($material->isPaidNote() && ! $canAccessContent)
                    <button type="button" class="sm-show-contents__download sm-show-contents__download--buy" data-bs-toggle="modal" data-bs-target="#studyMaterialPaymentModal">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i> Buy to unlock
                    </button>
                @else
                    <span class="sm-show-contents__download sm-show-contents__download--muted">
                        <i class="fa-solid fa-book-open" aria-hidden="true"></i> Read online
                    </span>
                @endif
            </aside>

            {{-- Center: Viewer + Tabs --}}
            <div class="sm-show-main">
                <div class="sm-show-viewer-card">
                    <div class="sm-show-viewer-toolbar">
                        <a href="{{ route('study-materials.notes') }}" class="sm-show-viewer-back">
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Back to Library
                        </a>

                        <div class="sm-show-viewer-controls">
                            <button type="button" class="sm-show-viewer-btn js-sm-viewer-zoom-out" title="Zoom out" aria-label="Zoom out">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <span class="sm-show-viewer-page js-sm-viewer-page-label">1 / {{ $totalPages }}</span>
                            <button type="button" class="sm-show-viewer-btn js-sm-viewer-zoom-in" title="Zoom in" aria-label="Zoom in">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                            <button type="button" class="sm-show-viewer-btn js-sm-viewer-fit" title="Fit width" aria-label="Fit width">
                                <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                            </button>
                            <select class="sm-show-viewer-fit-select js-sm-viewer-fit-select" aria-label="Fit mode">
                                <option value="width">Fit Width</option>
                                <option value="page">Fit Page</option>
                            </select>
                        </div>

                        <button type="button" class="sm-show-viewer-fullscreen js-sm-viewer-fullscreen">
                            <i class="fa-solid fa-expand" aria-hidden="true"></i> Full Screen
                        </button>
                    </div>

                    <div class="sm-show-viewer-stage" id="smViewerStage">
                        <button type="button" class="sm-show-viewer-nav sm-show-viewer-nav--prev js-sm-viewer-prev" aria-label="Previous page">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <div class="sm-show-viewer-canvas js-sm-viewer-canvas">
                            @if(! $canAccessContent && $material->isPaidNote())
                                <div class="sm-show-paywall">
                                    <div class="sm-show-paywall__icon"><i class="fa-solid fa-lock"></i></div>
                                    <h3>Paid note — purchase to unlock</h3>
                                    <p>This note costs <strong>{{ $material->formattedPrice() }}</strong>. Pay via UPI, submit your payment proof, and access the full content after admin approval.</p>
                                    @if(($paymentState['mode'] ?? '') === 'login_required')
                                        <a href="{{ route('login') }}" class="sm-btn sm-btn-primary">Login to purchase</a>
                                    @elseif(($paymentState['mode'] ?? '') === 'pending')
                                        <div class="alert alert-warning mb-0">
                                            <i class="fa-solid fa-clock me-1"></i>
                                            Your payment proof is under review.
                                            @if(!empty($paymentState['submitted_at']))
                                                Submitted {{ $paymentState['submitted_at']->diffForHumans() }}.
                                            @endif
                                        </div>
                                    @else
                                        @if(!empty($paymentState['last_rejected_note']))
                                            <div class="alert alert-danger">{{ $paymentState['last_rejected_note'] }}</div>
                                        @endif
                                        <button type="button" class="sm-btn sm-btn-primary" data-bs-toggle="modal" data-bs-target="#studyMaterialPaymentModal">
                                            <i class="fa-solid fa-indian-rupee-sign me-1"></i> Buy for {{ $material->formattedPrice() }}
                                        </button>
                                    @endif
                                </div>
                            @else
                            <div class="sm-show-viewer-content js-sm-viewer-content">
                            @if($material->hasCustomWrittenContent())
                                <article class="sm-show-custom-note ck-content">
                                    {!! $material->customNoteHtml() !!}
                                </article>
                            @elseif($material->canPreviewInline() && $material->fileUrl())
                                @if(str_contains(strtolower((string) $material->file_type), 'pdf'))
                                    <iframe
                                        src="{{ $material->fileUrl() }}#page=1"
                                        title="{{ $material->title }}"
                                        class="sm-show-viewer-frame js-sm-viewer-frame"
                                    ></iframe>
                                @else
                                    <img
                                        src="{{ $material->fileUrl() }}"
                                        alt="{{ $material->title }}"
                                        class="sm-show-viewer-image js-sm-viewer-image"
                                    >
                                @endif
                            @elseif($material->thumbnailUrl())
                                <img
                                    src="{{ $material->thumbnailUrl() }}"
                                    alt="{{ $material->title }}"
                                    class="sm-show-viewer-image js-sm-viewer-image"
                                >
                            @else
                                <div class="sm-show-viewer-placeholder js-sm-viewer-placeholder">
                                    <div class="sm-show-viewer-placeholder__icon sm-show-viewer-placeholder__icon--{{ $fileMeta['tone'] }}">
                                        <i class="fa-solid {{ $fileMeta['icon'] }}"></i>
                                    </div>
                                    <h3 class="js-sm-viewer-section-title">{{ $contentPages[0]['title'] ?? $material->title }}</h3>
                                    <p>{{ $material->topic_chapter ?: $material->subject ?: 'Study material preview' }}</p>
                                    <p class="sm-show-viewer-placeholder__file js-sm-viewer-page-meta">
                                        Page 1 of {{ $totalPages }} · {{ strtoupper((string) $material->file_type) }} · {{ $material->fileSizeLabel() }}
                                    </p>
                                </div>
                            @endif
                            </div>
                            @endif
                        </div>

                        <button type="button" class="sm-show-viewer-nav sm-show-viewer-nav--next js-sm-viewer-next" aria-label="Next page">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="sm-show-tabs-card">
                    <div class="sm-show-tabs" id="materialTabs" role="tablist">
                        <button type="button" class="sm-show-tab is-active" data-tab="description" role="tab">Description</button>
                        <button type="button" class="sm-show-tab" data-tab="details" role="tab">Details</button>
                        @if($hasBoardSolution)
                            <button type="button" class="sm-show-tab" data-tab="solution" role="tab">Board Solution</button>
                        @endif
                        @if($hasSolvedWorksheet)
                            <button type="button" class="sm-show-tab" data-tab="solved-worksheet" role="tab">Solved Worksheet</button>
                        @endif
                        <button type="button" class="sm-show-tab" data-tab="related" role="tab">Related Materials</button>
                        <button type="button" class="sm-show-tab" data-tab="questions" role="tab">Questions</button>
                        <button type="button" class="sm-show-tab" data-tab="reviews" role="tab">
                            Reviews ({{ number_format($material->reviews_count) }})
                        </button>
                    </div>

                    <div class="sm-show-tab-panel is-active" data-panel="description" role="tabpanel">
                        <div class="sm-show-desc-layout">
                            <div class="sm-show-desc-main">
                                @if($plainDescription !== '')
                                    <div class="sm-show-desc-text js-sm-desc-text is-collapsed">
                                        @if(filled($material->description) && strip_tags($material->description) !== $plainDescription)
                                            <div class="sm-richtext">{!! $material->description !!}</div>
                                        @else
                                            <p class="mb-0">{{ $plainDescription }}</p>
                                        @endif
                                    </div>
                                    @if(strlen($plainDescription) > 280)
                                        <button type="button" class="sm-show-desc-toggle js-sm-desc-toggle">
                                            Show More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                @else
                                    <p class="sm-empty mb-0">No description provided for this material.</p>
                                @endif

                                @if($tags->isNotEmpty())
                                    <div class="sm-show-tags">
                                        <h4>Tags</h4>
                                        <div class="sm-show-tags__list">
                                            @foreach($tags as $tag)
                                                <span class="sm-show-tag">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <aside class="sm-show-rate-box">
                                <h4>Rate this material</h4>
                                <p class="sm-show-rate-box__score">
                                    <strong class="js-sm-avg-rating">{{ number_format($avgRating, 1) }}</strong> out of 5
                                    (<span class="js-sm-reviews-count">{{ number_format($material->reviews_count) }}</span> Ratings)
                                </p>
                                <div class="sm-show-rate-box__stars" aria-hidden="true">
                                    @for($star = 1; $star <= 5; $star++)
                                        <i class="fa-solid fa-star{{ $star <= $filledStars ? '' : ' is-empty' }}"></i>
                                    @endfor
                                </div>
                                @auth
                                    <button type="button" class="sm-show-rate-box__btn js-sm-scroll-reviews">Write a Review</button>
                                @else
                                    <a href="{{ route('login') }}" class="sm-show-rate-box__btn">Write a Review</a>
                                @endauth
                            </aside>
                        </div>
                    </div>

                    <div class="sm-show-tab-panel" data-panel="details" role="tabpanel">
                        <ul class="sm-show-detail-list">
                            <li><span>Class / Course</span><strong>{{ $material->class_course ?: '—' }}</strong></li>
                            <li><span>Subject</span><strong>{{ $material->subject ?: '—' }}</strong></li>
                            <li><span>Chapter / Topic</span><strong>{{ $material->topic_chapter ?: '—' }}</strong></li>
                            <li><span>{{ $material->isBoardQuestionPaper() ? 'Board' : 'Board / University' }}</span><strong>{{ $material->board_university ?: '—' }}</strong></li>
                            @if($material->isBoardQuestionPaper())
                                <li><span>Code / Serial No.</span><strong>{{ data_get($material->meta, 'set_code') ?: '—' }}</strong></li>
                            @endif
                            <li><span>Medium</span><strong>{{ $material->medium ?: $material->language ?: '—' }}</strong></li>
                            <li><span>Academic Year</span><strong>{{ $material->academic_year ?: '—' }}</strong></li>
                            <li><span>Exam / Test</span><strong>{{ $material->exam_test ?: '—' }}</strong></li>
                            @if($material->material_type === 'sample_papers' && filled(data_get($material->meta, 'marks_obtained')))
                                <li><span>Marks Obtained</span><strong>{{ data_get($material->meta, 'marks_obtained') }}</strong></li>
                            @endif
                            @if($hasBoardSolution)
                                <li><span>Solution</span><strong>{{ $material->isCustomBoardSolution() ? 'Written solution' : 'Uploaded solution file' }}</strong></li>
                            @endif
                            @if($hasSolvedWorksheet)
                                <li><span>Solved Worksheet</span><strong>{{ $material->isCustomSolvedWorksheet() ? 'Written answer key' : 'Uploaded answer key' }}</strong></li>
                            @endif
                            <li><span>Difficulty</span><strong>{{ $material->difficulty ?: '—' }}</strong></li>
                            <li><span>Pages</span><strong>{{ $material->pages ?: '—' }}</strong></li>
                            <li><span>File Type</span><strong>{{ strtoupper((string) $material->file_type) ?: '—' }}</strong></li>
                            <li><span>File Size</span><strong>{{ $material->fileSizeLabel() }}</strong></li>
                        </ul>
                    </div>

                    @if($hasBoardSolution)
                        <div class="sm-show-tab-panel" data-panel="solution" role="tabpanel">
                            @if(! $canAccessContent)
                                <div class="sm-show-paywall">
                                    <div class="sm-show-paywall__icon"><i class="fa-solid fa-lock"></i></div>
                                    <h3>Solution locked</h3>
                                    <p>Please log in or purchase access to view the board paper solution.</p>
                                    <a href="{{ route('login') }}" class="sm-btn sm-btn-primary">Login to view</a>
                                </div>
                            @elseif($material->isCustomBoardSolution())
                                <article class="sm-show-custom-note ck-content">
                                    {!! $material->customSolutionHtml() !!}
                                </article>
                            @elseif($material->canPreviewSolutionInline() && $material->solutionFileUrl())
                                @if(str_contains((string) $material->solutionFileType(), 'pdf'))
                                    <iframe
                                        src="{{ $material->solutionFileUrl() }}#page=1"
                                        title="Board paper solution"
                                        class="sm-show-viewer-frame"
                                        style="width:100%;min-height:70vh;border:0;border-radius:12px;"
                                    ></iframe>
                                @else
                                    <img
                                        src="{{ $material->solutionFileUrl() }}"
                                        alt="Board paper solution"
                                        class="sm-show-viewer-image"
                                        style="max-width:100%;height:auto;border-radius:12px;"
                                    >
                                @endif
                                <div class="mt-3">
                                    <a href="{{ $solutionDownloadUrl }}" class="sm-btn sm-btn-outline">
                                        <i class="fa-solid fa-download me-1"></i> Download Solution
                                    </a>
                                </div>
                            @else
                                <div class="sm-show-solution-file">
                                    <p class="mb-2"><strong>{{ $material->solutionFileName() ?: 'Solution file' }}</strong></p>
                                    <p class="text-muted mb-3">{{ strtoupper((string) $material->solutionFileType()) }} · {{ $material->solutionFileSizeLabel() }}</p>
                                    <a href="{{ $solutionDownloadUrl }}" class="sm-btn sm-btn-primary">
                                        <i class="fa-solid fa-download me-1"></i> Download Solution
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($hasSolvedWorksheet)
                        <div class="sm-show-tab-panel" data-panel="solved-worksheet" role="tabpanel">
                            @if(! $canAccessContent)
                                <div class="sm-show-paywall">
                                    <div class="sm-show-paywall__icon"><i class="fa-solid fa-lock"></i></div>
                                    <h3>Solved worksheet locked</h3>
                                    <p>Please log in to view the solved worksheet.</p>
                                    <a href="{{ route('login') }}" class="sm-btn sm-btn-primary">Login to view</a>
                                </div>
                            @elseif($material->isCustomSolvedWorksheet())
                                <article class="sm-show-custom-note ck-content">
                                    {!! $material->customSolvedWorksheetHtml() !!}
                                </article>
                            @elseif($material->canPreviewSolvedWorksheetInline() && $material->solvedWorksheetFileUrl())
                                @if(str_contains((string) $material->solvedWorksheetFileType(), 'pdf'))
                                    <iframe
                                        src="{{ $material->solvedWorksheetFileUrl() }}#page=1"
                                        title="Solved worksheet"
                                        class="sm-show-viewer-frame"
                                        style="width:100%;min-height:70vh;border:0;border-radius:12px;"
                                    ></iframe>
                                @else
                                    <img
                                        src="{{ $material->solvedWorksheetFileUrl() }}"
                                        alt="Solved worksheet"
                                        class="sm-show-viewer-image"
                                        style="max-width:100%;height:auto;border-radius:12px;"
                                    >
                                @endif
                                <div class="mt-3">
                                    <a href="{{ $solvedWorksheetDownloadUrl }}" class="sm-btn sm-btn-outline">
                                        <i class="fa-solid fa-download me-1"></i> Download Solved Worksheet
                                    </a>
                                </div>
                            @else
                                <div class="sm-show-solution-file">
                                    <p class="mb-2"><strong>{{ $material->solvedWorksheetFileName() ?: 'Solved worksheet file' }}</strong></p>
                                    <p class="text-muted mb-3">{{ strtoupper((string) $material->solvedWorksheetFileType()) }} · {{ $material->solvedWorksheetFileSizeLabel() }}</p>
                                    <a href="{{ $solvedWorksheetDownloadUrl }}" class="sm-btn sm-btn-primary">
                                        <i class="fa-solid fa-download me-1"></i> Download Solved Worksheet
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="sm-show-tab-panel" data-panel="related" role="tabpanel">
                        <div class="sm-show-related-list">
                            @forelse($related as $item)
                                <a href="{{ $item->publicUrl() }}" class="sm-show-related-item">
                                    <span class="sm-show-related-item__icon sm-show-related-item__icon--{{ $item->fileTypeMeta()['tone'] }}">
                                        <i class="fa-solid {{ $item->fileTypeMeta()['icon'] }}"></i>
                                    </span>
                                    <span>
                                        <strong>{{ $item->title }}</strong>
                                        <small>{{ $item->subject ?: $item->materialTypeLabel() }}</small>
                                    </span>
                                </a>
                            @empty
                                <p class="sm-empty mb-0">No related materials found.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="sm-show-tab-panel" data-panel="questions" role="tabpanel">
                        <div class="sm-show-questions-empty">
                            <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                            <p>Have a question about this material? Ask in the discussion forum.</p>
                            <a href="{{ auth()->check() ? route('discussions.index') : route('login') }}" class="sm-btn sm-btn-outline">
                                Ask a Question
                            </a>
                        </div>
                    </div>

                    <div class="sm-show-tab-panel" data-panel="reviews" role="tabpanel" id="smReviewsPanel">
                        @auth
                            <form id="smReviewForm" class="sm-review-form mb-4" novalidate>
                                @csrf
                                <h4 class="sm-review-form__title">{{ $userReview ? 'Update your review' : 'Write a review' }}</h4>
                                <p class="sm-review-form__hint">Rate this material and share feedback for other learners.</p>

                                <div class="sm-star-picker" role="radiogroup" aria-label="Your rating">
                                    <input type="hidden" name="rating" id="smReviewRating" value="{{ $selectedRating }}">
                                    @foreach (range(1, 5) as $stars)
                                        <button
                                            type="button"
                                            class="sm-star-picker__btn {{ $stars <= $selectedRating ? 'is-active' : '' }}"
                                            data-rating="{{ $stars }}"
                                            aria-label="{{ $stars }} {{ $stars === 1 ? 'star' : 'stars' }}"
                                        >
                                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                                        </button>
                                    @endforeach
                                </div>

                                <label class="form-label mt-3" for="smReviewText">Your feedback</label>
                                <textarea
                                    id="smReviewText"
                                    name="review"
                                    class="form-control"
                                    rows="3"
                                    maxlength="2000"
                                    placeholder="What did you find useful? Any tips for other students?"
                                >{{ old('review', $userReview?->review) }}</textarea>

                                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                                    <small class="text-muted">Click the stars to set your rating.</small>
                                    <button type="submit" class="sm-btn sm-btn-primary" id="smReviewSubmitBtn">
                                        <span class="btn-text">{{ $userReview ? 'Update review' : 'Submit review' }}</span>
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="sm-review-login mb-4">
                                <p class="mb-2">Sign in to leave a review for this material.</p>
                                <a href="{{ route('login') }}" class="sm-btn sm-btn-outline">Login to review</a>
                            </div>
                        @endauth

                        <div id="smReviewsList" @auth data-current-user="{{ auth()->id() }}" @endauth>
                            @forelse($material->reviews as $review)
                                @include('frontend.study-materials.partials.review-item', ['review' => $review])
                            @empty
                                <p class="sm-empty mb-0" id="smReviewsEmpty">No reviews yet. Be the first to share your thoughts.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Summary sidebar --}}
            <aside class="sm-show-summary">
                <div class="sm-show-summary-card">
                    <div class="sm-show-summary__top">
                        <span class="sm-show-summary__file sm-show-summary__file--{{ $fileMeta['tone'] }}">
                            <i class="fa-solid {{ $fileMeta['icon'] }}"></i>
                            <span>{{ $fileMeta['label'] }}</span>
                        </span>
                        @if($material->is_verified || $material->educator?->isVerified())
                            <span class="sm-show-summary__verified"><i class="fa-solid fa-circle-check"></i> Verified</span>
                        @endif
                    </div>

                    <h1 class="sm-show-summary__title">{{ $material->title }}</h1>

                    <div class="sm-show-summary__badges">
                        <span class="sm-show-badge sm-show-badge--type">{{ $material->materialTypeLabel() }}</span>
                        @if($material->isPaidNote())
                            <span class="sm-show-badge sm-show-badge--paid">{{ $material->formattedPrice() }}</span>
                        @elseif($material->is_free)
                            <span class="sm-show-badge sm-show-badge--free">Free</span>
                        @endif
                    </div>

                    <div class="sm-show-summary__stats">
                        <span class="sm-show-summary__stat">
                            <i class="fa-solid fa-star"></i>
                            <strong class="js-sm-avg-rating">{{ number_format($avgRating, 1) }}</strong>
                            (<span class="js-sm-reviews-count">{{ number_format($material->reviews_count) }}</span>)
                        </span>
                        <span class="sm-show-summary__stat">
                            <i class="fa-solid fa-eye"></i>
                            <span class="js-sm-views">{{ \App\Models\StudyMaterial::formatCompactCount($material->views_count) }}</span> Views
                        </span>
                        <span class="sm-show-summary__stat">
                            <i class="fa-solid fa-download"></i>
                            {{ \App\Models\StudyMaterial::formatCompactCount($material->downloads_count) }} Downloads
                        </span>
                        @auth
                            <button
                                type="button"
                                class="sm-show-summary__save js-sm-save {{ $isBookmarked ? 'is-saved' : '' }}"
                                data-label-saved="Saved"
                                data-label-unsaved="Save"
                            >
                                <i class="fa-{{ $isBookmarked ? 'solid' : 'regular' }} fa-bookmark" aria-hidden="true"></i>
                                <span class="js-sm-save-label">{{ $isBookmarked ? 'Saved' : 'Save' }}</span>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="sm-show-summary__save">
                                <i class="fa-regular fa-bookmark" aria-hidden="true"></i> Save
                            </a>
                        @endauth
                    </div>

                    <div class="sm-show-summary__actions">
                        @if($canAccessContent)
                            @if($material->fileUrl())
                                <a href="{{ $downloadUrl }}" class="sm-btn sm-btn-primary sm-show-summary__download">
                                    <i class="fa-solid fa-download" aria-hidden="true"></i> Download Notes
                                </a>
                            @endif
                            @if($readOnlineUrl && ($material->canPreviewInline() || $material->hasCustomWrittenContent()))
                                <a href="{{ $readOnlineUrl }}" class="sm-btn sm-btn-outline sm-show-summary__read" target="_blank" rel="noopener">
                                    Read Online
                                </a>
                            @endif
                        @elseif($material->isPaidNote())
                            @if(($paymentState['mode'] ?? '') === 'login_required')
                                <a href="{{ route('login') }}" class="sm-btn sm-btn-primary w-100">Login to purchase</a>
                            @elseif(($paymentState['mode'] ?? '') === 'pending')
                                <button type="button" class="sm-btn sm-btn-outline w-100" disabled>Payment under review</button>
                            @else
                                <button type="button" class="sm-btn sm-btn-primary w-100" data-bs-toggle="modal" data-bs-target="#studyMaterialPaymentModal">
                                    <i class="fa-solid fa-indian-rupee-sign me-1"></i> Buy for {{ $material->formattedPrice() }}
                                </button>
                            @endif
                        @endif
                    </div>

                    <div class="sm-show-summary__details">
                        <h3>Material Details</h3>
                        <ul>
                            <li><span>Class / Course</span><strong>{{ $material->class_course ?: '—' }}</strong></li>
                            <li><span>Subject</span><strong>{{ $material->subject ?: '—' }}</strong></li>
                            <li><span>Chapter / Topic</span><strong>{{ $material->topic_chapter ?: '—' }}</strong></li>
                            <li><span>{{ $material->isBoardQuestionPaper() ? 'Board' : 'Board / University' }}</span><strong>{{ $material->board_university ?: '—' }}</strong></li>
                            <li><span>Medium</span><strong>{{ $material->medium ?: $material->language ?: '—' }}</strong></li>
                            <li><span>Academic Year</span><strong>{{ $material->academic_year ?: '—' }}</strong></li>
                            <li><span>Pages</span><strong>{{ $material->pages ?: '—' }}</strong></li>
                            <li><span>File Type</span><strong>{{ strtoupper((string) $material->file_type) ?: '—' }}</strong></li>
                            <li><span>File Size</span><strong>{{ $material->fileSizeLabel() }}</strong></li>
                            @if($hasBoardSolution)
                                <li><span>Board Solution</span><strong>{{ $material->isCustomBoardSolution() ? 'Written' : 'File uploaded' }}</strong></li>
                            @endif
                        </ul>
                        @if($hasBoardSolution && $canAccessContent)
                            <button type="button" class="sm-btn sm-btn-outline w-100 mt-2 js-sm-open-solution-tab">
                                <i class="fa-solid fa-file-circle-check me-1"></i> View Board Solution
                            </button>
                        @endif
                    </div>

                    @if($material->educator)
                        <div class="sm-show-summary__uploader">
                            <span>Uploaded By</span>
                            <a href="{{ $material->educator->publicUrl() }}" class="sm-show-uploader">
                                <img src="{{ $material->educator->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="{{ $material->educator->display_name }}">
                                <span>
                                    <strong>
                                        {{ $material->educator->display_name }}
                                        @if($material->educator->isVerified())
                                            <i class="fa-solid fa-circle-check sm-show-uploader__verified" aria-label="Verified"></i>
                                        @endif
                                    </strong>
                                    <small>{{ $material->educator->professional_headline ?: $material->educator->roleLabel() }}</small>
                                </span>
                            </a>
                        </div>
                    @endif

                    <div class="sm-show-summary__upload-date">
                        <span>Upload Date</span>
                        <strong>{{ $material->created_at?->format('j M Y') ?: '—' }}</strong>
                    </div>

                    <div class="sm-show-share">
                        <h3>Share this material</h3>
                        <div class="sm-show-share__icons">
                            <a href="https://wa.me/?text={{ $shareText }}" target="_blank" rel="noopener" class="sm-show-share__btn sm-show-share__btn--whatsapp" title="WhatsApp" aria-label="Share on WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                            <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($material->title) }}" target="_blank" rel="noopener" class="sm-show-share__btn sm-show-share__btn--telegram" title="Telegram" aria-label="Share on Telegram">
                                <i class="fa-brands fa-telegram"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="sm-show-share__btn sm-show-share__btn--facebook" title="Facebook" aria-label="Share on Facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($material->title) }}" target="_blank" rel="noopener" class="sm-show-share__btn sm-show-share__btn--twitter" title="Twitter" aria-label="Share on Twitter">
                                <i class="fa-brands fa-x-twitter"></i>
                            </a>
                            <a href="mailto:?subject={{ urlencode($material->title) }}&body={{ $shareText }}" class="sm-show-share__btn sm-show-share__btn--email" title="Email" aria-label="Share via email">
                                <i class="fa-solid fa-envelope"></i>
                            </a>
                            <button type="button" class="sm-show-share__btn sm-show-share__btn--copy js-sm-copy-link" title="Copy link" aria-label="Copy link">
                                <i class="fa-solid fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

@if($material->isPaidNote() && ! $canAccessContent && ($paymentState['mode'] ?? '') !== 'login_required')
    @include('frontend.study-materials.partials.payment-modal')
@endif
@endsection

@push('scripts')
@include('community.partials.toastr-assets')
<script src="{{ asset('assets/js/study-materials-show.js') }}?v={{ now()->timestamp }}" defer></script>
@if($material->isPaidNote() && ! $canAccessContent && ($paymentState['mode'] ?? '') !== 'login_required')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('studyMaterialPaymentForm');
    var fileInput = document.getElementById('studyMaterialPaymentScreenshot');
    var fileName = document.getElementById('studyMaterialPaymentFileName');
    var preview = document.getElementById('studyMaterialPaymentPreview');
    var previewImage = document.getElementById('studyMaterialPaymentPreviewImage');
    var alertBox = document.getElementById('studyMaterialPaymentAlert');
    var submitBtn = document.getElementById('studyMaterialPaymentSubmitBtn');
    var modalEl = document.getElementById('studyMaterialPaymentModal');

    if (!form) return;

    fileInput?.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;
        if (fileName) {
            fileName.textContent = file.name;
            fileName.classList.remove('d-none');
        }
        if (preview && previewImage) {
            previewImage.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!fileInput?.files?.length) {
            alert('Please upload a payment screenshot.');
            return;
        }

        submitBtn.disabled = true;
        alertBox?.classList.add('d-none');

        fetch(modalEl.dataset.submitUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: new FormData(form),
        }).then(function (response) {
            return response.json().then(function (payload) {
                if (!response.ok) throw new Error(payload.message || 'Unable to submit payment proof.');
                if (window.toastr) window.toastr.success(payload.message || 'Payment proof submitted.');
                window.location.reload();
            });
        }).catch(function (error) {
            if (alertBox) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = error.message || 'Unable to submit payment proof.';
                alertBox.classList.remove('d-none');
            }
            submitBtn.disabled = false;
        });
    });
});
</script>
@endif
@endpush
