@if(\App\Models\CommunityPost::supportsStarRating($post->content_type))
    @php
        $averageRating = $post->averageStarRating();
        $ratingsCount = $post->star_ratings_count ?? $post->starRatings->count();
        $heading = match ($post->content_type) {
            'poetry' => 'Poetry rating',
            'stories' => 'Story rating',
            default => 'Rating',
        };
        $hint = match ($post->content_type) {
            'poetry' => 'Reader ratings from 1 to 5 stars.',
            'stories' => 'Reader ratings from 1 to 5 stars.',
            default => 'Reader ratings.',
        };
        $panelClass = $post->content_type === 'poetry' ? 'poetry-rating-panel' : 'story-rating-panel';
    @endphp
    <div class="{{ $panelClass }} {{ $wrapperClass ?? 'about-box mt-4' }}">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <h4 class="mb-1 {{ isset($compact) && $compact ? 'h5' : '' }}">{{ $headingLabel ?? $heading }}</h4>
                <p class="text-muted small mb-0">{{ $hint }}</p>
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
            @else
                <span class="badge bg-light text-dark border">No ratings yet</span>
            @endif
        </div>
    </div>
@endif
