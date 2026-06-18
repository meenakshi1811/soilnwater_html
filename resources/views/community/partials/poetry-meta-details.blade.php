@php
    $orderedPoetryMeta = \App\Support\CommunityPostFormFields::orderedPoetryMetaForDisplay($post)
        ->except([
            'poetry_inspiration',
            'poetry_type',
            'sub_category',
            'poem_language',
            'reading_time',
            'poetry_series_name',
            'poetry_series_part',
        ]);
    $poetryMetaLabels = \App\Support\CommunityPostFormFields::poetryDetailMetaOrder();
    $regionalLocation = collect(\App\Support\CommunityPostFormFields::poetryRegionalLocationOrder())
        ->mapWithKeys(fn (string $label, string $key): array => [$key => data_get($post->meta, $key)])
        ->filter(fn (mixed $value): bool => filled($value));
    $hasContent = $orderedPoetryMeta->isNotEmpty() || $regionalLocation->isNotEmpty();
@endphp

@if($hasContent)
    <div class="about-box mt-4 poetry-meta-panel">
        <h4>{{ $heading ?? 'Poetry details' }}</h4>

        @if($orderedPoetryMeta->isNotEmpty())
            <div class="row g-3 {{ $regionalLocation->isNotEmpty() ? 'mb-3' : '' }}">
                @foreach($orderedPoetryMeta as $key => $value)
                    <div class="{{ in_array($key, ['poetry_themes', 'poetry_target_audience', 'dedication'], true) ? 'col-12' : 'col-md-6' }}">
                        <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-1">{{ $poetryMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            @if(in_array($key, ['poetry_themes', 'poetry_target_audience'], true))
                                <div class="story-meta-pills">
                                    @foreach(array_filter(array_map('trim', explode(',', (string) $value))) as $item)
                                        <span class="badge bg-white text-dark border story-meta-pill {{ $key === 'poetry_target_audience' ? 'story-meta-pill--audience' : 'story-meta-pill--theme' }}">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($regionalLocation->isNotEmpty())
            <h5 class="h6 mb-3">Regional location</h5>
            <div class="row g-3">
                @foreach($regionalLocation as $key => $value)
                    <div class="col-md-6 col-lg-3">
                        <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-1">{{ \App\Support\CommunityPostFormFields::poetryRegionalLocationOrder()[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
