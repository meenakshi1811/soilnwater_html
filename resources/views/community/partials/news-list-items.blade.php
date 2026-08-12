@php
    $layout = $layout ?? 'list';
    $emptyMessage = $emptyMessage ?? 'No news posts found for this filter yet.';
@endphp
@forelse ($posts as $post)
    @include('community.partials.news-list-item', [
        'post' => $post,
        'engagement' => $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []],
    ])
@empty
    @if(($layout ?? 'list') !== 'append')
        <div class="community-news-empty">
            <div class="community-news-empty__icon"><i class="fa-solid fa-newspaper"></i></div>
            <h3>No news yet</h3>
            <p>{{ $emptyMessage }}</p>
            @auth
                <a href="{{ route('community.posts.create', ['type' => 'news']) }}" class="btn btn-success btn-sm">Publish news</a>
            @endauth
        </div>
    @endif
@endforelse
