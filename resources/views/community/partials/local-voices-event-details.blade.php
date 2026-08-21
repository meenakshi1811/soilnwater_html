@php
    $post = $post ?? null;
    if (! $post?->isLocalVoicesPost()) {
        return;
    }

    $sidebarLayout = $sidebarLayout ?? false;
    $eventDate = data_get($post->meta, 'local_voice_event_date');
    $eventTime = data_get($post->meta, 'local_voice_event_time');
    $eventVenue = data_get($post->meta, 'local_voice_event_venue');
    $eventOrganizer = data_get($post->meta, 'local_voice_event_organizer');
    $eventFields = collect([
        'Date' => filled($eventDate) ? \Illuminate\Support\Carbon::parse($eventDate)->format('M j, Y') : null,
        'Time' => $eventTime,
        'Organizer' => $eventOrganizer,
        'Venue' => $eventVenue,
    ])->filter(fn (mixed $value): bool => filled($value));
@endphp

@if($eventFields->isNotEmpty())
    <div @class([
        'business-section-panel about-box mb-4' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--local-voices-event' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? 'Event details' }}</p>
        @else
            <div class="business-section-panel__header">
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
                    $label === 'Venue' ? 'col-12' : ($label === 'Organizer' ? 'col-md-6' : 'col-md-3') => ! $sidebarLayout,
                    'news-sidebar-meta-grid__item' => $sidebarLayout,
                    'news-sidebar-meta-grid__item--wide' => $sidebarLayout && $label === 'Venue',
                ])>
                    @if($sidebarLayout)
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ $label }}</strong>
                            <span>{{ $value }}</span>
                        </div>
                    @else
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $label }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
