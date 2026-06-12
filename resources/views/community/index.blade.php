@extends('frontend.layouts.app')

@push('styles')
<style>
    .community-post-card-excerpt {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="about-page">
    <section class="about-banner">
        @php($authorName = isset($activeAuthor) ? ($activeAuthor->name ?? $activeAuthor->full_name ?? 'Community author') : null)
        <h1>{{ $authorName ? $authorName . "'s Community Posts" : 'Community' }}</h1>
        <p>{{ $authorName ? 'Posts by '.$authorName.'.' : 'Read and share community posts.' }}</p>
        @auth
            <a href="{{ route('community.posts.create') }}" class="btn btn-light mt-3"><i class="fa-solid fa-plus me-2"></i>Create a Post</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-light mt-3">Login to Post</a>
        @endauth
    </section>

    <div class="about-inner">
        <section class="sec">
            <div class="sec-head">
                <div class="sec-title"><span class="icon"><i class="fa-solid fa-layer-group"></i></span> Browse sections</div>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-4">
                @php($sectionRoute = isset($activeAuthor) ? 'community.authors.show' : 'community.index')
                @php($sectionRouteParams = isset($activeAuthor) ? ['uniqueName' => $activeAuthor->authorUniqueName()] : [])
                <a href="{{ route($sectionRoute, $sectionRouteParams) }}" class="btn btn-sm {{ $activeType ? 'btn-outline-success' : 'btn-success' }}">All</a>
                @foreach($types as $key => $type)
                    <a href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['type' => $key])) }}" class="btn btn-sm {{ $activeType === $key ? 'btn-success' : 'btn-outline-success' }}">{{ $type['label'] }}</a>
                @endforeach
            </div>

            @if($activeType && isset($types[$activeType]))
                <div class="alert alert-success">
                    <strong>{{ $types[$activeType]['label'] }}:</strong> {{ $types[$activeType]['description'] }}
                    <div class="mt-2 small">Categories: {{ implode(', ', $types[$activeType]['categories']) }}</div>
                </div>
            @endif

            <div class="row g-4">
                @forelse($posts as $post)
                    <div class="col-md-6 col-lg-4">
                        <article class="about-box h-100">
                            @if($post->featured_image_path)
                                <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="img-fluid rounded mb-3" style="height:180px;width:100%;object-fit:cover;">
                            @endif
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-success">{{ $post->typeLabel() }}</span>
                                <span class="badge bg-light text-dark border">{{ $post->content_type === 'my-area' ? data_get($post->meta, 'report_type', $post->category) : $post->category }}</span>
                            </div>
                            <h4><a href="{{ route('community.show', $post) }}" class="text-decoration-none text-dark">{{ $post->title }}</a></h4>
                            <p class="community-post-card-excerpt">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 220) }}</p>
                            <div class="small text-muted">
                                By
                                @if($post->user)
                                    <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="text-muted fw-semibold">{{ $post->user->name ?? $post->user->full_name ?? 'Community author' }}</a>
                                @else
                                    Community author
                                @endif
                                · {{ $post->published_at?->format('M d, Y') }}
                            </div>
                            <a href="{{ route('community.show', $post) }}" class="btn btn-outline-success btn-sm mt-3">Read more</a>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info mb-0">{{ isset($activeAuthor) ? 'No posts found for this author yet.' : 'No posts found for this section yet.' }}</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $posts->links() }}</div>
        </section>
    </div>
</div>
@endsection
