@extends('backend.layouts.app')
@section('title','Educator Enquiries')

@section('content')
<div class="admin-panel ems-page">
    <div class="mb-4">
        <p class="ems-kicker mb-1">Educator Portal</p>
        <h2 class="admin-title mb-1">Questions &amp; Enquiries</h2>
        <p class="text-secondary mb-0">Reply to questions from students and parents. They are notified by email and portal when you answer.</p>
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
            <span class="btn btn-sm btn-warning disabled">
                Pending <span class="badge bg-light text-dark ms-1">{{ $pendingCount }}</span>
            </span>
            <span class="btn btn-sm btn-success disabled">
                Answered <span class="badge bg-light text-dark ms-1">{{ $answeredCount }}</span>
            </span>
        </div>
    </div>

    @forelse($enquiries as $enquiry)
        <div class="chart-card p-3 p-lg-4 mb-3">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                        <span class="badge {{ $enquiry->isAnswered() ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $enquiry->isAnswered() ? 'Answered' : 'Pending' }}
                        </span>
                        @if($enquiry->subject)
                            <span class="badge bg-light text-dark border">{{ $enquiry->subject }}</span>
                        @endif
                    </div>
                    <div class="text-muted small">
                        From <strong>{{ $enquiry->name }}</strong>
                        @if($enquiry->email)
                            · {{ $enquiry->email }}
                        @endif
                        @if($enquiry->phone)
                            · {{ $enquiry->phone }}
                        @endif
                        · {{ $enquiry->created_at?->format('d M Y, h:i A') }}
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 bg-light mb-3">
                <div class="small text-muted text-uppercase fw-semibold mb-1">Question</div>
                <p class="mb-0" style="white-space:pre-line">{{ $enquiry->message }}</p>
            </div>

            @if($enquiry->isAnswered())
                <div class="border rounded-3 p-3" style="background:linear-gradient(180deg,#f0fdf4 0%,#ecfdf5 100%);">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-1">
                        <div class="small text-success text-uppercase fw-semibold">Your answer</div>
                        <small class="text-muted">{{ $enquiry->answered_at?->format('d M Y, h:i A') }}</small>
                    </div>
                    <p class="mb-0" style="white-space:pre-line">{{ $enquiry->answer }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('educator.enquiries.answer', $enquiry) }}" class="js-educator-enquiry-answer-form">
                    @csrf
                    <label class="form-label fw-semibold" for="answer-{{ $enquiry->id }}">Write your answer</label>
                    <textarea
                        name="answer"
                        id="answer-{{ $enquiry->id }}"
                        class="form-control{{ $errors->has('answer') ? ' is-invalid' : '' }}"
                        rows="4"
                        maxlength="3000"
                        required
                        placeholder="Share a clear, helpful answer for the student or parent..."
                    >{{ old('answer') }}</textarea>
                    @error('answer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-success mt-3">Send answer</button>
                </form>
            @endif
        </div>
    @empty
        <div class="chart-card p-4 text-center">
            <div class="text-muted mb-2"><i class="fa-solid fa-envelope fa-2x"></i></div>
            <h5 class="mb-1">No enquiries yet</h5>
            <p class="text-muted mb-0">When students or parents ask questions from your public profile, they will appear here.</p>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.js-educator-enquiry-answer-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Sending...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    },
                    body: new FormData(form),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to send answer.');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message || 'Unable to send answer.');
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
</script>
@endpush
