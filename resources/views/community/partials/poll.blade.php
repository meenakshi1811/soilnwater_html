@php
    $pollCounts = $post->pollCounts();
    $userPollVote = $post->userPollVote(auth()->user());
    $pollTotal = max($pollCounts['total'], 1);
@endphp

@once
    @push('styles')
    <style>
        .community-poll-box {
            border: 1px solid #d7e8f8;
            border-radius: 0.9rem;
            background: linear-gradient(180deg, #f8fbff 0%, #f2f8ff 100%);
            padding: 1rem 1.1rem;
        }

        .community-poll-question {
            color: #173d67;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.85rem;
        }

        .community-poll-option {
            align-items: center;
            background: #fff;
            border: 1px solid #d7e3ef;
            border-radius: 0.75rem;
            display: flex;
            gap: 0.65rem;
            margin-bottom: 0.55rem;
            padding: 0.65rem 0.8rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .community-poll-option.is-selected {
            border-color: #2e7d32;
            box-shadow: 0 0 0 1px rgba(46, 125, 50, 0.15);
        }

        .community-poll-option label {
            cursor: pointer;
            flex: 1;
            font-weight: 600;
            margin-bottom: 0;
        }

        .community-poll-results {
            margin-top: 0.85rem;
        }

        .community-poll-result-row + .community-poll-result-row {
            margin-top: 0.55rem;
        }

        .community-poll-result-label {
            display: flex;
            font-size: 0.86rem;
            font-weight: 600;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .community-poll-result-bar {
            background: #e8eef5;
            border-radius: 999px;
            height: 0.45rem;
            overflow: hidden;
        }

        .community-poll-result-fill {
            background: linear-gradient(90deg, #1f66b4, #2e7d32);
            border-radius: 999px;
            height: 100%;
            transition: width 0.25s ease;
        }

        .community-poll-total {
            color: #6c849c;
            font-size: 0.82rem;
            margin-top: 0.65rem;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pollForm = document.getElementById('communityPollForm');

            if (!pollForm) {
                return;
            }

            pollForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const submitButton = pollForm.querySelector('button[type="submit"]');
                const selected = pollForm.querySelector('input[name="option"]:checked');

                if (!selected) {
                    window.alert('Please choose an option.');
                    return;
                }

                const originalText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';

                try {
                    const formData = new FormData(pollForm);
                    const response = await fetch(pollForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Unable to save your vote.');
                    }

                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || 'Unable to save your vote.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });
        });
    </script>
    @endpush
@endonce

<div class="community-poll-box mt-4" id="communityPoll">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
        <h4 class="mb-0">{{ $post->isAwarenessPost() ? 'Awareness poll' : ($post->isBusinessPost() ? 'Business poll' : ($post->isAstroConsultancyPost() ? 'Astro consultancy poll' : 'Community poll')) }}</h4>
        <span class="badge bg-primary">Open</span>
    </div>
    <p class="community-poll-question mb-0">{{ $post->pollQuestion() }}</p>

    @auth
        <form method="POST" action="{{ route('community.poll.vote', $post) }}" class="mt-3" id="communityPollForm">
            @csrf
            @foreach($post->pollOptionsForDisplay() as $value => $label)
                <div class="community-poll-option {{ $userPollVote === $value ? 'is-selected' : '' }}">
                    <input
                        type="radio"
                        name="option"
                        id="communityPollOption{{ \Illuminate\Support\Str::studly($value) }}"
                        value="{{ $value }}"
                        @checked($userPollVote === $value)
                    >
                    <label for="communityPollOption{{ \Illuminate\Support\Str::studly($value) }}">{{ $label }}</label>
                </div>
            @endforeach
            <button type="submit" class="btn btn-success btn-sm mt-2">
                {{ $userPollVote ? 'Update vote' : 'Submit vote' }}
            </button>
        </form>
    @else
        <div class="mt-3">
            @foreach($post->pollOptionsForDisplay() as $value => $label)
                <div class="community-poll-option">
                    <span class="text-secondary" aria-hidden="true">○</span>
                    <span class="fw-semibold">{{ $label }}</span>
                </div>
            @endforeach
            <p class="mb-0 mt-2"><a href="{{ route('login') }}">Login</a> to vote in this poll.</p>
        </div>
    @endauth

    @if($pollCounts['total'] > 0)
        <div class="community-poll-results">
            @foreach($post->pollOptionsForDisplay() as $value => $label)
                @php
                    $count = $pollCounts[$value] ?? 0;
                    $percent = round(($count / $pollTotal) * 100);
                @endphp
                <div class="community-poll-result-row">
                    <div class="community-poll-result-label">
                        <span>{{ $label }}</span>
                        <span>{{ $count }} ({{ $percent }}%)</span>
                    </div>
                    <div class="community-poll-result-bar" aria-hidden="true">
                        <div class="community-poll-result-fill" style="width: {{ $percent }}%;"></div>
                    </div>
                </div>
            @endforeach
            <div class="community-poll-total">{{ $pollCounts['total'] }} {{ \Illuminate\Support\Str::plural('vote', $pollCounts['total']) }} so far</div>
        </div>
    @endif
</div>
