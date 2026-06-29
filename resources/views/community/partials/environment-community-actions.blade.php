@php
    $environmentEngagement = $environmentEngagement ?? [
        'supports_count' => 0,
        'follows_count' => 0,
        'volunteers_count' => 0,
        'user_supported' => false,
        'user_following' => false,
        'user_volunteered' => false,
    ];
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canEngage = auth()->check() && $post->isPubliclyVisible() && ! $isAuthor;
    $askCommunity = data_get($post->meta, 'environment_ask_community');
    $participationRequests = $post->environmentParticipationRequests();
    $hasEngagementActions = $post->environmentHasParticipationActions();
    $showAskPanel = filled($askCommunity);
    $showPanel = $post->isEnvironmentPost()
        && $post->isPubliclyVisible()
        && ($showAskPanel || $hasEngagementActions);
@endphp

@if($showPanel)
    @if($hasEngagementActions)
        <section class="env-engagement-panel about-box mb-4" id="environmentEngagementPanel">
            <div class="env-engagement-panel__header">
                <div>
                    <span class="env-engagement-panel__kicker">Environment · Community action</span>
                    <h4 class="mb-1">Join this conservation effort</h4>
                    <p class="text-muted mb-0 small">Support the initiative, follow updates, or register as a volunteer.</p>
                </div>
                <div class="env-engagement-panel__stats">
                    @if($post->allowsEnvironmentSupportInitiative())
                        <span class="env-engagement-panel__stat">
                            <strong data-environment-stat="supports">{{ number_format($environmentEngagement['supports_count']) }}</strong>
                            supporters
                        </span>
                    @endif
                    @if($post->allowsEnvironmentFollowCampaign())
                        <span class="env-engagement-panel__stat">
                            <strong data-environment-stat="follows">{{ number_format($environmentEngagement['follows_count']) }}</strong>
                            following
                        </span>
                    @endif
                    @if($post->allowsEnvironmentVolunteerRegistration() || $post->allowsEnvironmentJoinCampaign())
                        <span class="env-engagement-panel__stat">
                            <strong data-environment-stat="volunteers">{{ number_format($environmentEngagement['volunteers_count']) }}</strong>
                            volunteers
                        </span>
                    @endif
                </div>
            </div>

            <div class="env-engagement-panel__grid">
                @if($post->allowsEnvironmentSupportInitiative())
                    <div class="env-engagement-card">
                        <div class="env-engagement-card__icon env-engagement-card__icon--support">
                            <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                        </div>
                        <div class="env-engagement-card__body">
                            <h5 class="mb-1">Support initiative</h5>
                            <p class="text-muted small mb-3">Show your support for this environmental effort.</p>
                            @auth
                                @if($canEngage)
                                    <button
                                        type="button"
                                        class="btn btn-sm {{ $environmentEngagement['user_supported'] ? 'btn-success' : 'btn-outline-success' }} js-environment-support-toggle"
                                        data-url="{{ route('community.environment-engagement.support', $post) }}"
                                        data-active="{{ $environmentEngagement['user_supported'] ? '1' : '0' }}"
                                    >
                                        <span class="js-environment-support-label">{{ $environmentEngagement['user_supported'] ? 'Supported' : 'Support initiative' }}</span>
                                    </button>
                                @elseif($isAuthor)
                                    <span class="badge bg-light text-dark border">Your post</span>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to support</a>
                            @endauth
                        </div>
                    </div>
                @endif

                @if($post->allowsEnvironmentFollowCampaign())
                    <div class="env-engagement-card">
                        <div class="env-engagement-card__icon env-engagement-card__icon--follow">
                            <i class="fa-solid fa-bell" aria-hidden="true"></i>
                        </div>
                        <div class="env-engagement-card__body">
                            <h5 class="mb-1">Follow campaign</h5>
                            <p class="text-muted small mb-3">Get notified when this campaign is updated.</p>
                            @auth
                                @if($canEngage)
                                    <button
                                        type="button"
                                        class="btn btn-sm {{ $environmentEngagement['user_following'] ? 'btn-success' : 'btn-outline-success' }} js-environment-follow-toggle"
                                        data-url="{{ route('community.environment-engagement.follow', $post) }}"
                                        data-active="{{ $environmentEngagement['user_following'] ? '1' : '0' }}"
                                    >
                                        <span class="js-environment-follow-label">{{ $environmentEngagement['user_following'] ? 'Following' : 'Follow campaign' }}</span>
                                    </button>
                                @elseif($isAuthor)
                                    <span class="badge bg-light text-dark border">Your post</span>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to follow</a>
                            @endauth
                        </div>
                    </div>
                @endif

                @if(($post->allowsEnvironmentVolunteerRegistration() || $post->allowsEnvironmentJoinCampaign()) && ! $environmentEngagement['user_volunteered'])
                    <div class="env-engagement-card env-engagement-card--wide">
                        <div class="env-engagement-card__icon env-engagement-card__icon--volunteer">
                            <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                        </div>
                        <div class="env-engagement-card__body flex-grow-1">
                            <h5 class="mb-1">Volunteer / join campaign</h5>
                            <p class="text-muted small mb-3">Register your interest to participate in this environmental activity.</p>
                            @if($isAuthor)
                                <span class="badge bg-light text-dark border">Volunteer registrations appear in your portal</span>
                            @else
                                <form method="POST" action="{{ route('community.environment-engagement.volunteer', $post) }}" class="js-environment-volunteer-form row g-2 g-md-3">
                                    @csrf
                                    <div class="col-md-6">
                                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Your name" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="mobile" class="form-control form-control-sm" placeholder="Mobile number" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="email" name="email" class="form-control form-control-sm" placeholder="Email (optional)" value="{{ auth()->user()?->email }}">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="city" class="form-control form-control-sm" placeholder="City (optional)">
                                    </div>
                                    @if($participationRequests !== [])
                                        <div class="col-md-4">
                                            <select name="interest" class="form-select form-select-sm">
                                                <option value="">How would you like to help?</option>
                                                @foreach($participationRequests as $requestOption)
                                                    <option value="{{ $requestOption }}">{{ $requestOption }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-sm btn-success">Register as volunteer</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @elseif($environmentEngagement['user_volunteered'])
                    <div class="env-engagement-card">
                        <div class="env-engagement-card__icon env-engagement-card__icon--volunteer">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        </div>
                        <div class="env-engagement-card__body">
                            <h5 class="mb-1">You're registered</h5>
                            <p class="text-muted small mb-0">Thank you for volunteering for this campaign.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="env-engagement-feedback alert d-none mt-3 mb-0" role="status" data-environment-feedback></div>
        </section>
    @endif

    @if($showAskPanel)
        <section class="report-community-panel about-box mt-4 env-community-panel" id="environmentCommunityActions">
            <div class="report-community-panel__header">
                <div>
                    <span class="report-community-panel__kicker">Environment · Community support</span>
                    <h4 class="mb-1">Help answer this conservation question</h4>
                    <p class="text-muted mb-0">Share practical advice, local experience, or resources with the author.</p>
                </div>
            </div>
            <div class="report-community-action-card">
                <div class="report-community-action-card__icon report-community-action-card__icon--support">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                </div>
                <div class="report-community-action-card__body">
                    <h5 class="mb-1">Community question</h5>
                    <p class="text-muted small mb-3">"{{ \Illuminate\Support\Str::limit($askCommunity, 160) }}"</p>
                    @auth
                        @if(! $isAuthor && $post->allow_comments)
                            <a href="#communityComments" class="btn btn-sm btn-outline-success">Reply in comments</a>
                        @elseif($isAuthor)
                            <span class="badge bg-light text-dark border">Awaiting community answers</span>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-success">Login to respond</a>
                    @endauth
                </div>
            </div>
        </section>
    @endif
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const feedback = document.querySelector('[data-environment-feedback]');

                function showFeedback(message, type) {
                    if (!feedback) return;
                    feedback.textContent = message;
                    feedback.className = 'env-engagement-feedback alert alert-' + (type || 'success') + ' mt-3 mb-0';
                    feedback.classList.remove('d-none');
                }

                function updateStats(engagement) {
                    if (!engagement) return;
                    const map = {
                        supports: engagement.supports_count,
                        follows: engagement.follows_count,
                        volunteers: engagement.volunteers_count,
                    };
                    Object.keys(map).forEach(function (key) {
                        const el = document.querySelector('[data-environment-stat="' + key + '"]');
                        if (el) el.textContent = new Intl.NumberFormat().format(map[key] || 0);
                    });
                }

                document.querySelectorAll('.js-environment-support-toggle').forEach(function (button) {
                    button.addEventListener('click', function () {
                        fetch(button.dataset.url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        })
                            .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
                            .then(function (result) {
                                if (!result.ok) throw new Error(result.data.message || 'Unable to update support.');
                                const active = !!result.data.supported;
                                button.dataset.active = active ? '1' : '0';
                                button.classList.toggle('btn-success', active);
                                button.classList.toggle('btn-outline-success', !active);
                                const label = button.querySelector('.js-environment-support-label');
                                if (label) label.textContent = active ? 'Supported' : 'Support initiative';
                                updateStats(result.data.engagement);
                                showFeedback(result.data.message, 'success');
                            })
                            .catch(function (error) { showFeedback(error.message, 'danger'); });
                    });
                });

                document.querySelectorAll('.js-environment-follow-toggle').forEach(function (button) {
                    button.addEventListener('click', function () {
                        fetch(button.dataset.url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        })
                            .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
                            .then(function (result) {
                                if (!result.ok) throw new Error(result.data.message || 'Unable to update follow status.');
                                const active = !!result.data.following;
                                button.dataset.active = active ? '1' : '0';
                                button.classList.toggle('btn-success', active);
                                button.classList.toggle('btn-outline-success', !active);
                                const label = button.querySelector('.js-environment-follow-label');
                                if (label) label.textContent = active ? 'Following' : 'Follow campaign';
                                updateStats(result.data.engagement);
                                showFeedback(result.data.message, 'success');
                            })
                            .catch(function (error) { showFeedback(error.message, 'danger'); });
                    });
                });

                document.querySelectorAll('.js-environment-volunteer-form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        const formData = new FormData(form);
                        fetch(form.action, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                            body: formData,
                        })
                            .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
                            .then(function (result) {
                                if (!result.ok) throw new Error(result.data.message || 'Unable to register.');
                                updateStats(result.data.engagement);
                                showFeedback(result.data.message, 'success');
                                form.reset();
                                window.location.reload();
                            })
                            .catch(function (error) { showFeedback(error.message, 'danger'); });
                    });
                });
            });
        </script>
    @endpush
@endonce
