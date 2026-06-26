@if($post->isCommunityIssuesPost())
    @php
        $issueCategory = data_get($post->meta, 'community_issue_category') ?: $post->category;
        $issueType = data_get($post->meta, 'community_issue_type');
        $severity = data_get($post->meta, 'community_issue_severity');
        $affectedPopulation = data_get($post->meta, 'community_issue_affected_population');
        $affectedGroups = array_values(array_filter((array) data_get($post->meta, 'community_issue_affected_groups', [])));
        $authority = data_get($post->meta, 'community_issue_authority');
        $alreadyReported = data_get($post->meta, 'community_issue_already_reported');
        $supportRequests = array_values(array_filter((array) data_get($post->meta, 'community_issue_support_requests', [])));
        $firstNoticedOn = data_get($post->meta, 'community_issue_first_noticed_on');
        $isRecurring = data_get($post->meta, 'community_issue_is_recurring');
        $frequency = data_get($post->meta, 'community_issue_frequency');
        $solution = data_get($post->meta, 'community_issue_suggested_solution');
        $statusTracker = data_get($post->meta, 'community_issue_status_tracker');
        $resolutionTimeline = $post->communityIssueResolutionTimelineEntries();
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $landmark = data_get($post->meta, 'location_landmark');
        $statusSteps = \App\Support\CommunityContentTaxonomy::communityIssueStatusSteps();
        $reportEngagement = $reportEngagement ?? ['supports_count' => 0, 'agreements_count' => 0, 'follows_count' => 0];
        $supportCount = (int) ($reportEngagement['supports_count'] ?? 0);
        $verificationCount = (int) ($reportEngagement['agreements_count'] ?? 0);
        $isEscalated = $post->isCommunityIssueEscalated($supportCount);
        $publishAsLabel = \App\Support\CommunityContentTaxonomy::communityIssuePublishAsOptions()[$post->resolvedPublishAs()]
            ?? $post->publishAsLabel();
        $currentStatusIndex = filled($statusTracker) ? array_search($statusTracker, $statusSteps, true) : false;
        $severityTone = match ($severity) {
            'Emergency', 'Critical' => 'emergency',
            'High' => 'high',
            'Medium' => 'medium',
            default => 'low',
        };
        $capabilities = [
            ['label' => 'Support campaign', 'enabled' => (bool) data_get($post->meta, 'community_issue_allow_campaign', true), 'icon' => 'fa-people-group'],
            ['label' => 'I support this issue', 'enabled' => $post->allowsCommunityIssueSupport(), 'icon' => 'fa-hand-holding-heart'],
            ['label' => 'Community verification', 'enabled' => $post->allowsCommunityIssueVerification(), 'icon' => 'fa-circle-check'],
            ['label' => 'Follow issue', 'enabled' => $post->allowsCommunityIssueFollow(), 'icon' => 'fa-bell'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Add evidence', 'enabled' => (bool) $post->allow_feedback, 'icon' => 'fa-camera'],
            ['label' => 'Suggestions', 'enabled' => (bool) $post->allow_suggestions, 'icon' => 'fa-lightbulb'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allow_poll, 'icon' => 'fa-square-poll-vertical'],
        ];
    @endphp

    <div class="ci-show-overview">
        <div class="ci-show-overview__kicker">Community Issues · Civic reporting</div>
        <div class="ci-show-overview__title">Structured civic issue with location, evidence, and resolution tracking</div>
        <div class="ci-show-overview__chips">
            @if(filled($issueCategory))
                <span class="ci-show-chip">{{ $issueCategory }}</span>
            @endif
            @if(filled($issueType))
                <span class="ci-show-chip">{{ $issueType }}</span>
            @endif
            @if(filled($severity))
                <span class="ci-show-chip ci-show-chip--severity-{{ $severityTone }}">{{ $severity }} severity</span>
            @endif
            @if(filled($statusTracker))
                <span class="ci-show-chip">{{ $statusTracker }}</span>
            @endif
            @if(filled($publishAsLabel) && $post->resolvedPublishAs() !== 'public_profile')
                <span class="ci-show-chip">{{ $publishAsLabel }}</span>
            @endif
        </div>
    </div>

    @if($isEscalated)
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-4" role="status">
            <i class="fa-solid fa-triangle-exclamation mt-1" aria-hidden="true"></i>
            <div>
                <strong>High priority issue</strong>
                <p class="mb-0 small">This issue exceeded {{ number_format($post->communityIssueEscalationThreshold()) }} community supporters. Authors and admins have been alerted to give it urgent attention.</p>
            </div>
        </div>
    @endif

    @if((bool) data_get($post->meta, 'community_issue_allow_campaign', true) && $post->allowsCommunityIssueSupport())
        <div class="ci-campaign-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="text-success fw-bold"><i class="fa-solid fa-people-group me-1"></i>Community support campaign</div>
                <div class="small text-muted">Residents can click “I support this issue” to build public pressure for resolution.</div>
            </div>
            <div class="text-end">
                <div class="display-6 fw-bold text-success lh-1">{{ number_format($supportCount) }}</div>
                <div class="small text-muted">{{ \Illuminate\Support\Str::plural('supporter', $supportCount) }}</div>
            </div>
        </div>
    @endif

    @if(filled($statusTracker) || $resolutionTimeline !== [])
        <div class="business-section-panel about-box mb-4 border-success">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-route text-success" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Status tracking</h4>
                    <p class="text-muted small mb-0">Live civic resolution progress for this issue.</p>
                </div>
            </div>

            @if(filled($statusTracker))
                <div class="ci-status-stepper" aria-label="Issue status steps">
                    @foreach($statusSteps as $index => $step)
                        @php
                            $isCurrent = $step === $statusTracker;
                            $isComplete = $currentStatusIndex !== false && $index < $currentStatusIndex;
                        @endphp
                        <div class="ci-status-step {{ $isCurrent ? 'is-current' : ($isComplete ? 'is-complete' : '') }}">
                            <span class="ci-status-step__label">{{ $step }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($resolutionTimeline !== [])
                <h5 class="h6 mb-2">Resolution tracker</h5>
                <ul class="list-unstyled mb-0">
                    @foreach($resolutionTimeline as $entry)
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <i class="fa-solid fa-circle-dot text-success mt-1" aria-hidden="true"></i>
                            <span>{{ $entry }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="row g-3 mb-4">
        @if(filled($affectedPopulation) || $affectedGroups !== [])
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                        <h4 class="mb-0">Issue impact</h4>
                    </div>
                    @if(filled($affectedPopulation))
                        <div class="business-meta-item mb-3">
                            <span class="business-meta-item__label">Affected population</span>
                            <span>{{ $affectedPopulation }}</span>
                        </div>
                    @endif
                    @if($affectedGroups !== [])
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($affectedGroups as $group)
                                <span class="badge bg-light text-dark border">{{ $group }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if(filled($firstNoticedOn) || filled($isRecurring) || filled($frequency))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                        <h4 class="mb-0">Issue timeline</h4>
                    </div>
                    <div class="row g-3">
                        @if(filled($firstNoticedOn))
                            <div class="col-md-6">
                                <div class="business-meta-item">
                                    <span class="business-meta-item__label">First noticed</span>
                                    <span>{{ \Illuminate\Support\Carbon::parse($firstNoticedOn)->format('d M Y') }}</span>
                                </div>
                            </div>
                        @endif
                        @if(filled($isRecurring))
                            <div class="col-md-6">
                                <div class="business-meta-item">
                                    <span class="business-meta-item__label">Recurring</span>
                                    <span>{{ ucfirst($isRecurring) }}</span>
                                </div>
                            </div>
                        @endif
                        @if(filled($frequency))
                            <div class="col-md-6">
                                <div class="business-meta-item">
                                    <span class="business-meta-item__label">Frequency</span>
                                    <span>{{ $frequency }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($structuredLocation->isNotEmpty() || filled($landmark))
        <div class="business-section-panel about-box mb-4 border-danger">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot text-danger" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Location details</h4>
                    <p class="text-muted small mb-0">Country, state, district, city, locality, landmark, and GPS map pin.</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($structuredLocation as $key => $value)
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
                @if(filled($landmark))
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Landmark</span>
                            <span>{{ $landmark }}</span>
                        </div>
                    </div>
                @endif
            </div>
            @if($post->hasMapCoordinates())
                <p class="mb-2 mt-3"><strong>GPS location:</strong> {{ $post->location_lat }}, {{ $post->location_lng }}</p>
                <div class="ratio ratio-16x9 border rounded overflow-hidden">
                    <iframe
                        title="Community issue GPS map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                    ></iframe>
                </div>
            @endif
        </div>
    @endif

    @if($post->featured_image_path || $post->communityIssuePhotoEvidence() !== [] || $post->hasVideo() || $post->communityIssueDocuments() !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-camera text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Evidence upload</h4>
            </div>

            @if($post->featuredImageUrl())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="{{ $post->featuredImageUrl() }}" alt="Featured issue evidence" class="img-fluid rounded border">
                </div>
            @endif

            @if($post->communityIssuePhotoEvidence() !== [])
                <div class="business-gallery-grid mb-3">
                    @foreach($post->communityIssuePhotoEvidence() as $photo)
                        <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                            <img src="{{ data_get($photo, 'url') }}" alt="Issue evidence" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif

            @if($post->hasVideo())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Video evidence</div>
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Video evidence" allowfullscreen></iframe></div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                    @endif
                </div>
            @endif

            @if($post->communityIssueDocuments() !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($post->communityIssueDocuments() as $document)
                        <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                            <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="row g-3 mb-4">
        @if(filled($authority))
            <div class="col-md-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                        <h4 class="mb-0">Responsible authority</h4>
                    </div>
                    <span class="badge bg-light text-dark border">{{ $authority }}</span>
                </div>
            </div>
        @endif

        @if($alreadyReported === 'yes')
            <div class="col-md-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        <h4 class="mb-0">Prior actions taken</h4>
                    </div>
                    @if(filled(data_get($post->meta, 'community_issue_complaint_number')))
                        <div class="business-meta-item mb-2">
                            <span class="business-meta-item__label">Complaint number</span>
                            <span>{{ data_get($post->meta, 'community_issue_complaint_number') }}</span>
                        </div>
                    @endif
                    @if(filled(data_get($post->meta, 'community_issue_complaint_date')))
                        <div class="business-meta-item mb-2">
                            <span class="business-meta-item__label">Complaint date</span>
                            <span>{{ \Illuminate\Support\Carbon::parse(data_get($post->meta, 'community_issue_complaint_date'))->format('d M Y') }}</span>
                        </div>
                    @endif
                    @if(filled(data_get($post->meta, 'community_issue_department_contacted')))
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Department contacted</span>
                            <span>{{ data_get($post->meta, 'community_issue_department_contacted') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if($supportRequests !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-handshake text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Community support request</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($supportRequests as $request)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $request }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(filled($solution))
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-lightbulb text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Suggested solution</h4>
            </div>
            <p class="mb-0">{!! nl2br(e($solution)) !!}</p>
        </div>
    @endif

    @if(!empty($post->tags))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                <h4 class="mb-0">Tags</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                    <span class="badge bg-light text-dark border">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->allowsCommunityIssueVerification() && $verificationCount > 0)
        <div class="alert alert-success py-2 px-3 mb-4">
            <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>
            Verified by <strong>{{ number_format($verificationCount) }}</strong> {{ \Illuminate\Support\Str::plural('resident', $verificationCount) }}
        </div>
    @endif

    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
            <h4 class="mb-0">Community actions enabled</h4>
        </div>
        <div class="ci-capability-grid">
            @foreach($capabilities as $capability)
                <span class="ci-capability-pill {{ $capability['enabled'] ? 'is-on' : 'is-off' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>{{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="ci-hub-link-card d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <strong>Explore more civic issues nearby</strong>
            <div class="small text-muted">View the SoilnWater Issues Hub heat map, dashboard, and community champions.</div>
        </div>
        <a href="{{ route('community.community-issues.index') }}" class="btn btn-outline-danger btn-sm">
            <i class="fa-solid fa-map-location-dot me-1"></i>Open Issues Hub
        </a>
    </div>
@endif
