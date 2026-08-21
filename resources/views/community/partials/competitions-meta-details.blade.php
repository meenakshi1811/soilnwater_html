@php
    $metaLabels = \App\Support\CommunityPostFormFields::competitionsDetailMetaOrder();
    $railLayout = $railLayout ?? false;
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
    $sidebarSkipKeys = [
        'competitions_date_announcement',
        'competitions_date_registration_opens',
        'competitions_date_registration_closes',
        'competitions_date_submission_deadline',
        'competitions_date_evaluation_period',
        'competitions_date_result',
        'competitions_date_award_ceremony',
        'competitions_organizer_name',
        'competitions_organizer_organization',
        'competitions_organizer_contact_person',
        'competitions_organizer_email',
        'competitions_organizer_phone',
        'competitions_organizer_website',
    ];
    $orderedMeta = collect($metaLabels)
        ->when($railLayout, fn ($collection) => $collection->except($sidebarSkipKeys))
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

@if($post->isCompetitionsPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-trophy"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Competition details' }}</h4>
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
            <h4>{{ $heading ?? (($includeAdmin ?? false) ? 'Saved Competitions metadata' : 'Competition details') }}</h4>
            <dl class="row mb-0 small">
                @foreach($orderedMeta as $key => $value)
                    <dt class="col-sm-4 text-muted">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                    <dd class="col-sm-8">{{ $value }}</dd>
                @endforeach
            </dl>
        </div>
    @endif
@endif
