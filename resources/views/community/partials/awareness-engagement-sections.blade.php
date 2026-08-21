@php
    $portalSidebarLayout = $portalSidebarLayout ?? false;
    $awarenessEngagement = $awarenessEngagement ?? [
        'supports_count' => 0,
        'pledges_count' => 0,
        'volunteers_count' => 0,
        'user_supported' => false,
        'user_pledge' => null,
    ];
    $awarenessPledgeCounts = collect($awarenessPledgeCounts ?? []);
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canEngage = auth()->check() && $post->isPubliclyVisible() && ! $isAuthor;
    $callToAction = data_get($post->meta, 'awareness_call_to_action');
    $actionItems = $post->awarenessCallToActionItems();
    $impactCategories = $post->awarenessSocialImpactCategories();
    $hasEngagementActions = $post->allowsAwarenessCauseSupport() || $post->allowsAwarenessPledges() || $post->allowsCampaignJoin();
@endphp

@if(filled($callToAction) || $actionItems !== [])
    <div class="awareness-section-panel awareness-cta-panel about-box mb-4">
        <div class="awareness-section-panel__header">
            <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
            <div>
                <h4 class="mb-0">Call to action</h4>
                <p class="text-muted small mb-0">The most important step — take action today.</p>
            </div>
        </div>
        @if(filled($callToAction))
            <p class="awareness-cta-panel__lead mb-3">{{ $callToAction }}</p>
        @endif
        @if($actionItems !== [])
            <div class="d-flex flex-wrap gap-2">
                @foreach($actionItems as $item)
                    <span class="awareness-audience-pill">{{ $item }}</span>
                @endforeach
            </div>
        @endif
    </div>
@endif

@if($impactCategories !== [])
    <div class="awareness-section-panel about-box mb-4">
        <div class="awareness-section-panel__header">
            <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
            <h4 class="mb-0">Social impact</h4>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach($impactCategories as $category)
                <span class="badge bg-success-subtle text-success border">{{ $category }}</span>
            @endforeach
        </div>
    </div>
@endif

@php
    $impactMetrics = collect([
        'Trees planted' => data_get($post->meta, 'awareness_impact_trees_planted'),
        'Volunteers joined' => data_get($post->meta, 'awareness_impact_volunteers_joined'),
        'People reached' => data_get($post->meta, 'awareness_impact_people_reached'),
    ])->filter(fn ($value) => filled($value));
@endphp
@if($impactMetrics->isNotEmpty())
    <div class="awareness-impact-strip mb-4">
        @foreach($impactMetrics as $label => $value)
            <div class="awareness-impact-strip__item">
                <span class="awareness-impact-strip__value">{{ number_format((int) $value) }}</span>
                <span class="awareness-impact-strip__label">{{ $label }}</span>
            </div>
        @endforeach
    </div>
@endif

@if($post->awarenessHasEventDetails() && ! ($portalSidebarLayout ?? false))
    @include('community.partials.awareness-event-details', ['post' => $post])
@endif

@if($hasEngagementActions)
    <section class="awareness-engagement-panel about-box mb-4" id="awarenessEngagementPanel">
        <div class="awareness-engagement-panel__header">
            <div>
                <span class="awareness-engagement-panel__kicker">SoilnWater Community Campaign</span>
                <h4 class="mb-1">Join the movement</h4>
                <p class="text-muted mb-0 small">Support this cause, take a pledge, or volunteer — every action counts.</p>
            </div>
            <div class="awareness-engagement-panel__stats">
                @if($post->allowsAwarenessCauseSupport())
                    <span class="awareness-engagement-panel__stat">
                        <strong data-awareness-stat="supports">{{ number_format($awarenessEngagement['supports_count']) }}</strong>
                        supporters
                    </span>
                @endif
                @if($post->allowsAwarenessPledges())
                    <span class="awareness-engagement-panel__stat">
                        <strong data-awareness-stat="pledges">{{ number_format($awarenessEngagement['pledges_count']) }}</strong>
                        pledges
                    </span>
                @endif
                @if($post->allowsCampaignJoin())
                    <span class="awareness-engagement-panel__stat">
                        <strong data-awareness-stat="volunteers">{{ number_format($awarenessEngagement['volunteers_count']) }}</strong>
                        volunteers
                    </span>
                @endif
            </div>
        </div>

        <div class="awareness-engagement-panel__grid">
            @if($post->allowsAwarenessCauseSupport())
                <div class="awareness-engagement-card">
                    <div class="awareness-engagement-card__icon awareness-engagement-card__icon--support">
                        <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                    </div>
                    <div class="awareness-engagement-card__body">
                        <h5 class="mb-1">I Support This Cause</h5>
                        <p class="text-muted small mb-3">Show your support and help this campaign grow.</p>
                        @auth
                            @if($canEngage)
                                <button
                                    type="button"
                                    class="btn btn-sm {{ $awarenessEngagement['user_supported'] ? 'btn-success' : 'btn-outline-success' }} js-awareness-support-toggle"
                                    data-url="{{ route('community.awareness-engagement.support', $post) }}"
                                    data-active="{{ $awarenessEngagement['user_supported'] ? '1' : '0' }}"
                                >
                                    <i class="fa-solid fa-hand-holding-heart me-1" aria-hidden="true"></i>
                                    <span class="js-awareness-support-label">{{ $awarenessEngagement['user_supported'] ? 'You support this cause' : 'Support this cause' }}</span>
                                </button>
                            @elseif($isAuthor)
                                <span class="badge bg-light text-dark border">Your campaign</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to support</a>
                        @endauth
                    </div>
                </div>
            @endif

            @if($post->allowsAwarenessPledges())
                <div class="awareness-engagement-card">
                    <div class="awareness-engagement-card__icon awareness-engagement-card__icon--pledge">
                        <i class="fa-solid fa-signature" aria-hidden="true"></i>
                    </div>
                    <div class="awareness-engagement-card__body">
                        <h5 class="mb-1">Take a pledge</h5>
                        <p class="text-muted small mb-3">Commit to a specific action for this cause.</p>
                        @auth
                            @if($canEngage)
                                <form method="POST" action="{{ route('community.awareness-engagement.pledge', $post) }}" class="js-awareness-pledge-form">
                                    @csrf
                                    @foreach($post->awarenessPledgeOptions() as $pledgeOption)
                                        <div class="form-check mb-2">
                                            <input
                                                type="radio"
                                                name="pledge_text"
                                                id="awarenessPledge{{ $loop->index }}"
                                                value="{{ $pledgeOption }}"
                                                class="form-check-input"
                                                @checked($awarenessEngagement['user_pledge'] === $pledgeOption)
                                            >
                                            <label class="form-check-label small" for="awarenessPledge{{ $loop->index }}">{{ $pledgeOption }}</label>
                                        </div>
                                    @endforeach
                                    <button type="submit" class="btn btn-sm btn-success mt-1">Save pledge</button>
                                </form>
                            @elseif($isAuthor)
                                <span class="badge bg-light text-dark border">Your campaign pledges</span>
                            @endif
                        @else
                            <p class="mb-0 small"><a href="{{ route('login') }}">Login</a> to take a pledge.</p>
                        @endauth
                        @if($awarenessPledgeCounts->isNotEmpty())
                            <div class="awareness-pledge-counts mt-3" data-awareness-pledge-counts>
                                @foreach($awarenessPledgeCounts as $pledgeRow)
                                    <div class="awareness-pledge-counts__row" data-pledge-text="{{ $pledgeRow['pledge_text'] }}">
                                        <span>{{ $pledgeRow['pledge_text'] }}</span>
                                        <strong>{{ number_format($pledgeRow['total']) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="awareness-pledge-counts mt-3 d-none" data-awareness-pledge-counts></div>
                        @endif
                    </div>
                </div>
            @endif

            @if($post->allowsCampaignJoin())
                <div class="awareness-engagement-card awareness-engagement-card--wide">
                    <div class="awareness-engagement-card__icon awareness-engagement-card__icon--volunteer">
                        <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                    </div>
                    <div class="awareness-engagement-card__body">
                        <h5 class="mb-1">Join this campaign</h5>
                        <p class="text-muted small mb-3">Volunteer your time and help make this awareness drive a success.</p>
                        @if($isAuthor)
                            <span class="badge bg-light text-dark border">Your campaign volunteers</span>
                        @else
                            <form method="POST" action="{{ route('community.awareness-engagement.volunteer', $post) }}" class="js-awareness-volunteer-form row g-2 g-md-3">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label small mb-1" for="awarenessVolunteerName">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="awarenessVolunteerName" class="form-control form-control-sm" required maxlength="160" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1" for="awarenessVolunteerMobile">Mobile <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" id="awarenessVolunteerMobile" class="form-control form-control-sm" required maxlength="40">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1" for="awarenessVolunteerEmail">Email</label>
                                    <input type="email" name="email" id="awarenessVolunteerEmail" class="form-control form-control-sm" maxlength="160" value="{{ auth()->user()?->email }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1" for="awarenessVolunteerCity">City</label>
                                    <input type="text" name="city" id="awarenessVolunteerCity" class="form-control form-control-sm" maxlength="120">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-sm btn-success">Join campaign</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="awareness-engagement-feedback alert d-none mt-3 mb-0" role="status" data-awareness-feedback></div>
    </section>
@endif

@if($post->user && $post->resolvedPublishAs() !== \App\Models\CommunityPost::PUBLISH_AS_ANONYMOUS)
    <div class="awareness-author-strip about-box mb-4">
        <div class="awareness-author-strip__avatar">
            <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
        </div>
        <div class="awareness-author-strip__body">
            <span class="awareness-author-strip__label">Campaign by</span>
            <strong>{{ $post->user->full_name ?: $post->user->name }}</strong>
            @if(filled(data_get($post->meta, 'awareness_organization_name')))
                <span class="text-muted">· {{ data_get($post->meta, 'awareness_organization_name') }}</span>
            @endif
        </div>
        @if($post->resolvedPublishAs() === \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE && filled($post->user->authorUniqueName()))
            <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="btn btn-sm btn-outline-primary">View profile</a>
        @endif
    </div>
@endif

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const feedbackEl = document.querySelector('[data-awareness-feedback]');

            function showAwarenessFeedback(message, type) {
                if (!feedbackEl) {
                    return;
                }
                feedbackEl.textContent = message;
                feedbackEl.classList.remove('d-none', 'alert-success', 'alert-danger');
                feedbackEl.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
            }

            function updateAwarenessStats(engagement) {
                if (!engagement) {
                    return;
                }
                document.querySelectorAll('[data-awareness-stat="supports"]').forEach(function (el) {
                    el.textContent = new Intl.NumberFormat().format(engagement.supports_count || 0);
                });
                document.querySelectorAll('[data-awareness-stat="pledges"]').forEach(function (el) {
                    el.textContent = new Intl.NumberFormat().format(engagement.pledges_count || 0);
                });
                document.querySelectorAll('[data-awareness-stat="volunteers"]').forEach(function (el) {
                    el.textContent = new Intl.NumberFormat().format(engagement.volunteers_count || 0);
                });
            }

            function renderPledgeCounts(counts) {
                const container = document.querySelector('[data-awareness-pledge-counts]');
                if (!container || !Array.isArray(counts)) {
                    return;
                }
                container.innerHTML = counts.map(function (row) {
                    return '<div class="awareness-pledge-counts__row" data-pledge-text="' + row.pledge_text + '">' +
                        '<span>' + row.pledge_text + '</span><strong>' + new Intl.NumberFormat().format(row.total || 0) + '</strong></div>';
                }).join('');
                container.classList.toggle('d-none', counts.length === 0);
            }

            document.querySelectorAll('.js-awareness-support-toggle').forEach(function (button) {
                button.addEventListener('click', async function () {
                    try {
                        const response = await fetch(button.dataset.url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });
                        const payload = await response.json();
                        if (!response.ok) {
                            throw new Error(payload.message || 'Unable to update support.');
                        }
                        const active = Boolean(payload.supported);
                        button.dataset.active = active ? '1' : '0';
                        button.classList.toggle('btn-success', active);
                        button.classList.toggle('btn-outline-success', !active);
                        const label = button.querySelector('.js-awareness-support-label');
                        if (label) {
                            label.textContent = active ? 'You support this cause' : 'Support this cause';
                        }
                        updateAwarenessStats(payload.engagement);
                        showAwarenessFeedback(payload.message, 'success');
                    } catch (error) {
                        showAwarenessFeedback(error.message || 'Unable to update support.', 'error');
                    }
                });
            });

            document.querySelectorAll('.js-awareness-pledge-form').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    const selected = form.querySelector('input[name="pledge_text"]:checked');
                    if (!selected) {
                        showAwarenessFeedback('Please choose a pledge.', 'error');
                        return;
                    }
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton?.textContent || 'Save pledge';
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Saving...';
                    }
                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });
                        const payload = await response.json();
                        if (!response.ok) {
                            throw new Error(payload.message || 'Unable to save pledge.');
                        }
                        updateAwarenessStats(payload.engagement);
                        renderPledgeCounts(payload.pledge_counts || []);
                        showAwarenessFeedback(payload.message, 'success');
                    } catch (error) {
                        showAwarenessFeedback(error.message || 'Unable to save pledge.', 'error');
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalText;
                        }
                    }
                });
            });

            document.querySelectorAll('.js-awareness-volunteer-form').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton?.textContent || 'Join campaign';
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Submitting...';
                    }
                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });
                        const payload = await response.json();
                        if (!response.ok) {
                            throw new Error(payload.message || 'Unable to join campaign.');
                        }
                        updateAwarenessStats(payload.engagement);
                        form.reset();
                        showAwarenessFeedback(payload.message, 'success');
                    } catch (error) {
                        showAwarenessFeedback(error.message || 'Unable to join campaign.', 'error');
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalText;
                        }
                    }
                });
            });
        });
    </script>
    @endpush
@endonce
