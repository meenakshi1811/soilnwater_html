@if($post->isCompetitionsPost())
    @php
        $portalSidebarLayout = $portalSidebarLayout ?? false;
        $competitionType = data_get($post->meta, 'competitions_competition_type');
        $category = data_get($post->meta, 'competitions_category', $post->category);
        $themes = (array) data_get($post->meta, 'competitions_themes', []);
        $eligibility = (array) data_get($post->meta, 'competitions_eligibility', []);
        $submissionTypes = (array) data_get($post->meta, 'competitions_submission_types', []);
        $originSections = (array) data_get($post->meta, 'competitions_origin_sections', []);
        $importantDates = array_filter([
            'Announcement' => data_get($post->meta, 'competitions_date_announcement'),
            'Registration opens' => data_get($post->meta, 'competitions_date_registration_opens'),
            'Registration closes' => data_get($post->meta, 'competitions_date_registration_closes'),
            'Submission deadline' => data_get($post->meta, 'competitions_date_submission_deadline'),
            'Evaluation period' => data_get($post->meta, 'competitions_date_evaluation_period'),
            'Result date' => data_get($post->meta, 'competitions_date_result'),
            'Award ceremony' => data_get($post->meta, 'competitions_date_award_ceremony'),
        ]);
        $prizes = array_filter([
            'First prize' => data_get($post->meta, 'competitions_prize_first'),
            'Second prize' => data_get($post->meta, 'competitions_prize_second'),
            'Third prize' => data_get($post->meta, 'competitions_prize_third'),
            'Consolation prize' => data_get($post->meta, 'competitions_prize_consolation'),
        ]);
        $jury = array_values(array_filter((array) data_get($post->meta, 'competitions_jury', [])));
        $sponsors = array_values(array_filter((array) data_get($post->meta, 'competitions_sponsors', [])));
        $organizerLogo = data_get($post->meta, 'competitions_organizer_logo');
        $capabilities = [
            ['label' => 'Portfolio', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_auto_portfolio'), 'icon' => 'fa-briefcase'],
            ['label' => 'QR entries', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_entry_qr_codes'), 'icon' => 'fa-qrcode'],
            ['label' => 'Badges', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_achievement_badges'), 'icon' => 'fa-award'],
            ['label' => 'Leaderboard', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_leaderboards'), 'icon' => 'fa-ranking-star'],
            ['label' => 'Institutions', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_institution_dashboard'), 'icon' => 'fa-school'],
            ['label' => 'Sponsored', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_sponsored_branding'), 'icon' => 'fa-hand-holding-dollar'],
            ['label' => 'Marketplace', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_ecommerce'), 'icon' => 'fa-store'],
            ['label' => 'Certificates', 'enabled' => (bool) data_get($post->meta, 'competitions_enable_digital_certificates'), 'icon' => 'fa-certificate'],
        ];
    @endphp

    <div class="comp-show-overview">
        <div class="comp-show-overview__kicker">Competitions · SoilnWater community</div>
        <p class="comp-show-overview__tagline mb-0">Join this SoilnWater competition — review rules, dates, prizes, and how to participate.</p>
        <div class="d-flex flex-wrap gap-2 mt-3">
            @if(filled($competitionType))
                <span class="comp-show-chip comp-show-chip--highlight">{{ $competitionType }}</span>
            @endif
            @if(filled($category))
                <span class="comp-show-chip">{{ $category }}</span>
            @endif
            @if(filled(data_get($post->meta, 'competitions_level')))
                <span class="comp-show-chip">{{ data_get($post->meta, 'competitions_level') }}</span>
            @endif
            @if(filled(data_get($post->meta, 'competitions_primary_origin_section')))
                <span class="comp-show-chip">From {{ data_get($post->meta, 'competitions_primary_origin_section') }}</span>
            @endif
        </div>
    </div>

    @unless($portalSidebarLayout)
    @if(data_get($post->meta, 'competitions_date_submission_deadline'))
        <div class="comp-deadline-banner">
            <strong><i class="fa-solid fa-clock me-1" aria-hidden="true"></i>Submission deadline:</strong>
            {{ data_get($post->meta, 'competitions_date_submission_deadline') }}
            @if(data_get($post->meta, 'competitions_registration_required'))
                <span class="ms-2 badge bg-warning text-dark">Registration required</span>
            @endif
        </div>
    @endif
    @endunless

    @if(collect($capabilities)->contains(fn ($item) => $item['enabled']))
        <div class="comp-capability-grid">
            @foreach($capabilities as $capability)
                <div class="comp-capability-card {{ $capability['enabled'] ? 'is-enabled' : '' }}">
                    <div class="comp-capability-card__icon"><i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i></div>
                    <div class="comp-capability-card__label">{{ $capability['label'] }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <section class="community-competition-details card border-0 shadow-sm mb-4">
        <div class="card-body">
            @if($competitionType)
                <div class="mb-3">
                    <span class="badge bg-warning text-dark">{{ $competitionType }}</span>
                    @if(data_get($post->meta, 'competitions_level'))
                        <span class="badge bg-light text-dark border">{{ data_get($post->meta, 'competitions_level') }}</span>
                    @endif
                </div>
            @endif

            @unless($portalSidebarLayout)
            @if(data_get($post->meta, 'competitions_organizer_name') || data_get($post->meta, 'competitions_organizer_organization'))
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if(is_array($organizerLogo) && data_get($organizerLogo, 'url'))
                        <img src="{{ data_get($organizerLogo, 'url') }}" alt="Organizer logo" class="rounded border" style="max-height:56px;">
                    @endif
                    <div>
                        <strong>Organizer</strong>
                        <div>{{ data_get($post->meta, 'competitions_organizer_name') }}</div>
                        @if(data_get($post->meta, 'competitions_organizer_organization'))
                            <div class="text-muted small">{{ data_get($post->meta, 'competitions_organizer_organization') }}</div>
                        @endif
                    </div>
                </div>
            @endif
            @endunless

            @if($eligibility !== [])
                <div class="mb-3">
                    <strong>Eligibility</strong>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @foreach($eligibility as $item)
                            <span class="badge bg-light text-dark border">{{ $item }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($themes !== [])
                <div class="mb-3">
                    <strong>Theme</strong>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @foreach($themes as $theme)
                            <span class="badge bg-success-subtle text-success border">{{ $theme }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($submissionTypes !== [])
                <div class="mb-3">
                    <strong>Submission types</strong>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @foreach($submissionTypes as $type)
                            <span class="badge bg-light text-dark border">{{ $type }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @unless($portalSidebarLayout)
            @if($importantDates !== [])
                <div class="mb-3">
                    <strong>Important dates</strong>
                    <ul class="list-unstyled small mb-0 mt-1">
                        @foreach($importantDates as $label => $value)
                            <li><strong>{{ $label }}:</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @endunless

            @if($prizes !== [])
                <div class="mb-3">
                    <strong>Prizes</strong>
                    <ul class="list-unstyled small mb-0 mt-1">
                        @foreach($prizes as $label => $value)
                            <li><strong>{{ $label }}:</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($jury !== [])
                <div class="mb-3">
                    <strong>Jury</strong>
                    <div class="row g-3 mt-1">
                        @foreach($jury as $member)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <div class="d-flex align-items-start gap-2">
                                        @if(is_array(data_get($member, 'photo')) && data_get($member, 'photo.url'))
                                            <img src="{{ data_get($member, 'photo.url') }}" alt="{{ data_get($member, 'name') }}" class="rounded border" style="width:48px;height:48px;object-fit:cover;">
                                        @endif
                                        <div>
                                            <strong>{{ data_get($member, 'name') }}</strong>
                                            @if(data_get($member, 'designation'))
                                                <div class="small text-muted">{{ data_get($member, 'designation') }}</div>
                                            @endif
                                            @if(data_get($member, 'organization'))
                                                <div class="small">{{ data_get($member, 'organization') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($sponsors !== [])
                <div class="mb-3">
                    <strong>Sponsors</strong>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        @foreach($sponsors as $sponsor)
                            <div class="border rounded p-3 bg-white text-center" style="min-width:140px;">
                                @if(is_array(data_get($sponsor, 'logo')) && data_get($sponsor, 'logo.url'))
                                    <img src="{{ data_get($sponsor, 'logo.url') }}" alt="{{ data_get($sponsor, 'name') }}" class="mb-2" style="max-height:48px;">
                                @endif
                                <div class="small fw-semibold">{{ data_get($sponsor, 'name') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @include('community.partials.competitions-unique-features-show', ['post' => $post])
        </div>
    </section>
@endif
