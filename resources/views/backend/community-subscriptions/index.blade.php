@extends('backend.layouts.app')

@section('title', 'Community Subscriptions')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community engagement</p>
            <h2 class="admin-title mb-1">My Subscriptions</h2>
            <p class="mb-0 text-secondary">Manage category subscriptions and topic follows. Matching posts appear first in the community hub, and you receive email and portal alerts when new posts are published.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="chart-card p-3 h-100">
                <h5 class="mb-3">Subscribed categories</h5>
                @forelse($categorySubscriptions as $subscription)
                    <div class="d-flex justify-content-between align-items-center gap-2 border rounded p-2 mb-2">
                        <span>{{ $subscription->label() }}</span>
                        <form method="POST" action="{{ route('community.subscriptions.category.toggle') }}">
                            @csrf
                            <input type="hidden" name="content_type" value="{{ $subscription->content_type }}">
                            <input type="hidden" name="category" value="{{ $subscription->category }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Unsubscribe</button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted mb-0">No category subscriptions yet. Subscribe from a post page or the community hub.</p>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card p-3 h-100">
                <h5 class="mb-3">Followed topics</h5>
                @forelse($topicFollows as $follow)
                    <div class="d-flex justify-content-between align-items-center gap-2 border rounded p-2 mb-2">
                        <span>#{{ $follow->displayTopic() }}</span>
                        <form method="POST" action="{{ route('community.subscriptions.topic.toggle') }}">
                            @csrf
                            <input type="hidden" name="topic" value="{{ $follow->topic }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Unfollow</button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted mb-0">No followed topics yet. Follow topics from tag badges on community posts.</p>
                @endforelse
            </div>
        </div>

        <div class="col-12">
            <div class="chart-card p-3">
                <h5 class="mb-3">Subscribe to a category</h5>
                <form method="POST" action="{{ route('community.subscriptions.category.toggle') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <select name="content_type" id="subscriptionContentType" class="form-select" required>
                            <option value="">Select section</option>
                            @foreach($types as $key => $type)
                                <option value="{{ $key }}">{{ $type['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category" id="subscriptionCategory" class="form-select" required>
                            <option value="">Select category</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const types = @json(collect($types)->map(fn ($type, $key) => ['key' => $key, 'categories' => $type['categories']])->values());
        const typeSelect = document.getElementById('subscriptionContentType');
        const categorySelect = document.getElementById('subscriptionCategory');

        if (!typeSelect || !categorySelect) {
            return;
        }

        typeSelect.addEventListener('change', function () {
            const selected = types.find((entry) => entry.key === typeSelect.value);
            categorySelect.innerHTML = '<option value="">Select category</option>';

            (selected?.categories || []).forEach(function (category) {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
        });
    })();
</script>
@endpush
