@extends('backend.layouts.app')

@section('title', 'Author Questions')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community publishing</p>
            <h2 class="admin-title mb-1">Reader Questions</h2>
            <p class="mb-0 text-secondary">Review questions from readers and publish your answers on the community site.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="chart-card p-3 mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('community.author-questions.index') }}" class="btn btn-sm {{ $activeStatus === 'all' ? 'btn-success' : 'btn-outline-success' }}">
                All <span class="badge bg-light text-dark ms-1">{{ $pendingCount + $answeredCount }}</span>
            </a>
            <a href="{{ route('community.author-questions.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $activeStatus === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                Pending <span class="badge bg-light text-dark ms-1">{{ $pendingCount }}</span>
            </a>
            <a href="{{ route('community.author-questions.index', ['status' => 'answered']) }}" class="btn btn-sm {{ $activeStatus === 'answered' ? 'btn-primary' : 'btn-outline-primary' }}">
                Answered <span class="badge bg-light text-dark ms-1">{{ $answeredCount }}</span>
            </a>
        </div>
    </div>

    @forelse($questions as $question)
        <div class="chart-card p-3 p-lg-4 mb-3">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                        <span class="badge {{ $question->isAnswered() ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $question->isAnswered() ? 'Answered' : 'Pending' }}
                        </span>
                        @if($question->post)
                            <span class="badge bg-light text-dark border">Post: {{ $question->post->title }}</span>
                        @else
                            <span class="badge bg-light text-dark border">Author page</span>
                        @endif
                    </div>
                    <div class="text-muted small">
                        Asked by <strong>{{ $question->askerDisplayName() }}</strong>
                        · {{ $question->created_at->format('d M Y, h:i A') }}
                    </div>
                </div>
                @if($question->post)
                    <a href="{{ route('community.show', $question->post) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        View post
                    </a>
                @endif
            </div>

            <div class="border rounded-3 p-3 bg-light mb-3">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Question</div>
                <p class="mb-0">{{ $question->question }}</p>
            </div>

            @if($question->isAnswered())
                <div class="border rounded-3 p-3" style="background:linear-gradient(180deg,#f0fdf4 0%,#ecfdf5 100%);">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-1">
                        <div class="small text-success text-uppercase fw-semibold">Your answer</div>
                        <small class="text-muted">{{ $question->answered_at?->format('d M Y, h:i A') }}</small>
                    </div>
                    <p class="mb-0">{{ $question->answer }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('community.author-questions.answer', $question) }}" class="js-author-question-answer-form">
                    @csrf
                    <label class="form-label fw-semibold" for="answer-{{ $question->id }}">Write your answer</label>
                    <textarea
                        name="answer"
                        id="answer-{{ $question->id }}"
                        class="form-control{{ $errors->has('answer') ? ' is-invalid' : '' }}"
                        rows="4"
                        maxlength="3000"
                        required
                        placeholder="Share a clear, helpful answer for the reader..."
                    >{{ old('answer') }}</textarea>
                    @error('answer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-success mt-3">Publish answer</button>
                </form>
            @endif
        </div>
    @empty
        <div class="chart-card p-4 text-center">
            <div class="text-muted mb-2"><i class="fa-solid fa-circle-question fa-2x"></i></div>
            <h5 class="mb-1">No questions yet</h5>
            <p class="text-muted mb-0">When readers ask you questions from your posts or author page, they will appear here.</p>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.js-author-question-answer-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Publishing...';

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
                    throw new Error(payload.message || 'Unable to publish answer.');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message || 'Unable to publish answer.');
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
</script>
@endpush
