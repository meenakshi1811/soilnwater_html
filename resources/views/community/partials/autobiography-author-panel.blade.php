@if($post->content_type === 'autobiography')
    <div class="autobiography-author-panel about-box mb-4">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            <div class="autobiography-author-panel__avatar">
                @if(filled($post->authorAvatarUrl()))
                    <img src="{{ $post->authorAvatarUrl() }}" alt="{{ $post->authorDisplayName() }}" class="rounded-circle">
                @else
                    <span class="autobiography-author-panel__initials rounded-circle">{{ $post->authorInitials() }}</span>
                @endif
            </div>
            <div class="flex-grow-1">
                <p class="autobiography-author-panel__kicker mb-1">Author</p>
                <h4 class="h5 mb-2">
                    @if($post->showsAuthorProfileLink())
                        <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="text-decoration-none">{{ $post->authorDisplayName() }}</a>
                    @else
                        {{ $post->authorDisplayName() }}
                    @endif
                </h4>
                @if(filled($post->authorBioForDisplay()))
                    <p class="text-muted mb-2">{{ $post->authorBioForDisplay() }}</p>
                @endif
                @if(filled($post->location))
                    <p class="autobiography-author-panel__location mb-0">
                        <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>{{ $post->location }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif
