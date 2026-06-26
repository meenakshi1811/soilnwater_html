@php
    $reportEngagement = $reportEngagement ?? null;
    $reportEngagementActivity = $reportEngagementActivity ?? null;
@endphp

@if($post->isMyAreaPost() && $reportEngagement)
    <div class="chart-card p-3 p-lg-4 mb-4" id="my-area-portal-activity">
        <h5 class="mb-1">My Area community activity</h5>
        <p class="text-muted small mb-3">Supports, agreements, and followers for this local post.</p>
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
    </div>
@endif
