@php
    $formFieldLabels = \App\Support\CommunityPostFormFields::labels();
    $skipKeys = array_merge(
        \App\Support\CommunityPostFormFields::awarenessStructuredMetaKeys(),
        \App\Support\CommunityPostFormFields::awarenessEngagementStructuredMetaKeys(),
        \App\Models\CommunityPost::structuredLocationMetaKeys(),
        [
            'awareness_video_type',
            'author_bio',
            'campaign_topic',
            'target_audience',
            'call_to_action',
            'related_resource_url',
            'awareness_infographics',
            'awareness_documents',
        ]
    );
    $additionalMeta = collect($post->meta ?? [])
        ->except($skipKeys)
        ->filter(function (mixed $value): bool {
            if (is_array($value)) {
                return array_filter($value, fn (mixed $item): bool => filled($item)) !== [];
            }

            if (is_object($value)) {
                return false;
            }

            return filled($value) || $value === false;
        });
@endphp

@if($post->isAwarenessPost() && $additionalMeta->isNotEmpty())
    <div class="awareness-section-panel about-box mb-4">
        <div class="awareness-section-panel__header">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <h4 class="mb-0">{{ $heading ?? 'Additional details' }}</h4>
        </div>
        <dl class="community-detail-list mb-0">
            @foreach($additionalMeta as $key => $value)
                @if(is_array($value))
                    @php
                        $value = implode(', ', array_values(array_filter($value, fn (mixed $item): bool => filled($item))));
                    @endphp
                @endif
                <div class="community-detail-list__row">
                    <dt>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                    <dd>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</dd>
                </div>
            @endforeach
        </dl>
    </div>
@endif
