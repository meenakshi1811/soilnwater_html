@php
    $metaLabels = \App\Support\CommunityPostFormFields::creativeCornerDetailMetaOrder();
    $railLayout = $railLayout ?? false;
    $arrayKeys = [
        'creative_corner_target_audience',
        'creative_corner_mediums',
        'creative_corner_software_tools',
        'creative_corner_materials',
        'creative_corner_themes',
        'creative_corner_competition_categories',
        'creative_corner_commission_options',
        'creative_corner_document_types',
        'creative_corner_comment_settings',
        'creative_corner_creative_licenses',
        'creative_corner_collaboration_roles',
    ];
    $booleanKeys = [
        'creative_corner_submit_to_competition',
        'creative_corner_available_for_sale',
        'creative_corner_custom_orders_accepted',
        'creative_corner_limited_edition',
        'creative_corner_shipping_available',
    ];
    $skipKeys = [
        'creative_corner_location_country',
        'creative_corner_location_state',
        'creative_corner_location_district',
        'creative_corner_location_city',
    ];
    $orderedMeta = collect($metaLabels)
        ->except($skipKeys)
        ->mapWithKeys(function (string $label, string $key) use ($post, $arrayKeys, $booleanKeys) {
            $value = data_get($post->meta, $key);
            if (in_array($key, $booleanKeys, true)) {
                $value = $value ? 'Yes' : null;
            }
            if (in_array($key, $arrayKeys, true) && is_array($value)) {
                $value = $value === [] ? null : implode(', ', $value);
            }

            return [$key => $value];
        })
        ->filter(fn ($value) => filled($value));
@endphp

@if($post->isCreativeCornerPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-palette"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Creative Corner details' }}</h4>
                </div>
            </div>
            <div class="community-detail-grid community-detail-grid--rail">
                @foreach($orderedMeta as $key => $value)
                    <div class="community-detail-item">
                        <span class="community-detail-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        <span class="community-detail-item__value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="community-meta-details mt-4">
            <h4>{{ $heading ?? (($includeAdmin ?? false) ? 'Saved Creative Corner metadata' : 'Creative Corner details') }}</h4>
            <dl class="row mb-0 small">
                @foreach($orderedMeta as $key => $value)
                    <dt class="col-sm-4 text-muted">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                    <dd class="col-sm-8">{{ $value }}</dd>
                @endforeach
            </dl>
        </div>
    @endif
@endif
