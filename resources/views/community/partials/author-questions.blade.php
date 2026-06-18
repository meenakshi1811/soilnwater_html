@php
    $authorDisplay = $author->name ?? $author->full_name ?? 'the author';
    $askAction = isset($post)
        ? route('community.author-questions.store.post', $post)
        : route('community.author-questions.store.author', $author);
    $contextLabel = isset($post) ? 'about this post' : 'on the author page';
@endphp

@once
    @push('styles')
    <style>
        .community-author-questions-box {
            border: 1px solid #d7e8f8;
            border-radius: 0.95rem;
            background: linear-gradient(180deg, #f8fbff 0%, #f2f8ff 100%);
            overflow: hidden;
        }

        .community-author-questions-head {
            border-bottom: 1px solid #d7e8f8;
            padding: 1rem 1.1rem;
        }

        .community-author-questions-title {
            color: #173d67;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .community-author-questions-body {
            padding: 1rem 1.1rem 1.1rem;
        }

        .community-author-questions-form-card {
            background: #fff;
            border: 1px solid #dbe7f5;
            border-radius: 0.85rem;
            padding: 1rem;
        }

        .community-qa-item {
            background: #fff;
            border: 1px solid #dbe7f5;
            border-radius: 0.85rem;
            padding: 1rem;
        }

        .community-qa-item + .community-qa-item {
            margin-top: 0.75rem;
        }

        .community-qa-question {
            color: #173d67;
            font-weight: 600;
            margin-bottom: 0.65rem;
        }

        .community-qa-answer {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 0.75rem;
            color: #14532d;
            padding: 0.85rem 0.95rem;
        }

        .community-qa-meta {
            color: #6c849c;
            font-size: 0.82rem;
        }

        .community-author-questions-success {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            border-radius: 0.75rem;
            color: #14532d;
            padding: 0.85rem 0.95rem;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-community-ask-author-form').forEach(function (form) {
                const successBox = form.closest('.community-author-questions-form-card')
                    ?.parentElement
                    ?.querySelector('.js-community-ask-author-success');

                form.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const button = form.querySelector('button[type="submit"]');
                    const originalText = button.textContent;
                    button.disabled = true;
                    button.textContent = 'Sending...';

                    if (successBox) {
                        successBox.classList.add('d-none');
                        successBox.textContent = '';
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || '',
                            },
                            body: new FormData(form),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            const firstError = payload.errors ? Object.values(payload.errors).flat()[0] : null;
                            throw new Error(firstError || payload.message || 'Unable to send your question.');
                        }

                        form.reset();

                        if (successBox) {
                            successBox.textContent = payload.message || 'Your question has been sent to the author.';
                            successBox.classList.remove('d-none');
                        } else {
                            alert(payload.message || 'Your question has been sent to the author.');
                        }
                    } catch (error) {
                        alert(error.message || 'Unable to send your question.');
                    } finally {
                        button.disabled = false;
                        button.textContent = originalText;
                    }
                });
            });
        });
    </script>
    @endpush
@endonce

<div class="community-author-questions-box {{ ($compactSection ?? false) ? 'community-author-questions-box--embedded mt-0 mb-4' : 'mt-4' }}" id="author-questions">
    <div class="community-author-questions-head">
        <h4 class="community-author-questions-title mb-0">{{ $sectionTitle ?? 'Ask Question to Author' }}</h4>
        <p class="text-muted small mb-0">
            @if(isset($post) && $post->content_type === 'news')
                Ask {{ $authorDisplay }} a direct question about this news story. You will be notified when the author answers in the portal.
            @else
                Send a direct question to {{ $authorDisplay }} {{ $contextLabel }}. Only logged-in readers can ask questions.
            @endif
        </p>
    </div>

    <div class="community-author-questions-body">
        @if(session('success'))
            <div class="community-author-questions-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="community-author-questions-success mb-3 d-none js-community-ask-author-success" role="status" aria-live="polite"></div>

        @auth
            @if(auth()->id() !== $author->id)
                <div class="community-author-questions-form-card mb-3">
                    <form method="POST" action="{{ $askAction }}" class="js-community-ask-author-form">
                        @csrf
                        <label class="form-label fw-semibold" for="communityAuthorQuestionInput">Your question</label>
                        <textarea
                            name="question"
                            id="communityAuthorQuestionInput"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            minlength="10"
                            required
                            placeholder="Ask {{ $authorDisplay }} about their experience, recommendations, or perspective..."
                        ></textarea>
                        <small class="text-muted d-block mt-2">Minimum 10 characters. The author will answer from their portal and the response will appear here.</small>
                        <button type="submit" class="btn btn-success mt-3">
                            <i class="fa-solid fa-paper-plane me-1" aria-hidden="true"></i>
                            Send question
                        </button>
                    </form>
                </div>
            @else
                <p class="text-muted mb-0">You are viewing your own {{ isset($post) ? 'post' : 'author page' }}. Readers can ask you questions here.</p>
            @endif
        @else
            <p class="mb-0"><a href="{{ route('login') }}">Login</a> to ask {{ $authorDisplay }} a question.</p>
        @endauth

        @if(($answeredQuestions ?? collect())->isNotEmpty())
            <div class="mt-3">
                <h5 class="h6 text-uppercase text-muted fw-semibold mb-3">Answered questions</h5>
                @foreach($answeredQuestions as $qa)
                    <div class="community-qa-item">
                        <div class="community-qa-meta mb-2">
                            Asked by {{ $qa->askerDisplayName() }}
                            · {{ $qa->answered_at?->format('M d, Y') }}
                        </div>
                        <div class="community-qa-question">Q: {{ $qa->question }}</div>
                        <div class="community-qa-answer">A: {{ $qa->answer }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
