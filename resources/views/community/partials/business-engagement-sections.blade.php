@php
    $businessEngagement = $businessEngagement ?? ['queries_count' => 0, 'user_submitted' => false];
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canContact = $post->allowsBusinessContact() && ! $isAuthor;
    $contactOptions = $post->businessContactOptionsForDisplay();
    $askCommunity = data_get($post->meta, 'business_ask_community');
    $usefulLinks = trim((string) data_get($post->meta, 'business_useful_links', ''));
    $governmentSchemes = trim((string) data_get($post->meta, 'business_government_schemes', ''));
    $trainingPrograms = trim((string) data_get($post->meta, 'business_training_programs', ''));
    $industryResources = trim((string) data_get($post->meta, 'business_industry_resources', ''));
    $hasResources = filled($usefulLinks) || filled($governmentSchemes) || filled($trainingPrograms) || filled($industryResources);
@endphp

@if(filled($askCommunity))
    <div class="business-section-panel business-ask-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-comments" aria-hidden="true"></i>
            <div>
                <h4 class="mb-0">Ask the community</h4>
                <p class="text-muted small mb-0">Share your experience or answer the author's question below.</p>
            </div>
        </div>
        <p class="business-ask-panel__lead mb-3">{{ $askCommunity }}</p>
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

@if($hasResources)
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <h4 class="mb-0">Business resources</h4>
        </div>
        <div class="row g-3">
            @if(filled($usefulLinks))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Useful links</h5>
                        <div class="business-resource-text">{!! nl2br(e($usefulLinks)) !!}</div>
                    </div>
                </div>
            @endif
            @if(filled($governmentSchemes))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Government schemes</h5>
                        <div class="business-resource-text">{!! nl2br(e($governmentSchemes)) !!}</div>
                    </div>
                </div>
            @endif
            @if(filled($trainingPrograms))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Training programs</h5>
                        <div class="business-resource-text">{!! nl2br(e($trainingPrograms)) !!}</div>
                    </div>
                </div>
            @endif
            @if(filled($industryResources))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Industry resources</h5>
                        <div class="business-resource-text">{!! nl2br(e($industryResources)) !!}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

@if($canContact)
    <section class="business-contact-panel about-box mb-4" id="businessContactPanel">
        <div class="business-contact-panel__header">
            <div>
                <span class="business-contact-panel__kicker">Networking</span>
                <h4 class="mb-1">Contact & guidance</h4>
                <p class="text-muted mb-0 small">Reach out to the author for business queries or guidance.</p>
            </div>
            <div class="business-contact-panel__stat">
                <strong data-business-stat="queries">{{ number_format($businessEngagement['queries_count'] ?? 0) }}</strong>
                <span>inquiries</span>
            </div>
        </div>

        <form method="POST" action="{{ route('community.business-engagement.query', $post) }}" class="js-business-query-form row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label small mb-1" for="businessQueryType">Inquiry type <span class="text-danger">*</span></label>
                <select name="query_type" id="businessQueryType" class="form-select form-select-sm" required>
                    <option value="">Select type</option>
                    @foreach($contactOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-1" for="businessQueryName">Your name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="businessQueryName" class="form-control form-control-sm" required maxlength="160" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-1" for="businessQueryEmail">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="businessQueryEmail" class="form-control form-control-sm" required maxlength="160" value="{{ auth()->user()?->email }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-1" for="businessQueryMobile">Mobile</label>
                <input type="text" name="mobile" id="businessQueryMobile" class="form-control form-control-sm" maxlength="40">
            </div>
            <div class="col-12">
                <label class="form-label small mb-1" for="businessQueryMessage">Message <span class="text-danger">*</span></label>
                <textarea name="message" id="businessQueryMessage" class="form-control form-control-sm" rows="4" required maxlength="5000" placeholder="Describe your question or how you'd like to connect..."></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-sm btn-primary">Send inquiry</button>
            </div>
        </form>

        <div class="business-contact-feedback alert d-none mt-3 mb-0" role="status" data-business-feedback></div>
    </section>
@elseif($post->allowsBusinessContact() && $isAuthor)
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
            <h4 class="mb-0">Business inquiries</h4>
        </div>
        <p class="text-muted mb-0 small">Readers can contact you through the options you enabled. Check the activity panel in your manage view for incoming messages.</p>
    </div>
@endif

@if($post->user && $post->resolvedPublishAs() !== \App\Models\CommunityPost::PUBLISH_AS_ANONYMOUS)
    <div class="business-author-strip about-box mb-4">
        <div class="business-author-strip__avatar">
            <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
        </div>
        <div class="business-author-strip__body">
            <span class="business-author-strip__label">Business post by</span>
            <strong>{{ $post->user->full_name ?: $post->user->name }}</strong>
            @if(filled(data_get($post->meta, 'business_author_designation')))
                <span class="text-muted">· {{ data_get($post->meta, 'business_author_designation') }}</span>
            @endif
            @if(filled(data_get($post->meta, 'business_name')))
                <span class="text-muted">· {{ data_get($post->meta, 'business_name') }}</span>
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
            const feedbackEl = document.querySelector('[data-business-feedback]');

            function showBusinessFeedback(message, type) {
                if (!feedbackEl) {
                    return;
                }
                feedbackEl.textContent = message;
                feedbackEl.classList.remove('d-none', 'alert-success', 'alert-danger');
                feedbackEl.classList.add(type === 'error' ? 'alert-danger' : 'alert-success');
            }

            document.querySelectorAll('.js-business-query-form').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton?.textContent || 'Send inquiry';
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Sending...';
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
                            throw new Error(payload.message || 'Unable to send inquiry.');
                        }
                        const statEl = document.querySelector('[data-business-stat="queries"]');
                        if (statEl && payload.engagement) {
                            statEl.textContent = new Intl.NumberFormat().format(payload.engagement.queries_count || 0);
                        }
                        form.reset();
                        showBusinessFeedback(payload.message, 'success');
                    } catch (error) {
                        showBusinessFeedback(error.message || 'Unable to send inquiry.', 'error');
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
