@if($post->content_type === 'poetry')
    <div class="about-box mt-0 mb-4 poetry-author-panel">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            <div class="poetry-author-panel__avatar">
                @if(filled($post->authorAvatarUrl()))
                    <img src="{{ $post->authorAvatarUrl() }}" alt="{{ $post->authorDisplayName() }}" class="rounded-circle">
                @else
                    <span class="poetry-author-panel__initials rounded-circle">{{ $post->authorInitials() }}</span>
                @endif
            </div>
            <div class="flex-grow-1">
                <p class="text-muted small mb-1">Poet</p>
                <h4 class="h5 mb-2">
                    @if($post->showsAuthorProfileLink())
                        <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="text-decoration-none">{{ $post->authorDisplayName() }}</a>
                    @else
                        {{ $post->authorDisplayName() }}
                    @endif
                </h4>
                @if(filled($post->authorBioForDisplay()))
                    <p class="text-muted mb-0">{{ $post->authorBioForDisplay() }}</p>
                @endif
            </div>
        </div>
    </div>
@endif
