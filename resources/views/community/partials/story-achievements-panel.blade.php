@if($post->content_type === 'stories')
    @php
        $earnedBadges = $post->storyAchievementBadges();
        $earnedLabels = collect($earnedBadges)->pluck('label')->all();
    @endphp
    <div class="story-achievements-panel {{ $wrapperClass ?? 'about-box mt-4' }}">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h4 class="mb-1 {{ isset($compact) && $compact ? 'h5' : '' }}">{{ $heading ?? 'Story achievements' }}</h4>
                <p class="text-muted small mb-0">Automatic badges based on reads, shares, ratings, and community engagement.</p>
            </div>
            @if($earnedBadges !== [])
                <span class="badge bg-success">{{ count($earnedBadges) }} earned</span>
            @endif
        </div>
        <div class="row g-2">
            @foreach(\App\Services\CommunityStoryAchievementService::BADGE_LABELS as $field => $badgeLabel)
                @php $earned = in_array($badgeLabel, $earnedLabels, true); @endphp
                <div class="col-md-6 col-lg-3">
                    <div class="story-achievement-item {{ $earned ? 'is-earned' : 'is-pending' }}">
                        <span class="story-achievement-item__icon" aria-hidden="true">
                            <i class="fa-solid {{ $earned ? 'fa-medal text-success' : 'fa-lock text-muted' }}"></i>
                        </span>
                        {{ $badgeLabel }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
