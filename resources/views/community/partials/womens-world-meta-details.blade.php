@php
    $orderedWomensWorldMeta = \App\Support\CommunityPostFormFields::orderedWomensWorldMetaForDisplay($post);
    $womensWorldMetaLabels = \App\Support\CommunityPostFormFields::womensWorldDetailMetaOrder();
@endphp

@if($post->isWomensWorldPost() && $orderedWomensWorldMeta->isNotEmpty())
    <div class="about-box mt-4">
        <h4>{{ $heading ?? "Women's World details" }}</h4>
        <div class="row g-3">
            @foreach($orderedWomensWorldMeta as $key => $value)
                <div class="col-md-6">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">{{ $womensWorldMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        <span>{{ $value }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
