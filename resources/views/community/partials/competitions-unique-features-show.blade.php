@if($post->isCompetitionsPost())
    @php
        $uniqueFeatures = $post->competitionsUniqueFeatureLabels();
        $originSections = (array) data_get($post->meta, 'competitions_origin_sections', []);
        $awardBadges = (array) data_get($post->meta, 'competitions_award_badges', []);
        $leaderboardTypes = (array) data_get($post->meta, 'competitions_leaderboard_types', []);
        $fraudProtections = (array) data_get($post->meta, 'competitions_voting_fraud_protections', []);
        $ecommerceOptions = (array) data_get($post->meta, 'competitions_ecommerce_options', []);
        $certificateTypes = (array) data_get($post->meta, 'competitions_digital_certificate_types', []);
    @endphp

    @if($uniqueFeatures !== [])
        <div class="community-competition-flagship card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                    <div>
                        <h4 class="h5 mb-1">SoilnWater competition features</h4>
                        <p class="text-muted small mb-0">Flagship capabilities enabled for this competition.</p>
                    </div>
                    <span class="badge bg-primary">Competition engine</span>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($uniqueFeatures as $feature)
                        <span class="badge bg-warning-subtle text-dark border py-2 px-3">{{ $feature }}</span>
                    @endforeach
                </div>

                @if(data_get($post->meta, 'competitions_enable_multi_section') && $originSections !== [])
                    <div class="mb-3">
                        <strong>Originating sections</strong>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($originSections as $section)
                                <span class="badge bg-light text-dark border">{{ $section }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(data_get($post->meta, 'competitions_enable_achievement_badges') && $awardBadges !== [])
                    <div class="mb-3">
                        <strong>Award badges</strong>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($awardBadges as $badge)
                                <span class="badge bg-success-subtle text-success border">{{ $badge }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(data_get($post->meta, 'competitions_enable_leaderboards') && $leaderboardTypes !== [])
                    <div class="mb-3">
                        <strong>Leaderboards</strong>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($leaderboardTypes as $type)
                                <span class="badge bg-info-subtle text-info-emphasis border">{{ $type }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(data_get($post->meta, 'competitions_enable_voting_fraud_protection') && $fraudProtections !== [])
                    <div class="mb-3">
                        <strong>Voting fraud protection</strong>
                        <ul class="small mb-0 mt-1">
                            @foreach($fraudProtections as $measure)
                                <li>{{ $measure }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(data_get($post->meta, 'competitions_enable_ecommerce') && $ecommerceOptions !== [])
                    <div class="mb-3">
                        <strong>E-commerce options</strong>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($ecommerceOptions as $option)
                                <span class="badge bg-light text-dark border">{{ $option }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(data_get($post->meta, 'competitions_enable_digital_certificates'))
                    <div>
                        <strong>Digital certificates</strong>
                        @if($certificateTypes !== [])
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @foreach($certificateTypes as $type)
                                    <span class="badge bg-light text-dark border">{{ $type }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if(data_get($post->meta, 'competitions_enable_verifiable_certificate_ids'))
                            <p class="small text-muted mb-0 mt-2">Verifiable certificate IDs with QR verification enabled.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
