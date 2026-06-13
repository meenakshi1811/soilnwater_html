@extends('frontend.layouts.app')

@push('styles')
<style>
    .community-featured-gallery {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .community-featured-gallery--single {
        grid-template-columns: 1fr;
    }
    .community-featured-gallery-item img {
        display: block;
        height: 100%;
        max-height: 420px;
        object-fit: cover;
        width: 100%;
    }
    @@media (max-width: 767.98px) {
        .community-featured-gallery {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="about-page">
    <section class="about-banner">
        <div class="mb-2">
            <span class="badge bg-light text-dark">{{ $post->typeLabel() }}</span>
            <span class="badge bg-light text-dark">{{ filled(data_get($post->meta, 'report_type')) ? data_get($post->meta, 'report_type', $post->category) : $post->category }}</span>
        </div>
        <h1>{{ $post->title }}</h1>
        <p>
            By
            @if($post->user)
                <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="text-white text-decoration-underline">{{ $post->user->name ?? $post->user->full_name ?? 'Community author' }}</a>
            @else
                Community author
            @endif
            · {{ $post->published_at?->format('M d, Y') ?? 'Draft' }}
        </p>
        @auth
            @if(auth()->id() === $post->user_id || auth()->user()->isAdmin())
                <a href="{{ route('community.posts.edit', $post) }}" class="btn btn-light mt-2"><i class="fa-solid fa-pen me-2"></i>Edit Post</a>
            @endif
        @endauth
    </section>

    <div class="about-inner">
        <section class="sec">
            @php
                $featuredImageUrls = $post->featuredImageUrls();
            @endphp
            @if($featuredImageUrls !== [])
                <div class="community-featured-gallery mb-4 {{ count($featuredImageUrls) === 1 ? 'community-featured-gallery--single' : '' }}">
                    @foreach($featuredImageUrls as $index => $imageUrl)
                        <div class="community-featured-gallery-item">
                            <img src="{{ $imageUrl }}" alt="{{ $post->title }} — image {{ $index + 1 }}" class="img-fluid rounded">
                        </div>
                    @endforeach
                </div>
            @endif

            @if($post->excerpt)
                <p class="lead">{{ $post->excerpt }}</p>
            @endif

            @if($post->hasVideo())
                <div class="community-post-video mb-4">
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                            <iframe
                                src="{{ $post->youtubeEmbedUrl() }}"
                                title="Video for {{ $post->title }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded shadow-sm" preload="metadata">
                            <source src="{{ $post->videoFileUrl() }}">
                            Your browser does not support embedded video playback.
                        </video>
                        @if(filled(data_get($post->videoData(), 'name')))
                            <small class="text-muted d-block mt-2">{{ data_get($post->videoData(), 'name') }}</small>
                        @endif
                    @endif
                </div>
            @endif

            <div class="community-post-body">{!! $post->body !!}</div>

            @php
                $resolvedLocation = $post->location ?? data_get($post->meta, 'location');
                $formFieldLabels = \App\Support\CommunityPostFormFields::labels();
                $visibleMeta = collect($post->meta ?? [])->except(['location', 'location_lat', 'location_lng']);
                $reportMetaLabels = [
                    'report_subtitle' => 'Subtitle',
                    'reporting_period' => 'Reporting period',
                    'report_date' => 'Report date',
                    'prepared_by' => 'Prepared by',
                    'report_scope' => 'Scope / objective',
                    'methodology' => 'Methodology',
                    'data_sources' => 'Data sources',
                    'key_findings' => 'Key findings',
                    'recommendations' => 'Recommendations',
                    'location' => 'Coverage / study area',
                ];
                $newsMetaLabels = [
                    'news_subtitle' => 'Subtitle / deck',
                    'news_dateline' => 'Dateline',
                    'news_date' => 'News date',
                    'reporter_name' => 'Reporter / byline',
                    'news_source' => 'Primary source',
                    'source_url' => 'Source URL',
                    'fact_summary' => 'Verified facts / 5W summary',
                    'verification_notes' => 'Verification notes',
                    'impact_area' => 'Impact / affected area',
                    'quote_attribution' => 'Quote / attribution',
                    'location' => 'News location',
                ];
                $myAreaMetaLabels = [
                    'report_type' => 'Report type',
                    'issue_priority' => 'Priority',
                    'issue_status' => 'Status',
                    'reported_to' => 'Reported to',
                    'issue_reference' => 'Reference / complaint no.',
                    'location' => 'GPS issue location',
                ];
                $myVoiceMetaLabels = [
                    'voice_topic' => 'Topic',
                    'voice_perspective' => 'Perspective',
                    'location' => 'Related location',
                ];
                $reportMetaOrder = array_keys($reportMetaLabels);
                $newsMetaOrder = array_keys($newsMetaLabels);
                $myAreaMetaOrder = array_keys($myAreaMetaLabels);
                $myVoiceMetaOrder = array_keys($myVoiceMetaLabels);
                $orderedReportMeta = collect($reportMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedNewsMeta = collect($newsMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedMyAreaMeta = collect($myAreaMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $orderedMyVoiceMeta = collect($myVoiceMetaOrder)
                    ->mapWithKeys(fn ($key) => [$key => data_get($post->meta, $key)])
                    ->filter(fn ($value) => filled($value) || is_bool($value));
                $additionalReportMeta = $visibleMeta->except([...$reportMetaOrder, 'report_format', 'author_bio']);
                $additionalNewsMeta = $visibleMeta->except([...$newsMetaOrder, 'author_bio']);
                $additionalMyAreaMeta = $visibleMeta->except([...$myAreaMetaOrder, 'report_format', 'issue_attachments', 'author_bio']);
                $additionalMyVoiceMeta = $visibleMeta->except([...$myVoiceMetaOrder, 'author_bio']);
            @endphp
            @if($post->content_type === 'reports' && blank(data_get($post->meta, 'report_type')) && $orderedReportMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>Report details</h4>
                    <div class="row g-3">
                        @foreach($orderedReportMeta as $key => $value)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <strong class="d-block mb-1">{{ $reportMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                                    <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php
                    $visibleMeta = $additionalReportMeta;
                @endphp
            @endif
            @if($post->content_type === 'news' && $orderedNewsMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>News details</h4>
                    <div class="row g-3">
                        @foreach($orderedNewsMeta as $key => $value)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <strong class="d-block mb-1">{{ $newsMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                                    @if($key === 'source_url')
                                        <a href="{{ $value }}" target="_blank" rel="noopener">{{ $value }}</a>
                                    @else
                                        <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php
                    $visibleMeta = $additionalNewsMeta;
                @endphp
            @endif
            @if($post->content_type === 'reports' && ($orderedMyAreaMeta->isNotEmpty() || !empty(data_get($post->meta, 'issue_attachments'))))
                <div class="about-box mt-4">
                    <h4>My Area report details</h4>
                    @if($orderedMyAreaMeta->isNotEmpty())
                        <div class="row g-3 mb-3">
                            @foreach($orderedMyAreaMeta as $key => $value)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <strong class="d-block mb-1">{{ $myAreaMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                                        <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty(data_get($post->meta, 'issue_attachments')))
                        <h5 class="h6">Evidence files</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(data_get($post->meta, 'issue_attachments', []) as $attachment)
                                <a href="{{ data_get($attachment, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-paperclip me-1"></i>{{ data_get($attachment, 'name', 'Attachment') }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @php
                    $visibleMeta = $additionalMyAreaMeta;
                @endphp
            @endif
            @if($post->content_type === 'my-voice' && $orderedMyVoiceMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>My Voice details</h4>
                    <div class="row g-3">
                        @foreach($orderedMyVoiceMeta as $key => $value)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light">
                                    <strong class="d-block mb-1">{{ $myVoiceMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                                    <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php
                    $visibleMeta = $additionalMyVoiceMeta;
                @endphp
            @endif
            @if(filled($resolvedLocation))
                <div class="about-box mt-4">
                    <h4>Location</h4>
                    <p class="mb-0">{{ $resolvedLocation }}</p>
                    @if(filled($post->location_lat) && filled($post->location_lng))
                        <small class="text-muted">Coordinates: {{ $post->location_lat }}, {{ $post->location_lng }}</small>
                    @endif
                </div>
            @endif
            @if($visibleMeta->isNotEmpty())
                <div class="about-box mt-4">
                    <h4>Additional details</h4>
                    <ul class="about-list mb-0">
                        @foreach($visibleMeta as $key => $value)
                            @if(blank($value) && $value !== false)
                                @continue
                            @endif
                            <li><strong>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}:</strong> {!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</li>
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
                    $userReactions = auth()->check() ? $post->reactions->where('user_id', auth()->id())->pluck('reaction')->all() : [];
                    $reactionOptions = $post->content_type === 'reports' && filled(data_get($post->meta, 'report_type'))
                        ? [
                            'Support' => 'fa-solid fa-hand-holding-heart',
                            'Vote' => 'fa-solid fa-square-poll-vertical',
                            'Helpful' => 'fa-solid fa-circle-info',
                            'Informative' => 'fa-solid fa-lightbulb',
                        ]
                        : [
                            'Helpful' => 'fa-solid fa-hand-holding-heart',
                            'Inspiring' => 'fa-solid fa-lightbulb',
                            'Excellent' => 'fa-solid fa-star',
                            'Informative' => 'fa-solid fa-circle-info',
                        ];
                @endphp
                @auth
                    <div class="d-flex flex-wrap gap-2 mb-3" id="communityReactionButtons">
                        @foreach($reactionOptions as $reaction => $icon)
                            <form method="POST" action="{{ route('community.react', $post) }}" class="js-community-reaction-form">
                                @csrf
                                <input type="hidden" name="reaction" value="{{ $reaction }}">
                                <button type="submit" class="btn {{ in_array($reaction, $userReactions, true) ? 'btn-success' : 'btn-outline-success' }} btn-sm" data-reaction-button="{{ $reaction }}">
                                    <i class="{{ $icon }} me-1" aria-hidden="true"></i><span class="reaction-label">{{ $reaction }}</span> <span class="reaction-count">{{ $reactionCounts[$reaction] ?? 0 }}</span>
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
                    <li>
                        Author profile:
                        @if($post->user)
                            <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}">{{ $post->user->name ?? $post->user->full_name ?? 'Community author' }}</a>
                        @else
                            Community author
                        @endif
                    </li>
                </ul>
            </div>

            <div class="about-box mt-4" id="discussion">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                    <div>
                        <h4 class="mb-1">Discussion</h4>
                        <p class="text-muted mb-0">Ask questions, share answers, and reply to other readers on this post.</p>
                    </div>
                    <span class="badge {{ $post->allow_comments ? 'bg-success' : 'bg-secondary' }}">{{ $post->allow_comments ? 'Open' : 'Closed' }}</span>
                </div>

                @if($post->allow_comments)
                    @auth
                        <form method="POST" action="{{ route('community.comments.store', $post) }}" class="mb-4">
                            @csrf
                            <label class="form-label" for="discussionBody">Start a discussion or add your answer</label>
                            <textarea name="body" id="discussionBody" class="form-control{{ $errors->has('body') ? ' is-invalid' : '' }}" rows="4" maxlength="2000" required placeholder="Write your question, answer, suggestion, or experience...">{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-success mt-2">Post comment</button>
                        </form>
                    @else
                        <p><a href="{{ route('login') }}">Login</a> to join this discussion.</p>
                    @endauth
                @else
                    <p class="text-muted mb-0">The author has disabled public discussion for this post.</p>
                @endif

                @forelse($post->discussionComments as $comment)
                    <div class="discussion-comment border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                            <strong>{{ $comment->user->name ?? $comment->user->full_name ?? 'Community member' }}</strong>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-2">{!! nl2br(e($comment->body)) !!}</p>

                        @if($post->allow_comments)
                            @auth
                                <details class="mb-3">
                                    <summary class="text-success fw-semibold">Reply</summary>
                                    <form method="POST" action="{{ route('community.comments.store', $post) }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <textarea name="body" class="form-control" rows="2" maxlength="2000" required placeholder="Reply to this comment..."></textarea>
                                        <button type="submit" class="btn btn-outline-success btn-sm mt-2">Post reply</button>
                                    </form>
                                </details>
                            @endauth
                        @endif

                        @foreach($comment->replies as $reply)
                            <div class="discussion-reply border-start ps-3 ms-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-1">
                                    <strong>{{ $reply->user->name ?? $reply->user->full_name ?? 'Community member' }}</strong>
                                    <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0">{!! nl2br(e($reply->body)) !!}</p>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="text-muted mb-0">No discussion yet. Be the first to comment.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection


@push('styles')
<style>
    .community-post-body {
        line-height: 1.8;
        overflow: auto;
    }
    .community-post-body > p,
    .community-post-body > h2,
    .community-post-body > h3,
    .community-post-body > h4,
    .community-post-body > blockquote,
    .community-post-body > ul,
    .community-post-body > ol {
        clear: none !important;
    }
    .community-post-body .image {
        clear: none !important;
        display: block;
        margin: 0.75rem auto;
        max-width: 100%;
    }
    .community-post-body .image img {
        display: block;
        height: auto;
        max-width: 100%;
    }
    .community-post-body .image img[style*="width"] {
        max-width: 100%;
    }
    .community-post-body .image.image-style-align-left,
    .community-post-body .image.image-style-side {
        clear: none !important;
        display: block !important;
        float: left !important;
        margin: 0.35rem 1.25rem 0.75rem 0 !important;
        max-width: 50%;
    }
    .community-post-body .image.image-style-align-right {
        clear: none !important;
        display: block !important;
        float: right !important;
        margin: 0.35rem 0 0.75rem 1.25rem !important;
        max-width: 50%;
    }
    .community-post-body .image.image-style-align-center,
    .community-post-body .image.image-style-block {
        clear: both;
        display: table;
        float: none;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
    }
    .community-post-body .image.image-style-inline,
    .community-post-body .image-inline {
        display: inline-block;
        float: none;
        margin: 0.15em 0.35em;
        max-width: 50%;
        vertical-align: top;
    }
    .discussion-comment { background: #fff; }
    .discussion-reply { background: #f8faf9; border-color: #badbcc !important; padding-bottom: .5rem; padding-top: .5rem; }
    @@media (max-width: 767.98px) {
        .community-post-body .image.image-style-align-left,
        .community-post-body .image.image-style-align-right,
        .community-post-body .image.image-style-side,
        .community-post-body .image.image-style-inline,
        .community-post-body .image-inline {
            display: block !important;
            float: none !important;
            margin: 1rem auto !important;
            max-width: 100% !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.js-community-reaction-form').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('[data-reaction-button]');
            const originalHtml = button.innerHTML;
            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to add reaction.');
                }

                document.querySelectorAll('[data-reaction-button] .reaction-count').forEach((countEl) => {
                    countEl.textContent = '0';
                });

                Object.entries(payload.counts || {}).forEach(([reaction, count]) => {
                    const countEl = document.querySelector(`[data-reaction-button="${reaction}"] .reaction-count`);
                    if (countEl) countEl.textContent = count;
                });

                if (payload.reaction) {
                    const reactionButton = document.querySelector(`[data-reaction-button="${payload.reaction}"]`);
                    if (reactionButton) {
                        reactionButton.classList.toggle('btn-success', Boolean(payload.active));
                        reactionButton.classList.toggle('btn-outline-success', !payload.active);
                    }
                }
            } catch (error) {
                alert(error.message || 'Unable to add reaction.');
                button.innerHTML = originalHtml;
            } finally {
                button.disabled = false;
            }
        });
    });
</script>
@endpush
