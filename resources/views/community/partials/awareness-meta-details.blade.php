@php
    $orderedAwarenessMeta = \App\Support\CommunityPostFormFields::orderedAwarenessMetaForDisplay($post);
    $awarenessMetaLabels = \App\Support\CommunityPostFormFields::awarenessDetailMetaOrder();
@endphp

@if($post->isAwarenessPost() && $orderedAwarenessMeta->isNotEmpty())
    <div class="about-box mt-4 awareness-meta-grid">
        <h4>{{ $heading ?? 'Awareness details' }}</h4>
        <div class="row g-3">
            @foreach($orderedAwarenessMeta as $key => $value)
                <div class="{{ $key === 'awareness_target_audience' ? 'col-12' : 'col-md-6' }}">
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
