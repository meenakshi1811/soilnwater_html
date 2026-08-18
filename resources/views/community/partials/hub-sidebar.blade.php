@php
    $popularTopics = $popularTopics ?? collect();
    $topContributors = $topContributors ?? collect();
@endphp

<aside class="community-hub-sidebar" aria-label="Community highlights">
    <section class="community-hub-sidebar__card">
        <h3 class="community-hub-sidebar__title">Popular Topics</h3>
        <div class="community-topic-cloud">
            @forelse ($popularTopics as $topic)
                <a href="{{ route('community.index', ['category' => $topic->category]) }}" class="community-topic-chip">
                    {{ $topic->category }} <span>({{ $topic->posts_count }})</span>
                </a>
            @empty
                <p class="community-hub-sidebar__empty">Topics will appear as posts are published.</p>
            @endforelse
        </div>
    </section>

    <section class="community-hub-sidebar__card">
        <h3 class="community-hub-sidebar__title">Top Contributors</h3>
        <ol class="community-contributors">
            @forelse ($topContributors as $index => $contributor)
                @php
                    $contributorName = $contributor->authorDisplayName();
                    $points = (int) ($contributor->views_sum ?? 0);
                @endphp
                <li class="community-contributor">
                    <span class="community-contributor__rank">{{ $index + 1 }}</span>
                    @include('community.partials.author-avatar', [
                        'avatarUrl' => $contributor->authorImageUrl(),
                        'initials' => $contributor->authorInitials(),
                        'alt' => $contributorName,
                        'sizeClass' => 'community-contributor__avatar',
                    ])
                    <div class="community-contributor__meta">
                        <a href="{{ route('community.authors.show', $contributor->authorUniqueName()) }}" class="community-contributor__name">{{ $contributorName }}</a>
                        <p>{{ number_format($points) }} Points · {{ $contributor->posts_count }} {{ \Illuminate\Support\Str::plural('Post', $contributor->posts_count) }}</p>
                    </div>
                </li>
            @empty
                <li class="community-hub-sidebar__empty">Contributors will appear here soon.</li>
            @endforelse
        </ol>
    </section>

</aside>
