@extends('backend.layouts.app')

@section('title', 'Manage Post')

@section('content')
@if($post->content_type === 'stories')
    @push('styles')
        @include('community.partials.story-styles')
    @endpush
@endif
@if($post->content_type === 'poetry')
    @push('styles')
        @include('community.partials.poetry-styles')
    @endpush
@endif
@if($post->content_type === 'autobiography')
    @push('styles')
        @include('community.partials.autobiography-styles')
    @endpush
@endif
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <p class="ems-kicker mb-1">{{ $post->typeLabel() }} · {{ $post->statusLabel() }}</p>
                <h2 class="admin-title mb-1">{{ $post->title }}</h2>
                <p class="mb-0 text-secondary">Manage reader engagement, review metadata, and respond to questions.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($post->isPubliclyVisible())
                    <a href="{{ route('community.show', $post) }}" class="btn btn-outline-primary" target="_blank" rel="noopener">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Public page
                    </a>
                @endif
                <a href="{{ route('community.posts.edit', $post) }}" class="btn btn-success">
                    <i class="fa-solid fa-pen me-1"></i>Edit post
                </a>
                <a href="{{ route('community.posts.index') }}" class="btn btn-outline-secondary">Back to posts</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            @if($post->content_type === 'news')
                @include('community.partials.news-meta-details', ['post' => $post, 'heading' => 'Saved news metadata'])
            @endif

            @if($post->content_type === 'stories')
                @include('community.partials.story-meta-details', ['post' => $post, 'heading' => 'Saved story metadata'])
                @include('community.partials.story-rating-summary', ['post' => $post, 'compact' => true])
                @include('community.partials.story-achievements-panel', ['post' => $post, 'compact' => true, 'wrapperClass' => 'chart-card p-3 p-lg-4 mb-4'])
            @endif

            @if($post->content_type === 'poetry')
                @include('community.partials.poetry-show-sections', ['post' => $post])
                @include('community.partials.poetry-meta-details', ['post' => $post, 'heading' => 'Saved poetry metadata'])
                @include('community.partials.story-rating-summary', ['post' => $post, 'compact' => true])
            @endif

            @if($post->content_type === 'autobiography')
                @include('community.partials.autobiography-show-sections', ['post' => $post])
                @if($post->usesChapterLayoutForDisplay())
                    @include('community.partials.book-reader', ['post' => $post])
                @endif
                @include('community.partials.autobiography-after-content', ['post' => $post])
                @include('community.partials.autobiography-meta-details', ['post' => $post, 'heading' => 'Saved autobiography metadata'])
                @include('community.partials.story-rating-summary', ['post' => $post, 'compact' => true])
            @endif

            <div class="chart-card p-3 p-lg-4 mb-4">
                <h5 class="mb-3">Comments &amp; discussion settings</h5>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-1"><strong>Comments:</strong> {{ $post->allow_comments ? 'Enabled' : 'Disabled' }}</li>
                    <li class="mb-1"><strong>Questions:</strong> {{ $post->allow_questions ? 'Enabled' : 'Disabled' }}</li>
                    <li class="mb-0"><strong>Suggestions:</strong> {{ $post->allow_suggestions ? 'Enabled' : 'Disabled' }}</li>
                </ul>
            </div>

            @if($post->allow_comments)
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Comments ({{ $engagementSummary['comments'] }})</h5>
                    @forelse($post->discussionComments as $comment)
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                                <strong>{{ $comment->user?->full_name ?: ($comment->user?->name ?? 'Community member') }}</strong>
                                <small class="text-muted">{{ $comment->created_at?->diffForHumans() }}</small>
                            </div>
                            <p class="mb-2">{!! nl2br(e($comment->body)) !!}</p>
                            @foreach($comment->replies as $reply)
                                <div class="border-start ps-3 ms-2 mb-2">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap mb-1">
                                        <strong class="small">{{ $reply->user?->full_name ?: ($reply->user?->name ?? 'Community member') }}</strong>
                                        <small class="text-muted">{{ $reply->created_at?->diffForHumans() }}</small>
                                    </div>
                                    <p class="small mb-0">{!! nl2br(e($reply->body)) !!}</p>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-muted mb-0">No comments yet.</p>
                    @endforelse
                </div>
            @endif

            @if($post->allow_suggestions && ($participationSuggestions ?? collect())->isNotEmpty())
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Suggestions ({{ $engagementSummary['suggestions'] }})</h5>
                    <div class="d-flex flex-column gap-2">
                        @foreach($participationSuggestions as $entry)
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex justify-content-between gap-2 flex-wrap mb-1">
                                    <strong>{{ $entry->user?->full_name ?: ($entry->user?->name ?? 'Community member') }}</strong>
                                    <small class="text-muted">{{ $entry->created_at?->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0">{!! nl2br(e($entry->body)) !!}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($post->allow_questions && $pendingAuthorQuestions->isNotEmpty())
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="mb-0">Pending questions ({{ $engagementSummary['pending_questions'] }})</h5>
                        <a href="{{ route('community.author-questions.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-success">Answer in portal</a>
                    </div>
                    @foreach($pendingAuthorQuestions as $question)
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between gap-2 flex-wrap mb-1">
                                <strong>{{ $question->asker?->full_name ?: ($question->asker?->name ?? 'Reader') }}</strong>
                                <small class="text-muted">{{ $question->created_at?->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0">{!! nl2br(e($question->question)) !!}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($post->allow_questions && $answeredAuthorQuestions->isNotEmpty())
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Answered questions</h5>
                    @foreach($answeredAuthorQuestions as $question)
                        <div class="border rounded p-3 mb-3 bg-white">
                            <div class="small text-muted mb-1">{{ $question->asker?->full_name ?: ($question->asker?->name ?? 'Reader') }} · {{ $question->answered_at?->diffForHumans() }}</div>
                            <p class="mb-2"><strong>Q:</strong> {!! nl2br(e($question->question)) !!}</p>
                            <p class="mb-0 text-success"><strong>A:</strong> {!! nl2br(e($question->answer)) !!}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="chart-card p-3 p-lg-4 mb-4">
                <h5 class="mb-3">Engagement summary</h5>
                <div class="row g-2 small">
                    <div class="col-6"><strong>Comments</strong><div>{{ $engagementSummary['comments'] }}</div></div>
                    <div class="col-6"><strong>Suggestions</strong><div>{{ $engagementSummary['suggestions'] }}</div></div>
                    <div class="col-6"><strong>Questions</strong><div>{{ $engagementSummary['questions'] }}</div></div>
                    <div class="col-6"><strong>Pending Q</strong><div>{{ $engagementSummary['pending_questions'] }}</div></div>
                </div>
            </div>

            <div class="chart-card p-3 p-lg-4 mb-4">
                <h5 class="mb-3">Post status</h5>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-1"><strong>Category:</strong> {{ $post->category }}</li>
                    <li class="mb-1"><strong>Published:</strong> {{ $post->published_at?->timezone(config('app.timezone'))->format('j M Y, g:i A') ?? 'Not published' }}</li>
                    <li class="mb-1"><strong>Submitted:</strong> {{ $post->submitted_at?->timezone(config('app.timezone'))->format('j M Y, g:i A') ?? '—' }}</li>
                    <li class="mb-0"><strong>Views:</strong> {{ number_format($post->views_count ?? 0) }}</li>
                </ul>
            </div>

            @if($post->content_type === 'news' && filled(data_get($post->meta, 'news_impact_level')))
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Community impact</h5>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1"><strong>Impact level:</strong> {{ data_get($post->meta, 'news_impact_level') }}</li>
                        <li class="mb-1"><strong>Affected group:</strong> {{ data_get($post->meta, 'news_affected_group', '—') }}</li>
                        <li class="mb-0"><strong>Priority:</strong> {{ data_get($post->meta, 'news_priority', '—') }}</li>
                    </ul>
                </div>
            @endif

            @if($post->content_type === 'stories')
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Story engagement</h5>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Ratings</strong><div>{{ number_format($post->star_ratings_count ?? 0) }}</div></div>
                        <div class="col-6"><strong>Avg. stars</strong><div>{{ $post->averageStarRating() ? number_format($post->averageStarRating(), 1) : '—' }}</div></div>
                        <div class="col-6"><strong>Shares</strong><div>{{ number_format($post->shares_count ?? 0) }}</div></div>
                        <div class="col-6"><strong>Views</strong><div>{{ number_format($post->views_count ?? 0) }}</div></div>
                    </div>
                    @if($post->storyAchievementBadges() !== [])
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach($post->storyAchievementBadges() as $badge)
                                <span class="badge bg-light text-dark border community-story-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if($post->content_type === 'poetry')
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Poetry engagement</h5>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Ratings</strong><div>{{ number_format($post->star_ratings_count ?? 0) }}</div></div>
                        <div class="col-6"><strong>Avg. stars</strong><div>{{ $post->averageStarRating() ? number_format($post->averageStarRating(), 1) : '—' }}</div></div>
                        <div class="col-6"><strong>Audio</strong><div>{{ $post->poetryAudioUrl() ? 'Yes' : 'No' }}</div></div>
                        <div class="col-6"><strong>Views</strong><div>{{ number_format($post->views_count ?? 0) }}</div></div>
                    </div>
                    @if(filled(data_get($post->meta, 'poetry_themes')))
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach((array) data_get($post->meta, 'poetry_themes', []) as $theme)
                                <span class="badge bg-light text-dark border">{{ $theme }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if($post->content_type === 'autobiography')
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Autobiography engagement</h5>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Ratings</strong><div>{{ number_format($post->star_ratings_count ?? 0) }}</div></div>
                        <div class="col-6"><strong>Avg. stars</strong><div>{{ $post->averageStarRating() ? number_format($post->averageStarRating(), 1) : '—' }}</div></div>
                        <div class="col-6"><strong>Chapters</strong><div>{{ count($post->bookPages()) }}</div></div>
                        <div class="col-6"><strong>Timeline</strong><div>{{ count((array) data_get($post->meta, 'life_timeline', [])) }}</div></div>
                        <div class="col-6"><strong>Audio</strong><div>{{ $post->autobiographyAudioUrl() ? 'Yes' : 'No' }}</div></div>
                        <div class="col-6"><strong>Views</strong><div>{{ number_format($post->views_count ?? 0) }}</div></div>
                    </div>
                    @if(filled(data_get($post->meta, 'autobiography_type')))
                        <div class="mt-3">
                            <span class="badge bg-light text-dark border">{{ data_get($post->meta, 'autobiography_type') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <div class="chart-card p-3 p-lg-4">
                <h5 class="mb-3">Quick links</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('community.author-questions.index') }}" class="btn btn-outline-success btn-sm">Reader questions inbox</a>
                    @if($post->isPubliclyVisible())
                        <a href="{{ route('community.show', $post) }}#comments-discussion" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">Jump to public discussion</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
