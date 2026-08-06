@php
    $tags = collect($post->tags ?? [])->filter()->values();
    $authorName = $post->authorDisplayName();
    $authorBio = data_get($post->meta, 'author_bio');
    $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authorName, 0, 1));
@endphp

        @if($tags->isNotEmpty())
            <div class="community-article-tags" aria-label="Post tags">
                @foreach($tags as $tag)
                    @php
                        $normalizedTag = \App\Models\CommunityTopicFollow::normalizeTopic((string) $tag);
                        $isFollowingTopic = auth()->check() && in_array($normalizedTag, $followedTopics ?? [], true);
                    @endphp
                    <span class="community-article-tag">
                        <i class="fa-solid fa-hashtag" aria-hidden="true"></i>{{ $tag }}
                    </span>
                    @auth
                        @if($post->isPubliclyVisible())
                            <button type="button"
                                class="btn btn-sm {{ $isFollowingTopic ? 'btn-success' : 'btn-outline-success' }} js-community-follow-topic {{ $isFollowingTopic ? 'is-following' : '' }}"
                                data-url="{{ route('community.subscriptions.topic.toggle') }}"
                                data-topic="{{ $tag }}">
                                {{ $isFollowingTopic ? 'Following' : 'Follow' }}
                            </button>
                        @endif
                    @endauth
                @endforeach
            </div>
        @endif

        <div class="community-article-author-card">
            @if(filled($post->authorAvatarUrl()))
                <img src="{{ $post->authorAvatarUrl() }}" alt="" class="community-article-author-card__avatar">
            @else
                <span class="community-article-author-card__initials" aria-hidden="true">{{ $authorInitial }}</span>
            @endif
            <div class="community-article-author-card__body">
                <p class="community-article-author-card__name">
                    <i class="fa-solid fa-pen-nib community-article-author-card__name-icon" aria-hidden="true"></i>
                    @if($post->showsAuthorProfileLink())
                        <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}">{{ $authorName }}</a>
                    @else
                        {{ $authorName }}
                    @endif
                </p>
                @if(filled($authorBio))
                    <p class="community-article-author-card__bio">{{ \Illuminate\Support\Str::limit($authorBio, 120) }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
