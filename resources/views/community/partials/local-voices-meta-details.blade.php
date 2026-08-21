@php
    $metaLabels = \App\Support\CommunityPostFormFields::localVoiceDetailMetaOrder();
    $railLayout = $railLayout ?? false;
    $locationKeys = \App\Models\CommunityPost::structuredLocationMetaKeys();
    $skipKeys = array_merge($locationKeys, [
        'local_voice_suggested_solution',
        'local_voice_estimated_benefit',
        'local_voice_authorities',
        'local_voice_call_for_action',
        'local_voice_hero_name',
        'local_voice_hero_location',
        'local_voice_hero_contribution',
        'local_voice_hero_achievements',
        'local_voice_initiatives',
        'local_voice_event_date',
        'local_voice_event_time',
        'local_voice_event_venue',
        'local_voice_event_organizer',
        'local_voice_poll_question',
        'local_voice_poll_options',
        'local_voice_allow_support',
        'local_voice_allow_follow',
    ]);
    $displayMeta = collect($metaLabels)
        ->except($skipKeys)
        ->mapWithKeys(function (string $label, string $key) use ($post): array {
            $value = data_get($post->meta, $key);

            if ($key === 'local_voice_category' && blank($value)) {
                $value = $post->category;
            }

            if (in_array($key, ['local_voice_affected_communities', 'local_voice_authorities', 'local_voice_call_for_action', 'local_voice_initiatives'], true) && is_array($value)) {
                $value = implode(', ', array_values(array_filter($value, fn (mixed $item): bool => filled($item))));
            }

            if ($key === 'local_voice_visibility' && filled($value)) {
                $value = $post->localVoiceVisibilityLabel();
            }

            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }

            return [$key => $value];
        })
        ->filter(fn (mixed $value): bool => filled($value));
@endphp

@if($post->isLocalVoicesPost() && $displayMeta->isNotEmpty())
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-microphone-lines"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Local Voices details' }}</h4>
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
                <i class="fa-solid fa-microphone-lines" aria-hidden="true"></i>
                <h4 class="mb-0">{{ $heading ?? 'Local Voices details' }}</h4>
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
