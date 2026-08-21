@php
    $metaLabels = \App\Support\CommunityPostFormFields::communityIssueDetailMetaOrder();
    $railLayout = $railLayout ?? false;
    $sidebarLayout = $sidebarLayout ?? false;
    $splitStatusSection = $splitStatusSection ?? false;
    $locationKeys = array_merge(
        \App\Models\CommunityPost::structuredLocationMetaKeys(),
        ['location_landmark']
    );
    $skipKeys = array_merge($locationKeys, [
        'community_issue_suggested_solution',
        'community_issue_support_requests',
        'community_issue_poll_question',
        'community_issue_poll_options',
        'community_issue_allow_campaign',
        'community_issue_allow_support',
        'community_issue_allow_follow',
        'community_issue_allow_verification',
        'community_issue_escalation_threshold',
    ]);
    if ($splitStatusSection) {
        $skipKeys = array_merge($skipKeys, [
            'community_issue_status_tracker',
            'community_issue_resolution_timeline',
        ]);
    }
    $displayMeta = collect($metaLabels)
        ->except($skipKeys)
        ->mapWithKeys(function (string $label, string $key) use ($post): array {
            $value = data_get($post->meta, $key);

            if ($key === 'community_issue_category' && blank($value)) {
                $value = $post->category;
            }

            if (in_array($key, ['community_issue_affected_groups', 'community_issue_support_requests'], true) && is_array($value)) {
                $value = implode(', ', array_values(array_filter($value, fn (mixed $item): bool => filled($item))));
            }

            if (in_array($key, ['community_issue_first_noticed_on', 'community_issue_complaint_date'], true) && filled($value)) {
                $value = \Illuminate\Support\Carbon::parse($value)->format('j F Y');
            }

            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }

            return [$key => $value];
        })
        ->filter(fn (mixed $value): bool => filled($value));
@endphp

@if($post->isCommunityIssuesPost() && $displayMeta->isNotEmpty())
    @if($sidebarLayout)
        <div class="community-news-sidebar__card community-news-sidebar__card--community-issues-details">
            <p class="community-news-sidebar__label">{{ $heading ?? 'Issue details' }}</p>
            <div class="news-sidebar-meta-grid">
                @foreach($displayMeta as $key => $value)
                    <div @class([
                        'news-sidebar-meta-grid__item',
                        'news-sidebar-meta-grid__item--wide' => in_array($key, ['community_issue_affected_groups', 'community_issue_department_contacted'], true),
                    ])>
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Issue details' }}</h4>
                </div>
            </div>
            <div class="community-detail-grid community-detail-grid--rail">
                @foreach($displayMeta as $key => $value)
                    <div class="community-detail-item">
                        <span class="community-detail-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        <span class="community-detail-item__value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                <h4 class="mb-0">{{ $heading ?? 'Issue details' }}</h4>
            </div>
            <div class="row g-3">
                @foreach($displayMeta as $key => $value)
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
