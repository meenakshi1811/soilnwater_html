@extends('frontend.layouts.app')

@section('content')
<div class="about-page">
    <section class="about-banner">
        <div class="mb-2">
            <span class="badge bg-light text-dark">{{ $post->typeLabel() }}</span>
            <span class="badge bg-light text-dark">{{ $post->category }}</span>
        </div>
        <h1>{{ $post->title }}</h1>
        <p>
            By
            @if($post->user)
                <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="text-white text-decoration-underline">{{ $post->user->name ?? $post->user->full_name ?? 'Community author' }}</a>
            @else
                Community author
            @endif
            · {{ $post->published_at?->format('M d, Y') ?? 'Draft' }}
        </p>
        @auth
            @if(auth()->id() === $post->user_id || auth()->user()->isAdmin())
                <a href="{{ route('community.posts.edit', $post) }}" class="btn btn-light mt-2"><i class="fa-solid fa-pen me-2"></i>Edit Post</a>
            @endif
        @endauth
    </section>

    <div class="about-inner">
        <section class="sec">
            @if($post->featured_image_path)
                <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="img-fluid rounded mb-4" style="max-height:420px;width:100%;object-fit:cover;">
            @endif

            @if($post->excerpt)
                <p class="lead">{{ $post->excerpt }}</p>
            @endif

            <div class="community-post-body" style="line-height:1.8;">{!! $post->body !!}</div>

            @php
                $visibleMeta = collect($post->meta ?? [])->except(['location_lat', 'location_lng']);
            @endphp
            @if($visibleMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>Additional details</h4>
                    <ul class="about-list mb-0">
                        @foreach($visibleMeta as $key => $value)
                            @continue(blank($value) || $value === false)
                            <li><strong>{{ \Illuminate\Support\Str::headline($key) }}:</strong> {{ is_bool($value) ? 'Yes' : $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($post->tags))
                <div class="mt-4 d-flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                        <span class="badge bg-light text-dark border">#{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            <div class="about-box mt-4">
                <h4>Community engagement</h4>
                @php
                    $reactionCounts = $post->reactions->groupBy('reaction')->map->count();
                    $userReactions = auth()->check() ? $post->reactions->where('user_id', auth()->id())->pluck('reaction')->all() : [];
                    $reactionOptions = [
                        'Helpful' => 'fa-solid fa-hand-holding-heart',
                        'Inspiring' => 'fa-solid fa-lightbulb',
                        'Excellent' => 'fa-solid fa-star',
                        'Informative' => 'fa-solid fa-circle-info',
                    ];
                @endphp
                @auth
                    <div class="d-flex flex-wrap gap-2 mb-3" id="communityReactionButtons">
                        @foreach($reactionOptions as $reaction => $icon)
                            <form method="POST" action="{{ route('community.react', $post) }}" class="js-community-reaction-form">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $reaction }}">
                                <button type="submit" class="btn {{ in_array($reaction, $userReactions, true) ? 'btn-success' : 'btn-outline-success' }} btn-sm" data-reaction-button="{{ $reaction }}">
                                    <i class="{{ $icon }} me-1" aria-hidden="true"></i><span class="reaction-label">{{ $reaction }}</span> <span class="reaction-count">{{ $reactionCounts[$reaction] ?? 0 }}</span>
                                </button>
                            </form>
                        @endforeach
                        @if($post->user && auth()->id() !== $post->user_id)
                            <form method="POST" action="{{ route('community.authors.follow', $post->user) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Follow Author</button>
                            </form>
                        @endif
                    </div>
                @else
                    <p><a href="{{ route('login') }}">Login</a> to react or follow this author.</p>
                @endauth
                <ul class="about-list mb-0">
                    <li>
                        Author profile:
                        @if($post->user)
                            <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}">{{ $post->user->name ?? $post->user->full_name ?? 'Community author' }}</a>
                        @else
                            Community author
                        @endif
                    </li>
                </ul>
            </div>
        </section>
    </div>
</div>
@endsection


@push('scripts')
<script>
    document.querySelectorAll('.js-community-reaction-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('[data-reaction-button]');
            const originalHtml = button.innerHTML;
            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to add reaction.');
                }

                document.querySelectorAll('[data-reaction-button] .reaction-count').forEach((countEl) => {
                    countEl.textContent = '0';
                });

                Object.entries(payload.counts || {}).forEach(([reaction, count]) => {
                    const countEl = document.querySelector(`[data-reaction-button="${reaction}"] .reaction-count`);
                    if (countEl) countEl.textContent = count;
                });

                if (payload.reaction) {
                    const reactionButton = document.querySelector(`[data-reaction-button="${payload.reaction}"]`);
                    if (reactionButton) {
                        reactionButton.classList.toggle('btn-success', Boolean(payload.active));
                        reactionButton.classList.toggle('btn-outline-success', !payload.active);
                    }
                }
            } catch (error) {
                alert(error.message || 'Unable to add reaction.');
                button.innerHTML = originalHtml;
            } finally {
                button.disabled = false;
            }
        });
    });
</script>
@endpush
