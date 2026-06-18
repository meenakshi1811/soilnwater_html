@extends('backend.layouts.app')

@section('title', 'Manage Community Post')

@push('styles')
<style>
    .community-review-preview {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        min-height: 720px;
        width: 100%;
    }

    .community-review-meta .label {
        color: #64748b;
        font-size: .8rem;
        margin-bottom: .2rem;
        text-transform: uppercase;
    }

    .report-trust-score {
        background: linear-gradient(135deg, #f8fafc 0%, #eef6ff 100%);
        border: 1px solid #cfe0f5;
        border-radius: 12px;
        padding: 1rem 1.1rem;
    }

    .report-trust-score--high {
        border-color: #86efac;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    }

    .report-trust-score--medium {
        border-color: #fcd34d;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }

    .report-trust-score__header {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .report-trust-score__kicker {
        color: #0f766e;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .report-trust-score__title {
        font-size: 1rem;
        font-weight: 700;
    }

    .report-trust-score__value-wrap {
        align-items: center;
        background: #fff;
        border: 2px solid #0f766e;
        border-radius: 999px;
        color: #0f766e;
        display: inline-flex;
        min-width: 5rem;
        justify-content: center;
        padding: 0.25rem 0.85rem;
    }

    .report-trust-score__value {
        font-size: 1.1rem;
        font-weight: 800;
        line-height: 1;
    }

    .report-trust-score__factor {
        align-items: flex-start;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
        display: grid;
        gap: 0.65rem;
        grid-template-columns: auto 1fr auto;
        padding: 0.65rem 0;
    }

    .report-trust-score__factor-icon {
        color: #94a3b8;
    }

    .report-trust-score__factor.is-met .report-trust-score__factor-icon {
        color: #16a34a;
    }

    .report-trust-score__factor-points {
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .community-admin-action-group {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        padding: 0.9rem 1rem;
    }

    .community-admin-action-group + .community-admin-action-group {
        margin-top: 0.75rem;
    }
</style>
@include('community.partials.story-styles')
@if($post->content_type === 'poetry')
@include('community.partials.poetry-styles')
@endif
@if($post->content_type === 'autobiography')
@include('community.partials.autobiography-styles')
@endif
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <p class="ems-kicker mb-1">Community moderation</p>
            <h2 class="admin-title mb-1">Manage Post</h2>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge {{ $post->statusBadgeClass() }}">{{ $post->statusLabel() }}</span>
                @foreach($post->adminPromotionLabels() as $label)
                    <span class="badge bg-light text-dark border">{{ $label }}</span>
                @endforeach
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.community-posts.all.index') }}" class="btn btn-outline-secondary">All Posts</a>
            @if($post->isPendingApproval())
                <a href="{{ route('admin.community-posts.index') }}" class="btn btn-outline-secondary">Approval Queue</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="community-admin-action-group">
                <h6 class="mb-2">Workflow actions</h6>
                <div class="d-flex flex-wrap gap-2">
                    @if(! $post->isArchived())
                        <button type="button" class="btn btn-success js-approve" data-slug="{{ $post->slug }}">Approve</button>
                        <button type="button" class="btn btn-outline-danger js-reject" data-slug="{{ $post->slug }}">Reject</button>
                    @endif
                    @if($post->status !== \App\Models\CommunityPost::STATUS_DRAFT && ! $post->isArchived())
                        <button type="button" class="btn btn-outline-secondary js-draft" data-slug="{{ $post->slug }}">Draft</button>
                    @endif
                    @if(! $post->isArchived())
                        <button type="button" class="btn btn-outline-dark js-archive" data-slug="{{ $post->slug }}">Archive</button>
                    @endif
                    @if($post->isArchived())
                        <button type="button" class="btn btn-success js-approve" data-slug="{{ $post->slug }}">Restore &amp; Publish</button>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="community-admin-action-group h-100">
                <h6 class="mb-2">Promotion flags</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="btn btn-sm {{ $post->is_featured ? 'btn-primary' : 'btn-outline-primary' }} js-feature"
                        data-slug="{{ $post->slug }}"
                        data-enabled="{{ $post->is_featured ? '1' : '0' }}"
                    >Feature</button>
                    <button
                        type="button"
                        class="btn btn-sm {{ $post->is_sponsored ? 'btn-info' : 'btn-outline-info' }} js-sponsor"
                        data-slug="{{ $post->slug }}"
                        data-enabled="{{ $post->is_sponsored ? '1' : '0' }}"
                    >Sponsor</button>
                    <button
                        type="button"
                        class="btn btn-sm {{ $post->is_highlighted ? 'btn-warning' : 'btn-outline-warning' }} js-highlight"
                        data-slug="{{ $post->slug }}"
                        data-enabled="{{ $post->is_highlighted ? '1' : '0' }}"
                    >Highlight</button>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="communityReviewTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab">Post details</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-pane" type="button" role="tab">Frontend preview</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="details-pane" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="chart-card p-3 p-lg-4 h-100">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <h3 class="mb-1">{{ $post->title }}</h3>
                                <div class="text-muted small">
                                    {{ $post->typeLabel() }} · {{ $post->content_type === 'reports' ? $post->listingCategoryLabel() : $post->category }}
                                </div>
                                @if($post->content_type === 'reports' && filled($post->reportStatus()))
                                    <span class="badge {{ $post->reportStatusBadgeClass() }} mt-2">{{ $post->reportStatus() }}</span>
                                @endif
                                @if($post->isReportContent())
                                    <span class="badge bg-success mt-2">Trust Score: {{ $post->reportTrustScore() }}%</span>
                                @endif
                            </div>
                        </div>

                        @if($post->excerpt)
                            <h5 class="mb-2">Excerpt</h5>
                            <p class="text-secondary">{{ $post->excerpt }}</p>
                        @endif

                        <h5 class="mb-2">Content</h5>
                        @if($post->usesBookLayout() && $post->bookPages() !== [])
                            @include('community.partials.book-reader', ['post' => $post])
                        @else
                            <div class="border rounded p-3 bg-white community-review-body">
                                {!! $post->body !!}
                            </div>
                        @endif

                        @if($post->featuredImageUrls() !== [])
                            <h5 class="mt-4 mb-2">Featured images</h5>
                            <div class="row g-2">
                                @foreach($post->featuredImageUrls() as $imageUrl)
                                    <div class="col-md-4">
                                        <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="img-fluid rounded border">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($post->hasVideo())
                            <h5 class="mt-4 mb-2">Video</h5>
                            @if($post->youtubeEmbedUrl())
                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                    <iframe src="{{ $post->youtubeEmbedUrl() }}" title="Video preview" allowfullscreen></iframe>
                                </div>
                            @elseif($post->videoFileUrl())
                                <video controls class="w-100 rounded border" preload="metadata">
                                    <source src="{{ $post->videoFileUrl() }}">
                                </video>
                            @endif
                        @endif

                        @if($post->isReportContent())
                            <div class="mb-4">
                                @include('community.partials.report-trust-score', ['post' => $post])
                            </div>
                            @include('community.partials.report-community-actions', [
                                'post' => $post,
                                'reportEngagement' => $reportEngagement,
                            ])
                        @endif

                        @if($post->content_type === 'reports')
                            @include('community.partials.report-meta-details', ['post' => $post, 'heading' => 'Report metadata'])
                        @endif

                        @if($post->content_type === 'news')
                            @include('community.partials.news-meta-details', ['post' => $post, 'heading' => 'News metadata'])
                        @endif

                        @if($post->content_type === 'stories')
                            @include('community.partials.story-meta-details', ['post' => $post, 'heading' => 'Story metadata'])
                            @include('community.partials.story-rating-summary', ['post' => $post, 'compact' => true])
                            @include('community.partials.story-achievements-panel', ['post' => $post, 'compact' => true])
                        @endif

                        @if($post->content_type === 'poetry')
                            @include('community.partials.poetry-show-sections', ['post' => $post])
                            @include('community.partials.poetry-meta-details', ['post' => $post, 'heading' => 'Poetry metadata'])
                            @include('community.partials.story-rating-summary', ['post' => $post, 'compact' => true])
                        @endif

                        @if($post->content_type === 'autobiography')
                            @include('community.partials.autobiography-show-sections', ['post' => $post])
                            @if($post->usesChapterLayoutForDisplay())
                                @include('community.partials.book-reader', ['post' => $post])
                            @endif
                            @include('community.partials.autobiography-after-content', ['post' => $post])
                            @include('community.partials.autobiography-meta-details', ['post' => $post, 'heading' => 'Autobiography metadata'])
                            @include('community.partials.story-rating-summary', ['post' => $post, 'compact' => true])
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    @if($post->isReportContent() && $reportEngagement)
                        <div class="chart-card p-3 p-lg-4 mb-4">
                            <h5 class="mb-3">Community reporting activity</h5>
                            <div class="row g-2 mb-3 small">
                                <div class="col-6"><strong>Supports:</strong> {{ number_format($reportEngagement['supports_count']) }}</div>
                                <div class="col-6"><strong>I Agree:</strong> {{ number_format($reportEngagement['agreements_count']) }}</div>
                                <div class="col-6"><strong>Followers:</strong> {{ number_format($reportEngagement['follows_count']) }}</div>
                                <div class="col-6"><strong>Evidence files:</strong> {{ number_format($reportEngagement['evidence_count']) }}</div>
                            </div>

                            @if($reportEngagementActivity)
                                @foreach([
                                    'supports' => 'Recent supporters',
                                    'agreements' => 'Recent agreements',
                                    'follows' => 'Recent followers',
                                ] as $key => $label)
                                    @if($reportEngagementActivity[$key]->isNotEmpty())
                                        <h6 class="small text-uppercase text-muted mt-3 mb-2">{{ $label }}</h6>
                                        <ul class="list-unstyled small mb-0">
                                            @foreach($reportEngagementActivity[$key] as $item)
                                                <li class="mb-1">
                                                    {{ $item->user?->full_name ?: ($item->user?->name ?? 'Community member') }}
                                                    <span class="text-muted">· {{ $item->created_at?->diffForHumans() }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endforeach
                            @endif

                            @if($communityParticipationEvidence->isNotEmpty())
                                <h6 class="small text-uppercase text-muted mt-3 mb-2">Additional evidence</h6>
                                <div class="d-flex flex-column gap-2">
                                    @foreach($communityParticipationEvidence as $evidence)
                                        <div class="border rounded p-2 bg-white small">
                                            <div class="fw-semibold">{{ $evidence->user?->full_name ?: ($evidence->user?->name ?? 'Community member') }}</div>
                                            <a href="{{ $evidence->url }}" target="_blank" rel="noopener">{{ $evidence->name }}</a>
                                            @if(filled($evidence->note))
                                                <div class="text-muted">{{ $evidence->note }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(($participationSuggestions ?? collect())->isNotEmpty() || ($participationFeedback ?? collect())->isNotEmpty() || ($post->content_type === 'news' && ($post->allow_comments || $post->allow_questions)))
                        <div class="chart-card p-3 p-lg-4 mb-4">
                            <h5 class="mb-3">Public participation submissions</h5>

                            @if($post->allow_comments && $post->discussionComments->isNotEmpty())
                                <h6 class="small text-uppercase text-muted mb-2">Comments</h6>
                                <div class="d-flex flex-column gap-2 mb-3">
                                    @foreach($post->discussionComments->take(10) as $comment)
                                        <div class="border rounded p-2 bg-white small">
                                            <div class="fw-semibold">{{ $comment->user?->full_name ?: ($comment->user?->name ?? 'Community member') }}</div>
                                            <div class="text-muted">{{ $comment->created_at?->diffForHumans() }}</div>
                                            <div>{{ $comment->body }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($post->allow_questions && $post->authorQuestions->isNotEmpty())
                                <h6 class="small text-uppercase text-muted mb-2">Reader questions</h6>
                                <div class="d-flex flex-column gap-2 mb-3">
                                    @foreach($post->authorQuestions->take(10) as $question)
                                        <div class="border rounded p-2 bg-white small">
                                            <div class="fw-semibold">{{ $question->asker?->full_name ?: ($question->asker?->name ?? 'Reader') }}</div>
                                            <div class="text-muted">{{ $question->created_at?->diffForHumans() }} · {{ $question->answered_at ? 'Answered' : 'Pending' }}</div>
                                            <div>{{ $question->question }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(($participationSuggestions ?? collect())->isNotEmpty())
                                <h6 class="small text-uppercase text-muted mb-2">Suggestions</h6>
                                <div class="d-flex flex-column gap-2 mb-3">
                                    @foreach($participationSuggestions as $entry)
                                        <div class="border rounded p-2 bg-white small">
                                            <div class="fw-semibold">{{ $entry->user?->full_name ?: ($entry->user?->name ?? 'Community member') }}</div>
                                            <div class="text-muted">{{ $entry->created_at?->diffForHumans() }}</div>
                                            <div>{{ $entry->body }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(($participationFeedback ?? collect())->isNotEmpty())
                                <h6 class="small text-uppercase text-muted mb-2">Feedback</h6>
                                <div class="d-flex flex-column gap-2">
                                    @foreach($participationFeedback as $entry)
                                        <div class="border rounded p-2 bg-white small">
                                            <div class="fw-semibold">{{ $entry->user?->full_name ?: ($entry->user?->name ?? 'Community member') }}</div>
                                            <div class="text-muted">{{ $entry->created_at?->diffForHumans() }}</div>
                                            <div>{{ $entry->body }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="chart-card p-3 p-lg-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h5 class="mb-1">Article score system</h5>
                                <p class="text-muted small mb-0">Admin-side score based on views, likes, shares, comments, reading time, and quality score.</p>
                            </div>
                            <span class="badge bg-primary fs-6" id="communityArticleScoreValue">{{ number_format((float) $post->article_score, 1) }}</span>
                        </div>

                        <div class="row g-2 mb-3 small">
                            <div class="col-6"><strong>Views:</strong> {{ number_format($scoreMetrics['views']) }}</div>
                            <div class="col-6"><strong>Likes:</strong> {{ number_format($scoreMetrics['likes']) }}</div>
                            <div class="col-6"><strong>Shares:</strong> {{ number_format($scoreMetrics['shares']) }}</div>
                            <div class="col-6"><strong>Comments:</strong> {{ number_format($scoreMetrics['comments']) }}</div>
                            <div class="col-6"><strong>Reading time:</strong> {{ number_format($scoreMetrics['reading_minutes'], 1) }} min</div>
                            <div class="col-6"><strong>Saves:</strong> {{ number_format($scoreMetrics['saves']) }}</div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Factor</th>
                                        <th>Score</th>
                                        <th>Weight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scoreBreakdown as $factor => $component)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $factor)) }}</td>
                                            <td>{{ number_format($component['normalized'], 1) }}</td>
                                            <td>{{ number_format($component['weighted'], 1) }}/{{ number_format($component['max'], 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <form id="communityQualityScoreForm" class="mb-3">
                            @csrf
                            <label class="form-label" for="communityQualityScore">Quality score (0-100)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="0" max="100" step="0.1" class="form-control" id="communityQualityScore" name="quality_score" value="{{ $post->quality_score }}">
                                <button type="submit" class="btn btn-outline-primary">Save</button>
                            </div>
                        </form>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-success js-recalculate-score">Recalculate score</button>
                            <button type="button" class="btn btn-sm btn-outline-success js-recalculate-score" data-auto-badges="0">Recalculate only</button>
                        </div>

                        <h6 class="mb-2">Article badges</h6>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach(\App\Services\CommunityArticleScoreService::BADGE_LABELS as $field => $label)
                                @php $enabled = (bool) $post->{$field}; @endphp
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $enabled ? 'btn-dark' : 'btn-outline-dark' }} js-article-badge"
                                    data-badge="{{ $field }}"
                                    data-enabled="{{ $enabled ? '1' : '0' }}"
                                >{{ $label }}</button>
                            @endforeach
                        </div>

                        @if($post->content_type === 'stories')
                            <h6 class="mb-2">Story achievement badges</h6>
                            <p class="small text-muted">Automatic badges recalculated from reads, shares, ratings, and saves.</p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(\App\Services\CommunityStoryAchievementService::BADGE_LABELS as $field => $label)
                                    @php $enabled = (bool) $post->{$field}; @endphp
                                    <span class="badge {{ $enabled ? 'bg-success' : 'bg-light text-dark border' }} community-story-badge community-story-badge--{{ str_replace('_', '-', str_replace('badge_', '', $field)) }}">
                                        {{ $label }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="chart-card p-3 p-lg-4 mb-4 community-review-meta">
                        <h5 class="mb-3">Submission info</h5>
                        <div class="mb-3">
                            <div class="label">Author</div>
                            <div>{{ $post->user?->full_name ?: ($post->user?->name ?? 'Unknown user') }}</div>
                            <div class="small text-muted">{{ $post->user?->email }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Submitted</div>
                            <div>{{ optional($post->submitted_at)->format('d M Y, h:i A') ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Published</div>
                            <div>{{ optional($post->published_at)->format('d M Y, h:i A') ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Location type</div>
                            <div>{{ $post->locationTypeLabel() }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Location</div>
                            <div>{{ $post->location ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Publish as</div>
                            <div>{{ $post->publishAsLabel() }}</div>
                            @if($post->resolvedPublishAs() === \App\Models\CommunityPost::PUBLISH_AS_PEN_NAME && filled($post->pen_name))
                                <div class="small text-muted">Pen name: {{ $post->pen_name }}</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <div class="label">Sharing</div>
                            <div>{{ $post->allowsSharing() ? 'Enabled' : 'Disabled' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Poll</div>
                            <div>{{ $post->allowsPoll() ? 'Enabled' : 'Disabled' }}</div>
                            @if($post->allowsPoll())
                                <div class="small text-muted">{{ $post->pollQuestion() }}</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <div class="label">Public participation</div>
                            <ul class="list-unstyled small mb-0">
                                <li>Comments: {{ $post->allow_comments ? 'Enabled' : 'Disabled' }}</li>
                                <li>Questions: {{ $post->allow_questions ? 'Enabled' : 'Disabled' }}</li>
                                <li>Suggestions: {{ $post->allow_suggestions ? 'Enabled' : 'Disabled' }}</li>
                                <li>Feedback: {{ $post->allow_feedback ? 'Enabled' : 'Disabled' }}</li>
                                <li>Additional evidence: {{ $post->allow_additional_evidence ? 'Enabled' : 'Disabled' }}</li>
                            </ul>
                        </div>
                        @if(filled($post->review_note))
                            <div class="mb-3">
                                <div class="label">Review note</div>
                                <div>{{ $post->review_note }}</div>
                            </div>
                        @endif
                        @if(is_array($post->tags) && $post->tags !== [])
                            <div class="mb-3">
                                <div class="label">Tags</div>
                                <div>{{ implode(', ', $post->tags) }}</div>
                            </div>
                        @endif
                        <div class="mb-3">
                            <div class="label">Account ID</div>
                            <div>{{ $post->user_id ?? '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Submission IP</div>
                            <div>{{ $post->submission_ip ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Submitted at</div>
                            <div>{{ optional($post->submitted_at)->format('d M Y, h:i A') ?: optional($post->created_at)->format('d M Y, h:i A') ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Content responsibility accepted</div>
                            <div>{{ optional($post->content_responsibility_accepted_at)->format('d M Y, h:i A') ?: '—' }}</div>
                        </div>
                        <div class="mb-0">
                            <div class="label">Original work / indemnity accepted</div>
                            <div>{{ optional($post->original_work_accepted_at)->format('d M Y, h:i A') ?: '—' }}</div>
                        </div>
                    </div>

                    @if($post->auditLogs->isNotEmpty())
                        <div class="chart-card p-3 p-lg-4 mb-4">
                            <h5 class="mb-3">Edit audit log</h5>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>When</th>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>IP</th>
                                            <th>Changes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($post->auditLogs as $log)
                                            <tr>
                                                <td>{{ optional($log->created_at)->format('d M Y, h:i A') }}</td>
                                                <td>{{ $log->actionLabel() }}</td>
                                                <td>{{ $log->user?->full_name ?: ($log->user?->name ?? 'System') }}</td>
                                                <td>{{ $log->ip_address ?: '—' }}</td>
                                                <td class="small text-muted">
                                                    @if(is_array($log->changes) && $log->changes !== [])
                                                        {{ \Illuminate\Support\Str::limit(json_encode($log->changes), 180) }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if(is_array($post->meta) && $post->meta !== [])
                        @php
                            $reportMetaKeys = $post->content_type === 'reports'
                                ? array_keys(\App\Support\CommunityPostFormFields::reportDetailMetaOrder())
                                : [];
                            $storyMetaKeys = $post->content_type === 'stories'
                                ? array_keys(\App\Support\CommunityPostFormFields::storyDetailMetaOrder())
                                : [];
                            $poetryMetaKeys = $post->content_type === 'poetry'
                                ? array_merge(
                                    array_keys(\App\Support\CommunityPostFormFields::poetryDetailMetaOrder()),
                                    array_keys(\App\Support\CommunityPostFormFields::poetryRegionalLocationOrder())
                                )
                                : [];
                            $autobiographyMetaKeys = $post->content_type === 'autobiography'
                                ? \App\Support\CommunityPostFormFields::autobiographyStructuredMetaKeys()
                                : [];
                            $skipMetaKeys = array_merge($reportMetaKeys, $storyMetaKeys, $poetryMetaKeys, $autobiographyMetaKeys, [
                                'story_gallery',
                                'story_audio',
                                'poetry_audio',
                                'book_pages',
                            ]);
                        @endphp
                        <div class="chart-card p-3 p-lg-4">
                            <h5 class="mb-3">Additional metadata</h5>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach($post->meta as $key => $value)
                                            @continue(in_array($key, $skipMetaKeys, true))
                                            @continue($key === 'issue_attachments')
                                            @continue(is_object($value))
                                            @continue(blank($value) && ! is_bool($value))
                                            <tr>
                                                <th class="text-muted">{{ \App\Support\CommunityPostFormFields::labels()[$key] ?? \Illuminate\Support\Str::headline($key) }}</th>
                                                <td>
                                                    @if(is_array($value))
                                                        {{ implode(', ', $value) }}
                                                    @else
                                                        {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="preview-pane" role="tabpanel">
            <div class="chart-card p-3">
                <p class="text-muted mb-3">This preview shows how the post will appear on the public community page when published.</p>
                <iframe
                    class="community-review-preview"
                    src="{{ route('admin.community-posts.preview', $post) }}"
                    title="Community post frontend preview"
                    loading="lazy"
                ></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.communityPostApprovalConfig = {
        approveUrl: @json(route('admin.community-posts.approve', $post)),
        rejectUrl: @json(route('admin.community-posts.reject', $post)),
        draftUrl: @json(route('admin.community-posts.draft', $post)),
        archiveUrl: @json(route('admin.community-posts.archive', $post)),
        featureUrl: @json(route('admin.community-posts.feature', $post)),
        sponsorUrl: @json(route('admin.community-posts.sponsor', $post)),
        highlightUrl: @json(route('admin.community-posts.highlight', $post)),
        qualityScoreUrl: @json(route('admin.community-posts.quality-score', $post)),
        recalculateScoreUrl: @json(route('admin.community-posts.recalculate-score', $post)),
        articleBadgeUrl: @json(route('admin.community-posts.article-badge', $post)),
        redirectUrl: @json(route('admin.community-posts.all.index')),
    };
</script>
<script src="{{ asset('assets/js/admin-community-posts.js') }}?v={{ now()->timestamp }}"></script>
@endpush
