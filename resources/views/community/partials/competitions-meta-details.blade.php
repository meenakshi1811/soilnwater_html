@php
    $metaLabels = \App\Support\CommunityPostFormFields::competitionsDetailMetaOrder();
    $arrayKeys = [
        'competitions_eligibility',
        'competitions_themes',
        'competitions_submission_types',
        'competitions_team_details',
        'competitions_entry_fields',
        'competitions_supporting_documents',
        'competitions_judging_criteria',
        'competitions_public_voting_methods',
        'competitions_comment_settings',
        'competitions_copyright_options',
        'competitions_origin_sections',
        'competitions_award_badges',
        'competitions_leaderboard_types',
        'competitions_voting_fraud_protections',
        'competitions_ecommerce_options',
        'competitions_digital_certificate_types',
    ];
    $booleanKeys = [
        'competitions_registration_required',
        'competitions_team_allowed',
        'competitions_individual_only',
        'competitions_prize_certificates',
        'competitions_prize_trophies',
        'competitions_prize_cash',
        'competitions_prize_gift_voucher',
        'competitions_prize_internship',
        'competitions_prize_scholarship',
        'competitions_prize_featured_homepage',
        'competitions_certificate_participation',
        'competitions_certificate_winner',
        'competitions_certificate_merit',
        'competitions_certificate_digital',
        'competitions_enable_multi_section',
        'competitions_enable_auto_portfolio',
        'competitions_enable_entry_qr_codes',
        'competitions_enable_achievement_badges',
        'competitions_enable_leaderboards',
        'competitions_enable_institution_dashboard',
        'competitions_enable_sponsored_branding',
        'competitions_enable_ecommerce',
        'competitions_enable_voting_fraud_protection',
        'competitions_enable_digital_certificates',
        'competitions_enable_verifiable_certificate_ids',
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

@if($post->isCompetitionsPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
    <div class="community-meta-details mt-4">
        <h4>{{ $heading ?? (($includeAdmin ?? false) ? 'Saved Competitions metadata' : 'Competition details') }}</h4>
        <dl class="row mb-0 small">
            @foreach($orderedMeta as $key => $value)
                <dt class="col-sm-4 text-muted">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                <dd class="col-sm-8">{{ $value }}</dd>
            @endforeach
        </dl>
    </div>
@endif
