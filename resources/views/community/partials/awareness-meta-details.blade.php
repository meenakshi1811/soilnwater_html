@php
    $orderedAwarenessMeta = \App\Support\CommunityPostFormFields::orderedAwarenessMetaForDisplay($post);
    $awarenessMetaLabels = \App\Support\CommunityPostFormFields::awarenessDetailMetaOrder();
    $displayMeta = $orderedAwarenessMeta->except([
        'awareness_posted_by',
        'awareness_organization_name',
    ]);
@endphp

@if($post->isAwarenessPost() && $displayMeta->isNotEmpty())
    <div class="awareness-section-panel about-box mb-4">
        <div class="awareness-section-panel__header">
            <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
            <h4 class="mb-0">{{ $heading ?? 'Awareness details' }}</h4>
        </div>
        <div class="row g-3">
            @foreach($displayMeta as $key => $value)
                <div class="{{ $key === 'awareness_target_audience' ? 'col-12' : 'col-md-6 col-lg-4' }}">
                    <div class="awareness-meta-item">
                        <span class="awareness-meta-item__label">{{ $awarenessMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        @if($key === 'awareness_target_audience')
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(array_filter(array_map('trim', explode(',', (string) $value))) as $item)
                                    <span class="awareness-audience-pill">{{ $item }}</span>
                                @endforeach
                            </div>
                        @else
                            <span>{{ $value }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
