@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $tags = collect($post->tags ?? [])->filter()->values();
    $followedTopics = $followedTopics ?? [];
@endphp

@if($tags->isNotEmpty())
    <div class="community-news-sidebar__card">
        <p class="community-news-sidebar__label">Topics</p>
        <div class="news-detail-tags news-detail-tags--sidebar" aria-label="Post tags">
            <ul class="news-detail-tags__list">
                @foreach($tags as $tag)
                    @php
                        $normalizedTag = \App\Models\CommunityTopicFollow::normalizeTopic((string) $tag);
                        $isFollowingTopic = auth()->check() && in_array($normalizedTag, $followedTopics, true);
                    @endphp
                    <li class="news-detail-tags__item">
                        <span class="news-detail-tags__chip" title="{{ $tag }}">
                            <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                            <span class="news-detail-tags__label">{{ \Illuminate\Support\Str::limit($tag, 42) }}</span>
                        </span>
                        @auth
                            @if($post->isPubliclyVisible())
                                <button type="button"
                                    class="btn btn-sm news-detail-tags__follow {{ $isFollowingTopic ? 'btn-success' : 'btn-outline-success' }} js-community-follow-topic {{ $isFollowingTopic ? 'is-following' : '' }}"
                                    data-url="{{ route('community.subscriptions.topic.toggle') }}"
                                    data-topic="{{ $tag }}">
                                    {{ $isFollowingTopic ? 'Following' : 'Follow' }}
                                </button>
                            @endif
                        @endauth
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
