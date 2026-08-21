@php
    $post = $post ?? null;
    if (! $post?->isAwarenessPost() || ! $post->awarenessHasEventDetails()) {
        return;
    }

    $sidebarLayout = $sidebarLayout ?? false;
    $eventFields = collect([
        'Event type' => data_get($post->meta, 'awareness_event_type'),
        'Date' => filled(data_get($post->meta, 'awareness_event_date'))
            ? \Illuminate\Support\Carbon::parse(data_get($post->meta, 'awareness_event_date'))->format('j F Y')
            : null,
        'Venue' => data_get($post->meta, 'awareness_event_venue'),
        'Time' => data_get($post->meta, 'awareness_event_time'),
        'Organizer' => data_get($post->meta, 'awareness_event_organizer'),
    ])->filter(fn (mixed $value): bool => filled($value));
@endphp

@if($eventFields->isNotEmpty())
    <div @class([
        'awareness-section-panel about-box mb-4' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--awareness-event' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? 'Event details' }}</p>
        @else
            <div class="awareness-section-panel__header">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                <h4 class="mb-0">{{ $heading ?? 'Event details' }}</h4>
            </div>
        @endif

        <div @class([
            'row g-3' => ! $sidebarLayout,
            'news-sidebar-meta-grid' => $sidebarLayout,
        ])>
            @foreach($eventFields as $label => $value)
                <div @class([
                    'col-md-6' => ! $sidebarLayout,
                    'news-sidebar-meta-grid__item' => $sidebarLayout,
                    'news-sidebar-meta-grid__item--wide' => $sidebarLayout && in_array($label, ['Venue', 'Organizer'], true),
                ])>
                    @if($sidebarLayout)
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ $label }}</strong>
                            <span>{{ $value }}</span>
                        </div>
                    @else
                        <div class="awareness-meta-item">
                            <span class="awareness-meta-item__label">{{ $label }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
