@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }

    $statusTracker = data_get($post->meta, 'community_issue_status_tracker');
    $resolutionTimeline = $post->communityIssueResolutionTimelineEntries();
    $statusSteps = \App\Support\CommunityContentTaxonomy::communityIssueStatusSteps();
    $currentStatusIndex = filled($statusTracker) ? array_search($statusTracker, $statusSteps, true) : false;
    $capabilities = [
        ['label' => 'Support campaign', 'enabled' => (bool) data_get($post->meta, 'community_issue_allow_campaign', true), 'icon' => 'fa-people-group'],
        ['label' => 'I support this issue', 'enabled' => $post->allowsCommunityIssueSupport(), 'icon' => 'fa-hand-holding-heart'],
        ['label' => 'Community verification', 'enabled' => $post->allowsCommunityIssueVerification(), 'icon' => 'fa-circle-check'],
        ['label' => 'Follow issue', 'enabled' => $post->allowsCommunityIssueFollow(), 'icon' => 'fa-bell'],
        ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
        ['label' => 'Add evidence', 'enabled' => (bool) $post->allow_feedback, 'icon' => 'fa-camera'],
        ['label' => 'Suggestions', 'enabled' => (bool) $post->allow_suggestions, 'icon' => 'fa-lightbulb'],
        ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
        ['label' => 'Poll', 'enabled' => (bool) $post->allow_poll, 'icon' => 'fa-square-poll-vertical'],
    ];
@endphp

<div class="community-news-sidebar__community-issues-extras" aria-label="Community issue sidebar details">
    @include('community.partials.community-issues-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
        'splitStatusSection' => true,
    ])

    @if(filled($statusTracker) || $resolutionTimeline !== [])
        <div class="community-news-sidebar__card community-news-sidebar__card--community-issues-status">
            <p class="community-news-sidebar__label">Status tracking</p>
            @if(filled($statusTracker))
                <div class="ci-status-stepper ci-status-stepper--sidebar" aria-label="Issue status steps">
                    @foreach($statusSteps as $index => $step)
                        @php
                            $isCurrent = $step === $statusTracker;
                            $isComplete = $currentStatusIndex !== false && $index < $currentStatusIndex;
                        @endphp
                        <div class="ci-status-step {{ $isCurrent ? 'is-current' : ($isComplete ? 'is-complete' : '') }}">
                            <span class="ci-status-step__label">{{ $step }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            @if($resolutionTimeline !== [])
                <div class="community-issues-sidebar-timeline">
                    <div class="community-issues-sidebar-timeline__label">Resolution tracker</div>
                    <ul class="list-unstyled mb-0">
                        @foreach($resolutionTimeline as $entry)
                            <li class="community-issues-sidebar-timeline__item">
                                <i class="fa-solid fa-circle-dot" aria-hidden="true"></i>
                                <span>{{ $entry }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="community-news-sidebar__card community-news-sidebar__card--community-issues-actions">
        <p class="community-news-sidebar__label">Community actions enabled</p>
        <div class="ci-capability-grid ci-capability-grid--sidebar">
            @foreach($capabilities as $capability)
                <span class="ci-capability-pill {{ $capability['enabled'] ? 'is-on' : 'is-off' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>{{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>
</div>
