@php
    $metaLabels = \App\Support\CommunityPostFormFields::creativeCornerDetailMetaOrder();
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
    $orderedMeta = collect($metaLabels)->mapWithKeys(function (string $label, string $key) use ($post, $arrayKeys, $booleanKeys) {
        $value = data_get($post->meta, $key);
        if (in_array($key, $booleanKeys, true)) {
            $value = $value ? 'Yes' : null;
        }
        if (in_array($key, $arrayKeys, true) && is_array($value)) {
            $value = $value === [] ? null : implode(', ', $value);
        }

        return [$key => $value];
    })->filter(fn ($value) => filled($value));
@endphp

@if($post->isCreativeCornerPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
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
