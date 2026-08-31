@php
    $post = $post ?? null;
    if (! $post?->isReligionSpiritualityPost()) {
        return;
    }

    $audiences = $post->religionSpiritualityTargetAudiences();
    $meditationTopics = $post->religionSpiritualityMeditationTopics();
    $capabilities = [
        ['label' => 'Digital Pilgrimage Guide', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_digital_pilgrimage_guide'), 'icon' => 'fa-map-location-dot'],
        ['label' => 'Festival Calendar', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_festival_calendar'), 'icon' => 'fa-calendar-days'],
        ['label' => 'Service Directory', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_community_service_directory'), 'icon' => 'fa-hands-holding-heart'],
        ['label' => 'Wisdom Library', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_wisdom_library'), 'icon' => 'fa-book'],
        ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
        ['label' => 'Questions', 'enabled' => (bool) $post->allow_questions, 'icon' => 'fa-circle-question'],
        ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
        ['label' => 'Poll', 'enabled' => (bool) $post->allowsPoll(), 'icon' => 'fa-square-poll-vertical'],
    ];
    $hasContent = $audiences !== [] || $meditationTopics !== [] || $capabilities !== [];
@endphp

@if($hasContent)
    <div class="community-news-sidebar__card community-news-sidebar__card--religion-spirituality-details">
        <p class="community-news-sidebar__label">Religion &amp; Spirituality</p>

        <div class="community-news-sidebar__pill-groups">
            @if($audiences !== [])
                <div class="community-news-sidebar__pill-group">
                    <span class="community-news-sidebar__pill-label">Target audience</span>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($audiences as $audience)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $audience }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($meditationTopics !== [])
                <div class="community-news-sidebar__pill-group">
                    <span class="community-news-sidebar__pill-label">Meditation &amp; wellness</span>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($meditationTopics as $topic)
                            <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $topic }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="community-news-sidebar__pill-group">
                <span class="community-news-sidebar__pill-label">Post capabilities</span>
                <div class="rs-capability-grid">
                    @foreach($capabilities as $capability)
                        <span class="rs-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                            <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                            {{ $capability['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
