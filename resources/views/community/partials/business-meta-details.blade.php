@php
    $orderedBusinessMeta = \App\Support\CommunityPostFormFields::orderedBusinessMetaForDisplay($post);
    $businessMetaLabels = \App\Support\CommunityPostFormFields::businessDetailMetaOrder();
    $skipKeys = [
        'business_category', 'business_content_type', 'business_stage', 'business_industry',
        'business_name', 'business_author_designation', 'business_profile_type',
        'business_target_audience', 'business_challenges', 'business_opportunity_type',
        'business_market_segments', 'business_themes', 'business_ask_community',
        'business_useful_links', 'business_government_schemes', 'business_training_programs',
        'business_industry_resources', 'business_contact_options', 'business_video_type',
    ];
    $displayMeta = $orderedBusinessMeta->except($skipKeys);
@endphp

@if($post->isBusinessPost() && $displayMeta->isNotEmpty())
    <div class="about-box mt-4 business-meta-grid">
        <h4>{{ $heading ?? 'Business details' }}</h4>
        <div class="row g-3">
            @foreach($displayMeta as $key => $value)
                <div class="{{ in_array($key, ['business_poll_options'], true) ? 'col-12' : 'col-md-6' }}">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">{{ $businessMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        @if(in_array($key, ['business_useful_links', 'business_government_schemes', 'business_training_programs', 'business_industry_resources'], true))
                            <div class="business-resource-text">{!! nl2br(e((string) $value)) !!}</div>
                        @else
                            <span>{{ $value }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
