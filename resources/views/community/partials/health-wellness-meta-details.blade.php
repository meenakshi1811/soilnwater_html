@php
    $metaOrder = ['wellness_topic', 'target_audience', 'wellness_summary', 'medical_disclaimer_ack'];
    $metaLabels = \App\Support\CommunityPostFormFields::labels();
    $textareaKeys = ['wellness_summary'];
    $orderedMeta = collect($metaOrder)
        ->mapWithKeys(fn (string $key): array => [$key => data_get($post->meta, $key)])
        ->filter(fn (mixed $value): bool => filled($value) || $value === false);
    $sidebarLayout = $sidebarLayout ?? false;
@endphp

@if($post->content_type === 'health-wellness' && $orderedMeta->isNotEmpty())
    <div @class([
        'about-box mt-4 business-meta-grid' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--wellness-details' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? 'Health & Wellness details' }}</p>
        @else
            <h4>{{ $heading ?? 'Health & Wellness details' }}</h4>
        @endif

        <div @class([
            'row g-3' => ! $sidebarLayout,
            'news-sidebar-meta-grid' => $sidebarLayout,
        ])>
            @foreach($orderedMeta as $key => $value)
                <div @class([
                    in_array($key, $textareaKeys, true) ? 'col-12' : 'col-md-6' => ! $sidebarLayout,
                    'news-sidebar-meta-grid__item' => $sidebarLayout,
                    'news-sidebar-meta-grid__item--wide' => $sidebarLayout && in_array($key, $textareaKeys, true),
                ])>
                    <div @class(['business-meta-item' => ! $sidebarLayout, 'border rounded p-3 h-100 bg-light' => $sidebarLayout])>
                        <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        @if(in_array($key, $textareaKeys, true))
                            <span>{!! nl2br(e($value)) !!}</span>
                        @else
                            <span>{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
