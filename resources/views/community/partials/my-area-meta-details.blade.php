@php
    $includeAdmin = $includeAdmin ?? false;
    $metaLabels = \App\Support\CommunityPostFormFields::myAreaDetailMetaOrder();
    $orderedMeta = collect($metaLabels)
        ->mapWithKeys(fn ($label, $key) => [$key => data_get($post->meta, $key)])
        ->filter(fn ($value) => filled($value) || is_bool($value));
    $pillKeys = ['my_area_affected_communities', 'my_area_authorities', 'my_area_poll_options'];
@endphp

@if($post->isMyAreaPost() && ($orderedMeta->isNotEmpty() || $includeAdmin))
    <div class="about-box mt-4 business-meta-grid">
        <h4>{{ $heading ?? ($includeAdmin ? 'Saved My Area metadata' : 'My Area details') }}</h4>

        @if($includeAdmin)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Publish as</span>
                        <span>{{ \App\Support\CommunityContentTaxonomy::myAreaPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Visibility</span>
                        <span>{{ $post->myAreaVisibilityLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Tags</span>
                        <span>{{ !empty($post->tags) ? implode(', ', $post->tags) : '—' }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if($orderedMeta->isNotEmpty())
            <div class="row g-3">
                @foreach($orderedMeta as $key => $value)
                    <div class="col-md-6">
                        <div class="business-meta-item h-100">
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true) && is_array($value))
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($value as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span>{!! nl2br(e(is_bool($value) ? ($value ? 'Yes' : 'No') : (is_array($value) ? implode(', ', $value) : $value))) !!}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
