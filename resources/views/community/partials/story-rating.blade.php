@if(\App\Models\CommunityPost::supportsStarRating($post->content_type))
    @php
        $averageRating = $post->averageStarRating();
        $ratingsCount = $post->star_ratings_count ?? $post->starRatings->count();
        $userRating = auth()->check() ? $post->userStarRating(auth()->id()) : null;
        $ratingTitle = match ($post->content_type) {
            'poetry' => 'Poetry rating',
            'autobiography' => 'Autobiography rating',
            default => 'Story rating',
        };
        $ratingHint = match ($post->content_type) {
            'poetry' => 'Readers can rate this poem from 1 to 5 stars.',
            'autobiography' => 'Readers can rate this life story from 1 to 5 stars.',
            default => 'Readers can rate this story from 1 to 5 stars.',
        };
        $loginHint = match ($post->content_type) {
            'poetry' => 'Login to rate this poem.',
            'autobiography' => 'Login to rate this autobiography.',
            default => 'Login to rate this story.',
        };
        $panelClass = match ($post->content_type) {
            'poetry' => 'poetry-rating-panel',
            'autobiography' => 'autobiography-rating-panel',
            default => 'story-rating-panel',
        };
    @endphp
    <div class="{{ $panelClass }} about-box mt-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h4 class="mb-1">{{ $ratingTitle }}</h4>
                <p class="text-muted small mb-0">{{ $ratingHint }}</p>
            </div>
            @if($averageRating)
                <div class="text-end">
                    <div class="story-rating-summary__score">{{ number_format($averageRating, 1) }}</div>
                    <div class="story-rating-summary__stars" aria-label="Average rating {{ $averageRating }} out of 5">
                        @for($star = 1; $star <= 5; $star++)
                            <i class="fa-solid fa-star{{ $star <= round($averageRating) ? '' : '-o' }}" aria-hidden="true"></i>
                        @endfor
                    </div>
                    <small class="text-muted">{{ number_format($ratingsCount) }} rating{{ $ratingsCount === 1 ? '' : 's' }}</small>
                </div>
            @endif
        </div>

        @auth
            <form method="POST" action="{{ route('community.story.rate', $post) }}" class="js-story-rating-form">
                @csrf
                <div class="story-rating-input d-flex flex-wrap align-items-center gap-2">
                    <span class="small fw-semibold me-1">Your rating:</span>
                    @for($star = 1; $star <= 5; $star++)
                        <button
                            type="submit"
                            name="rating"
                            value="{{ $star }}"
                            class="btn btn-sm {{ ($userRating ?? 0) >= $star ? 'btn-warning' : 'btn-outline-warning' }} story-rating-star"
                            data-story-rating-star="{{ $star }}"
                            aria-label="Rate {{ $star }} star{{ $star === 1 ? '' : 's' }}"
                        >
                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                        </button>
                    @endfor
                </div>
            </form>
        @else
            <p class="mb-0"><a href="{{ route('login') }}">{{ $loginHint }}</a></p>
        @endauth
    </div>
@endif
