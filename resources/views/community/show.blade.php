@extends('frontend.layouts.app')

@section('content')
<div class="about-page">
    <section class="about-banner">
        <div class="mb-2">
            <span class="badge bg-light text-dark">{{ $post->typeLabel() }}</span>
            <span class="badge bg-light text-dark">{{ $post->category }}</span>
        </div>
        <h1>{{ $post->title }}</h1>
        <p>By {{ $post->user?->name ?? $post->user?->full_name ?? 'Community author' }} · {{ $post->published_at?->format('M d, Y') ?? 'Draft' }}</p>
        @auth
            @if(auth()->id() === $post->user_id || auth()->user()->isAdmin())
                <a href="{{ route('community.posts.edit', $post) }}" class="btn btn-light mt-2"><i class="fa-solid fa-pen me-2"></i>Edit Post</a>
            @endif
        @endauth
    </section>

    <div class="about-inner">
        <section class="sec">
            @if($post->featured_image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($post->featured_image_path) }}" alt="{{ $post->title }}" class="img-fluid rounded mb-4" style="max-height:420px;width:100%;object-fit:cover;">
            @endif

            @if($post->excerpt)
                <p class="lead">{{ $post->excerpt }}</p>
            @endif

            <div class="community-post-body" style="white-space: pre-line; line-height:1.8;">{{ $post->body }}</div>

            @if(!empty($post->meta))
                <div class="about-box mt-4">
                    <h4>Additional details</h4>
                    <ul class="about-list mb-0">
                        @foreach($post->meta as $key => $value)
                            @continue(blank($value) || $value === false)
                            <li><strong>{{ \Illuminate\Support\Str::headline($key) }}:</strong> {{ is_bool($value) ? 'Yes' : $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($post->tags))
                <div class="mt-4 d-flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                        <span class="badge bg-light text-dark border">#{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            <div class="about-box mt-4">
                <h4>Community engagement</h4>
                @php
                    $reactionCounts = $post->reactions->groupBy('reaction')->map->count();
                    $reactionOptions = ['Helpful', 'Inspiring', 'Excellent', 'Informative'];
                @endphp
                @auth
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($reactionOptions as $reaction)
                            <form method="POST" action="{{ route('community.react', $post) }}">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $reaction }}">
                                <button type="submit" class="btn btn-outline-success btn-sm">
                                    {{ $reaction }} {{ $reactionCounts[$reaction] ?? 0 }}
                                </button>
                            </form>
                        @endforeach
                        @if($post->user && auth()->id() !== $post->user_id)
                            <form method="POST" action="{{ route('community.authors.follow', $post->user) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Follow Author</button>
                            </form>
                        @endif
                    </div>
                @else
                    <p><a href="{{ route('login') }}">Login</a> to react or follow this author.</p>
                @endauth
                <ul class="about-list mb-0">
                    <li>Author profile: {{ $post->user?->name ?? $post->user?->full_name ?? 'Community author' }}</li>
                    <li>Reactions: Helpful, Inspiring, Excellent, Informative.</li>
                    <li>Comments/discussions: {{ $post->allow_comments ? 'Allowed' : 'Disabled by author' }}.</li>
                    <li>Digest-ready for trending articles, top writers, and popular discussions.</li>
                </ul>
            </div>
        </section>
    </div>
</div>
@endsection
