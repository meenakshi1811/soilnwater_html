@php
    $astroConsultancyEngagement = $astroConsultancyEngagement ?? [
        'queries_count' => 0,
        'user_submitted' => false,
    ];
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canEngage = $post->isPubliclyVisible() && ! $isAuthor;
    $askCommunity = data_get($post->meta, 'astro_consultancy_ask_community');
    $queryOptions = $post->astroPrivateQueryOptionsForDisplay();
    $hasPrivateQuery = $post->astroHasPrivateQueryActions();
    $showAskPanel = filled($askCommunity);
    $showPanel = $post->isAstroConsultancyPost()
        && $post->isPubliclyVisible()
        && ($showAskPanel || $hasPrivateQuery || $post->astroEnablesLiveQa());
@endphp

@if($showPanel)
    @if($showAskPanel)
        <div class="business-section-panel astro-community-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-circle-question text-primary" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Ask the community</h4>
                    <p class="text-muted small mb-0">Share perspectives respectfully — this is educational discussion, not guaranteed prediction.</p>
                </div>
            </div>
            <p class="fs-5 mb-3">"{{ $askCommunity }}"</p>
            @if($post->allow_questions || $post->allow_comments)
                <div class="d-flex flex-wrap gap-2">
                    @if($post->allow_questions)
                        <a href="#communityAuthorQuestions" class="btn btn-sm btn-outline-primary">Ask a question</a>
                    @endif
                    @if($post->allow_comments)
                        <a href="#participation-comments" class="btn btn-sm btn-outline-secondary">Join the discussion</a>
                    @endif
                </div>
            @endif
        </div>
    @endif

    @if($hasPrivateQuery && $canEngage)
        <section class="astro-engagement-panel about-box mb-4" id="astroConsultancyEngagementPanel">
            <div class="astro-engagement-panel__header">
                <div>
                    <span class="astro-engagement-panel__kicker">Astro Consultancy · Private engagement</span>
                    <h4 class="mb-1">Request consultation privately</h4>
                    <p class="text-muted mb-0 small">Send a private message to the author instead of posting personal birth details publicly.</p>
                </div>
                <div class="astro-engagement-panel__stats">
                    <span class="astro-engagement-panel__stat">
                        <strong data-astro-stat="queries">{{ number_format($astroConsultancyEngagement['queries_count'] ?? 0) }}</strong>
                        requests
                    </span>
                </div>
            </div>

            @if($post->astroEnablesLiveQa())
                <div class="astro-engagement-card mb-3">
                    <div class="astro-engagement-card__icon astro-engagement-card__icon--live">
                        <i class="fa-solid fa-microphone-lines" aria-hidden="true"></i>
                    </div>
                    <div class="astro-engagement-card__body">
                        <h5 class="mb-1">Live Q&amp;A available</h5>
                        <p class="text-muted small mb-0">This post is open for live session discovery. Use the form below for private consultation requests.</p>
                    </div>
                </div>
            @endif

            @if(! ($astroConsultancyEngagement['user_submitted'] ?? false))
                <form method="POST" action="{{ route('community.astro-consultancy-engagement.private-query', $post) }}" class="js-astro-private-query-form row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="astroQueryType">Request type <span class="text-danger">*</span></label>
                        <select name="query_type" id="astroQueryType" class="form-select form-select-sm" required>
                            <option value="">Select type</option>
                            @foreach($queryOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="astroQueryName">Your name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="astroQueryName" class="form-control form-control-sm" required maxlength="160" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="astroQueryEmail">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="astroQueryEmail" class="form-control form-control-sm" required maxlength="160" value="{{ auth()->user()?->email }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1" for="astroQueryMobile">Mobile</label>
                        <input type="text" name="mobile" id="astroQueryMobile" class="form-control form-control-sm" maxlength="40">
                    </div>
                    <div class="col-12">
                        <label class="form-label small mb-1" for="astroQueryMessage">Your message <span class="text-danger">*</span></label>
                        <textarea name="message" id="astroQueryMessage" class="form-control form-control-sm" rows="4" required maxlength="5000" placeholder="Briefly describe your consultation need. Avoid sharing sensitive personal details you are not comfortable sending."></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i>Send private request
                        </button>
                    </div>
                </form>
            @else
                <div class="alert alert-success mb-0 small">
                    <i class="fa-solid fa-circle-check me-1"></i>You have already sent a consultation request for this post. The author may respond through SoilnWater.
                </div>
            @endif
        </section>
    @elseif($hasPrivateQuery && $isAuthor)
        <div class="astro-engagement-panel about-box mb-4">
            <span class="astro-engagement-panel__kicker">Author portal</span>
            <h4 class="mb-1">Private consultation requests</h4>
            <p class="text-muted small mb-0">Private queries from readers appear in your post management portal.</p>
        </div>
    @elseif($hasPrivateQuery)
        <div class="astro-engagement-panel about-box mb-4">
            <p class="mb-2">Private consultation requests are available on this post.</p>
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">Login to send a private request</a>
        </div>
    @endif
@endif

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-astro-private-query-form').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton.textContent;
                    submitButton.disabled = true;
                    submitButton.textContent = 'Sending...';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: new FormData(form),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw new Error(payload.message || 'Unable to send your request.');
                        }

                        const stat = document.querySelector('[data-astro-stat="queries"]');
                        if (stat && payload.engagement) {
                            stat.textContent = new Intl.NumberFormat().format(payload.engagement.queries_count || 0);
                        }

                        window.alert(payload.message || 'Request sent successfully.');
                        window.location.reload();
                    } catch (error) {
                        window.alert(error.message || 'Unable to send your request.');
                    } finally {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                });
            });
        });
    </script>
    @endpush
@endonce
