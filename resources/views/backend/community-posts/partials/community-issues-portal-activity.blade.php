@php
    $reportEngagement = $reportEngagement ?? null;
    $reportEngagementActivity = $reportEngagementActivity ?? null;
    $supportCount = (int) data_get($reportEngagement, 'supports_count', 0);
    $isEscalated = $post->isCommunityIssueEscalated($supportCount);
@endphp

@if($post->isCommunityIssuesPost() && $reportEngagement)
    <div class="chart-card p-3 p-lg-4 mb-4" id="community-issues-portal-activity">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Community Issues engagement</h5>
                <p class="text-muted small mb-0">Supports, verifications, followers, and campaign status for this civic issue.</p>
            </div>
            @if($isEscalated)
                <span class="badge bg-danger">High priority · {{ number_format($supportCount) }} supporters</span>
            @endif
        </div>

        <div class="row g-2 mb-3 small">
            <div class="col-6 col-md-3"><strong>Supports:</strong> {{ number_format($reportEngagement['supports_count']) }}</div>
            <div class="col-6 col-md-3"><strong>Verified:</strong> {{ number_format($reportEngagement['agreements_count']) }}</div>
            <div class="col-6 col-md-3"><strong>Followers:</strong> {{ number_format($reportEngagement['follows_count']) }}</div>
            <div class="col-6 col-md-3"><strong>Evidence files:</strong> {{ number_format($reportEngagement['evidence_count']) }}</div>
        </div>

        <div class="row g-2 mb-3 small">
            <div class="col-md-4"><strong>Campaign:</strong> {{ data_get($post->meta, 'community_issue_allow_campaign', true) ? 'Enabled' : 'Disabled' }}</div>
            <div class="col-md-4"><strong>Escalation threshold:</strong> {{ number_format($post->communityIssueEscalationThreshold()) }}</div>
            <div class="col-md-4"><strong>Status:</strong> {{ data_get($post->meta, 'community_issue_status_tracker') ?: '—' }}</div>
        </div>

        @if($reportEngagementActivity)
            @foreach([
                'supports' => 'Recent supporters',
                'agreements' => 'Recent verifications',
                'follows' => 'Recent followers',
            ] as $key => $label)
                @if(($reportEngagementActivity[$key] ?? collect())->isNotEmpty())
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

        <div class="mt-3">
            <a href="{{ route('community.community-issues.index') }}" class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener">
                <i class="fa-solid fa-map-location-dot me-1"></i>View on Issues Hub
            </a>
        </div>
    </div>
@endif
