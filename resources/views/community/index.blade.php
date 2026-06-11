@extends('frontend.layouts.app')

@section('content')
<div class="about-page">
    <section class="about-banner">
        <h1>Community</h1>
        <p>Read and share articles, reports, news, stories, poetry, awareness posts, local voices, discussions, and more.</p>
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
                <a href="{{ route('community.index') }}" class="btn btn-sm {{ $activeType ? 'btn-outline-success' : 'btn-success' }}">All</a>
                @foreach($types as $key => $type)
                    <a href="{{ route('community.index', ['type' => $key]) }}" class="btn btn-sm {{ $activeType === $key ? 'btn-success' : 'btn-outline-success' }}">{{ $type['label'] }}</a>
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
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($post->featured_image_path) }}" alt="{{ $post->title }}" class="img-fluid rounded mb-3" style="height:180px;width:100%;object-fit:cover;">
                            @endif
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-success">{{ $post->typeLabel() }}</span>
                                <span class="badge bg-light text-dark border">{{ $post->category }}</span>
                            </div>
                            <h4><a href="{{ route('community.show', $post) }}" class="text-decoration-none text-dark">{{ $post->title }}</a></h4>
                            <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 140) }}</p>
                            <div class="small text-muted">By {{ $post->user?->name ?? $post->user?->full_name ?? 'Community author' }} · {{ $post->published_at?->format('M d, Y') }}</div>
                            <a href="{{ route('community.show', $post) }}" class="btn btn-outline-success btn-sm mt-3">Read more</a>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info mb-0">No posts found for this section yet.</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $posts->links() }}</div>
        </section>
    </div>
</div>
@endsection
