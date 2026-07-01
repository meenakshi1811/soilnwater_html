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
@if($post->isChildrensCornerPost())
    @push('styles')
        @include('community.partials.childrens-corner-styles')
    @endpush
@endif
@if($post->isAwarenessPost())
    @push('styles')
        @include('community.partials.awareness-styles')
    @endpush
@endif
@if($post->isBusinessPost())
    @push('styles')
        @include('community.partials.business-styles')
    @endpush
@endif
@if($post->isWomensWorldPost())
    @push('styles')
        @include('community.partials.business-styles')
    @endpush
@endif
@if($post->isStudentCornerPost())
    @push('styles')
        @include('community.partials.business-styles')
    @endpush
@endif
@if($post->isYouthCornerPost())
    @push('styles')
        @include('community.partials.business-styles')
    @endpush
@endif
@if($post->isCommunityIssuesPost())
    @push('styles')
        @include('community.partials.business-styles')
    @endpush
@endif
@if($post->isAgriculturePost())
    @push('styles')
        @include('community.partials.agriculture-styles')
    @endpush
@endif
@if($post->isEnvironmentPost())
    @push('styles')
        @include('community.partials.environment-styles')
    @endpush
@endif
@if($post->isAstroConsultancyPost())
    @push('styles')
        @include('community.partials.astro-consultancy-styles')
    @endpush
@endif
@if($post->isReligionSpiritualityPost())
    @push('styles')
        @include('community.partials.religion-spirituality-styles')
    @endpush
@endif
@if($post->isCreativeCornerPost())
    @push('styles')
        @include('community.partials.creative-corner-styles')
    @endpush
@endif
@if($post->isCompetitionsPost())
    @push('styles')
        @include('community.partials.competitions-styles')
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

            @if($post->isChildrensCornerPost())
                @include('community.partials.childrens-corner-show-sections', [
                    'post' => $post,
                    'placement' => 'media',
                    'showQuizAnswers' => true,
                ])
                @include('community.partials.childrens-corner-meta-details', [
                    'post' => $post,
                    'heading' => "Saved Children's Corner metadata",
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isAwarenessPost())
                @include('community.partials.awareness-show-sections', ['post' => $post])
                @include('community.partials.awareness-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved awareness metadata',
                ])
            @endif

            @if($post->isBusinessPost())
                @include('community.partials.business-show-sections', [
                    'post' => $post,
                    'businessEngagement' => $businessEngagement ?? null,
                ])
                @include('community.partials.business-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved business metadata',
                ])
            @endif

            @if($post->isWomensWorldPost())
                @include('community.partials.womens-world-show-sections', ['post' => $post])
                @include('community.partials.womens-world-meta-details', [
                    'post' => $post,
                    'heading' => "Saved Women's World metadata",
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isSeniorCitizensForumPost())
                @include('community.partials.senior-citizens-forum-show-sections', ['post' => $post])
                @include('community.partials.senior-citizens-forum-after-content', ['post' => $post])
                @include('community.partials.senior-citizens-forum-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Senior Citizens Forum metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isStudentCornerPost())
                @include('community.partials.student-corner-show-sections', ['post' => $post])
                @include('community.partials.student-corner-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Student Corner metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isYouthCornerPost())
                @include('community.partials.youth-corner-show-sections', ['post' => $post])
                @include('community.partials.youth-corner-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Youth Corner metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isLocalVoicesPost())
                @include('community.partials.local-voices-show-sections', ['post' => $post])
            @endif

            @if($post->isMyAreaPost())
                @include('community.partials.my-area-show-sections', ['post' => $post])
                @include('community.partials.my-area-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved My Area metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isCommunityIssuesPost())
                @include('community.partials.community-issues-show-sections', [
                    'post' => $post,
                    'reportEngagement' => $reportEngagement,
                ])
                @include('community.partials.community-issues-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Community Issues metadata',
                    'includeAdmin' => true,
                    'reportEngagement' => $reportEngagement,
                ])
            @endif

            @if($post->isAgriculturePost())
                @include('community.partials.agriculture-show-sections', ['post' => $post])
                @include('community.partials.agriculture-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Agriculture metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isEnvironmentPost())
                @include('community.partials.environment-show-sections', ['post' => $post])
                @include('community.partials.environment-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Environment metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isAstroConsultancyPost())
                @include('community.partials.astro-consultancy-show-sections', ['post' => $post])
                @include('community.partials.astro-consultancy-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Astro Consultancy metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isReligionSpiritualityPost())
                @include('community.partials.religion-spirituality-show-sections', ['post' => $post])
                @include('community.partials.religion-spirituality-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Religion & Spirituality metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isCreativeCornerPost())
                @include('community.partials.creative-corner-show-sections', ['post' => $post])
                @include('community.partials.creative-corner-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Creative Corner metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isCompetitionsPost())
                @include('community.partials.competitions-show-sections', ['post' => $post])
                @include('community.partials.competitions-meta-details', [
                    'post' => $post,
                    'heading' => 'Saved Competitions metadata',
                    'includeAdmin' => true,
                ])
            @endif

            @if($post->isAstroConsultancyPost() && $post->astroHasEngagementActions())
                <div class="mb-4" id="astro-consultancy-activity">
                    @include('backend.community-posts.partials.astro-consultancy-portal-activity', [
                        'post' => $post,
                        'astroConsultancyEngagement' => $astroConsultancyEngagement,
                        'astroConsultancyEngagementActivity' => $astroConsultancyEngagementActivity,
                        'showQueryContacts' => true,
                    ])
                </div>
            @endif

            @if($post->isEnvironmentPost() && $post->environmentHasParticipationActions())
                <div class="mb-4" id="environment-campaign-activity">
                    @include('backend.community-posts.partials.environment-portal-activity', [
                        'post' => $post,
                        'environmentEngagement' => $environmentEngagement,
                        'environmentEngagementActivity' => $environmentEngagementActivity,
                        'showVolunteerContacts' => true,
                    ])
                </div>
            @endif

            @if($post->isAwarenessPost() && ($post->allowsAwarenessCauseSupport() || $post->allowsAwarenessPledges() || $post->allowsCampaignJoin()))
                <div class="mb-4" id="awareness-campaign-activity">
                    @include('backend.community-posts.partials.awareness-portal-activity', [
                        'post' => $post,
                        'awarenessEngagement' => $awarenessEngagement,
                        'awarenessEngagementActivity' => $awarenessEngagementActivity,
                        'awarenessPledgeCounts' => $awarenessPledgeCounts ?? [],
                        'showVolunteerContacts' => true,
                    ])
                </div>
            @endif

            @if($post->isBusinessPost() && $post->allowsBusinessContact())
                <div class="mb-4" id="business-inquiry-activity">
                    @include('backend.community-posts.partials.business-portal-activity', [
                        'post' => $post,
                        'businessEngagement' => $businessEngagement,
                        'businessEngagementActivity' => $businessEngagementActivity,
                        'showQueryContacts' => true,
                    ])
                </div>
            @endif

            @if($post->isWomensWorldPost())
                <div class="mb-4" id="womens-world-portal-activity">
                    @include('backend.community-posts.partials.womens-world-portal-activity', ['post' => $post])
                </div>
            @endif

            @if($post->isSeniorCitizensForumPost())
                <div class="mb-4" id="senior-citizens-forum-portal-activity">
                    @include('backend.community-posts.partials.senior-citizens-forum-portal-activity', ['post' => $post])
                </div>
            @endif

            @if($post->isStudentCornerPost())
                <div class="mb-4" id="student-corner-portal-activity">
                    @include('backend.community-posts.partials.student-corner-portal-activity', ['post' => $post])
                </div>
            @endif

            @if($post->isYouthCornerPost())
                <div class="mb-4" id="youth-corner-portal-activity">
                    @include('backend.community-posts.partials.youth-corner-portal-activity', ['post' => $post])
                </div>
            @endif

            @if($post->isMyAreaPost())
                <div class="mb-4" id="my-area-portal-activity">
                    @include('backend.community-posts.partials.my-area-portal-activity', [
                        'post' => $post,
                        'reportEngagement' => $reportEngagement,
                        'reportEngagementActivity' => $reportEngagementActivity ?? null,
                    ])
                </div>
            @endif

            @if($post->isCommunityIssuesPost())
                <div class="mb-4" id="community-issues-portal-activity">
                    @include('backend.community-posts.partials.community-issues-portal-activity', [
                        'post' => $post,
                        'reportEngagement' => $reportEngagement,
                        'reportEngagementActivity' => $reportEngagementActivity ?? null,
                    ])
                </div>
            @endif

            <div class="chart-card p-3 p-lg-4 mb-4">
                <h5 class="mb-3">Comments &amp; discussion settings</h5>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-1"><strong>Comments:</strong> {{ $post->allow_comments ? 'Enabled' : 'Disabled' }}</li>
                    @if($post->isChildrensCornerPost())
                        <li class="mb-1"><strong>Privacy:</strong> {{ $post->childrensCornerPrivacyLabel() }}</li>
                        <li class="mb-1"><strong>Safety declaration:</strong> {{ data_get($post->meta, 'childrens_corner_safety_confirmed') ? 'Confirmed' : 'Not recorded' }}</li>
                        <li class="mb-1"><strong>Comments moderated:</strong> {{ $post->commentsModerated() ? 'Yes — approval required' : 'No' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> Child-friendly only</li>
                    @elseif($post->isWomensWorldPost())
                        <li class="mb-1"><strong>Publish as:</strong> {{ \App\Support\CommunityContentTaxonomy::womensWorldPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</li>
                        <li class="mb-1"><strong>Visibility:</strong> {{ $post->womensWorldVisibilityLabel() }}</li>
                        <li class="mb-1"><strong>Sharing:</strong> {{ $post->allow_sharing ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> Women's World positive reactions</li>
                    @elseif($post->isSeniorCitizensForumPost())
                        <li class="mb-1"><strong>Visibility:</strong> {{ $post->seniorCitizensForumVisibilityLabel() }}</li>
                        <li class="mb-1"><strong>Digital legacy:</strong> {{ data_get($post->meta, 'senior_citizens_forum_preserve_digital_legacy') ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Sharing:</strong> {{ $post->allow_sharing ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> Senior Citizens Forum positive reactions</li>
                    @elseif($post->isStudentCornerPost())
                        <li class="mb-1"><strong>Publish as:</strong> {{ \App\Support\CommunityContentTaxonomy::studentCornerPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</li>
                        <li class="mb-1"><strong>Visibility:</strong> {{ $post->studentCornerVisibilityLabel() }}</li>
                        <li class="mb-1"><strong>Peer discussion:</strong> {{ $post->allow_feedback ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Sharing:</strong> {{ $post->allow_sharing ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Competition entry:</strong> {{ data_get($post->meta, 'student_corner_submit_to_competition') ? 'Submitted' : 'Not submitted' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> Student Corner positive reactions</li>
                    @elseif($post->isYouthCornerPost())
                        <li class="mb-1"><strong>Publish as:</strong> {{ \App\Support\CommunityContentTaxonomy::youthCornerPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</li>
                        <li class="mb-1"><strong>Visibility:</strong> {{ $post->youthCornerVisibilityLabel() }}</li>
                        <li class="mb-1"><strong>Peer discussion:</strong> {{ $post->allow_feedback ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Sharing:</strong> {{ $post->allow_sharing ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> Youth Corner positive reactions</li>
                    @elseif($post->isMyAreaPost())
                        <li class="mb-1"><strong>Activity:</strong> {{ $post->myAreaActivityType() ?: '—' }}</li>
                        <li class="mb-1"><strong>Publish as:</strong> {{ \App\Support\CommunityContentTaxonomy::myAreaPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</li>
                        <li class="mb-1"><strong>Visibility:</strong> {{ $post->myAreaVisibilityLabel() }}</li>
                        <li class="mb-1"><strong>Status tracker:</strong> {{ data_get($post->meta, 'my_area_status_tracker') ?: '—' }}</li>
                        <li class="mb-1"><strong>Community voting:</strong> {{ $post->allow_poll ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Sharing:</strong> {{ $post->allow_sharing ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> My Area civic reactions</li>
                    @elseif($post->isCommunityIssuesPost())
                        <li class="mb-1"><strong>Issue category:</strong> {{ data_get($post->meta, 'community_issue_category') ?: '—' }}</li>
                        <li class="mb-1"><strong>Issue type:</strong> {{ data_get($post->meta, 'community_issue_type') ?: '—' }}</li>
                        <li class="mb-1"><strong>Severity:</strong> {{ data_get($post->meta, 'community_issue_severity') ?: '—' }}</li>
                        <li class="mb-1"><strong>Publish as:</strong> {{ \App\Support\CommunityContentTaxonomy::communityIssuePublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</li>
                        <li class="mb-1"><strong>Visibility:</strong> {{ $post->communityIssueVisibilityLabel() }}</li>
                        <li class="mb-1"><strong>Status tracker:</strong> {{ data_get($post->meta, 'community_issue_status_tracker') ?: '—' }}</li>
                        <li class="mb-1"><strong>Support campaign:</strong> {{ data_get($post->meta, 'community_issue_allow_campaign', true) ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Community verification:</strong> {{ $post->allowsCommunityIssueVerification() ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Poll:</strong> {{ $post->allow_poll ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Sharing:</strong> {{ $post->allow_sharing ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> Community Issues civic reactions</li>
                    @elseif($post->isEnvironmentPost())
                        <li class="mb-1"><strong>Post type:</strong> {{ $post->environmentPostTypeLabel() ?: '—' }}</li>
                        <li class="mb-1"><strong>Category:</strong> {{ $post->environmentCategoryLabel() ?: '—' }}</li>
                        <li class="mb-1"><strong>Green Map:</strong> {{ $post->showsOnGreenMap() ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Impact calculator:</strong> {{ $post->enablesEnvironmentImpactCalculator() ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Campaign actions:</strong> {{ $post->environmentHasParticipationActions() ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Poll:</strong> {{ $post->allow_poll ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Sharing:</strong> {{ $post->allow_sharing ? 'Enabled' : 'Disabled' }}</li>
                        <li class="mb-1"><strong>Reactions:</strong> Environment eco reactions</li>
                    @endif
                    <li class="mb-1"><strong>Questions:</strong> {{ $post->allow_questions ? 'Enabled' : 'Disabled' }}</li>
                    <li class="mb-0"><strong>Suggestions:</strong> {{ $post->allow_suggestions ? 'Enabled' : 'Disabled' }}</li>
                </ul>
            </div>

            @if($post->allow_comments && $post->isChildrensCornerPost() && $post->commentsModerated())
                @php
                    $pendingCommentsCount = $post->discussionComments->filter(fn ($comment) => ! $comment->is_approved)->count()
                        + $post->discussionComments->flatMap->replies->filter(fn ($reply) => ! $reply->is_approved)->count();
                @endphp
                @if($pendingCommentsCount > 0)
                    <div class="alert alert-warning mb-4">
                        <strong>{{ $pendingCommentsCount }} comment{{ $pendingCommentsCount === 1 ? '' : 's' }} awaiting your approval.</strong>
                        Approve them below to make them visible on the public page.
                    </div>
                @endif
            @endif

            @if($post->allow_comments)
                <div class="chart-card p-3 p-lg-4 mb-4" id="participation-comments">
                    <h5 class="mb-3">Comments ({{ $engagementSummary['comments'] }})</h5>
                    @forelse($post->discussionComments as $comment)
                        <div class="border rounded p-3 mb-3 bg-light {{ ! $comment->is_approved ? 'border-warning' : '' }}">
                            <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <strong>{{ $comment->user?->full_name ?: ($comment->user?->name ?? 'Community member') }}</strong>
                                    @if(! $comment->is_approved)
                                        <span class="badge bg-warning text-dark">Pending approval</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if(! $comment->is_approved)
                                        <form method="POST" action="{{ route('community.comments.approve', [$post, $comment]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                    @endif
                                    <small class="text-muted">{{ $comment->created_at?->diffForHumans() }}</small>
                                </div>
                            </div>
                            <p class="mb-2">{!! nl2br(e($comment->body)) !!}</p>
                            @foreach($comment->replies as $reply)
                                <div class="border-start ps-3 ms-2 mb-2 {{ ! $reply->is_approved ? 'border-warning' : '' }}">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap mb-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <strong class="small">{{ $reply->user?->full_name ?: ($reply->user?->name ?? 'Community member') }}</strong>
                                            @if(! $reply->is_approved)
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(! $reply->is_approved)
                                                <form method="POST" action="{{ route('community.comments.approve', [$post, $reply]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                                </form>
                                            @endif
                                            <small class="text-muted">{{ $reply->created_at?->diffForHumans() }}</small>
                                        </div>
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

            @if($post->isAwarenessPost())
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Awareness campaign engagement</h5>
                    <div class="row g-2 small">
                        <div class="col-4"><strong>Supporters</strong><div>{{ number_format($awarenessEngagement['supports_count'] ?? 0) }}</div></div>
                        <div class="col-4"><strong>Pledges</strong><div>{{ number_format($awarenessEngagement['pledges_count'] ?? 0) }}</div></div>
                        <div class="col-4"><strong>Volunteers</strong><div>{{ number_format($awarenessEngagement['volunteers_count'] ?? 0) }}</div></div>
                    </div>
                    @if($post->allowsAwarenessCauseSupport() || $post->allowsAwarenessPledges() || $post->allowsCampaignJoin())
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @if($post->allowsAwarenessCauseSupport())
                                <span class="badge bg-success-subtle text-success border">Cause support enabled</span>
                            @endif
                            @if($post->allowsAwarenessPledges())
                                <span class="badge bg-warning-subtle text-dark border">Pledges enabled</span>
                            @endif
                            @if($post->allowsCampaignJoin())
                                <span class="badge bg-info-subtle text-info border">Volunteer join enabled</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            @if($post->isEnvironmentPost())
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Environment campaign engagement</h5>
                    <div class="row g-2 small">
                        <div class="col-4"><strong>Supporters</strong><div>{{ number_format($environmentEngagement['supports_count'] ?? 0) }}</div></div>
                        <div class="col-4"><strong>Followers</strong><div>{{ number_format($environmentEngagement['follows_count'] ?? 0) }}</div></div>
                        <div class="col-4"><strong>Volunteers</strong><div>{{ number_format($environmentEngagement['volunteers_count'] ?? 0) }}</div></div>
                    </div>
                    @if($post->environmentHasParticipationActions())
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @if($post->allowsEnvironmentSupportInitiative())
                                <span class="badge bg-success-subtle text-success border">Support enabled</span>
                            @endif
                            @if($post->allowsEnvironmentFollowCampaign())
                                <span class="badge bg-primary-subtle text-primary border">Follow enabled</span>
                            @endif
                            @if($post->allowsEnvironmentVolunteerRegistration() || $post->allowsEnvironmentJoinCampaign())
                                <span class="badge bg-info-subtle text-info border">Volunteer registration enabled</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            @if($post->isBusinessPost())
                <div class="chart-card p-3 p-lg-4 mb-4">
                    <h5 class="mb-3">Business networking</h5>
                    <div class="row g-2 small">
                        <div class="col-4"><strong>Inquiries</strong><div>{{ number_format($businessEngagement['queries_count'] ?? 0) }}</div></div>
                    </div>
                    @if($post->allowsBusinessContact())
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach($post->businessContactOptionsForDisplay() as $option)
                                <span class="badge bg-warning-subtle text-dark border">{{ $option }}</span>
                            @endforeach
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
