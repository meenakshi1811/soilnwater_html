@extends('backend.layouts.app')

@section('title', 'Manage Community Post')

@push('styles')
<style>
    .community-review-preview {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        min-height: 720px;
        width: 100%;
    }

    .community-review-meta .label {
        color: #64748b;
        font-size: .8rem;
        margin-bottom: .2rem;
        text-transform: uppercase;
    }

    .community-admin-action-group {
        border: 1px solid #dbe4ef;
        border-radius: 12px;
        padding: 0.9rem 1rem;
    }

    .community-admin-action-group + .community-admin-action-group {
        margin-top: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <p class="ems-kicker mb-1">Community moderation</p>
            <h2 class="admin-title mb-1">Manage Post</h2>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge {{ $post->statusBadgeClass() }}">{{ $post->statusLabel() }}</span>
                @foreach($post->adminPromotionLabels() as $label)
                    <span class="badge bg-light text-dark border">{{ $label }}</span>
                @endforeach
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.community-posts.all.index') }}" class="btn btn-outline-secondary">All Posts</a>
            @if($post->isPendingApproval())
                <a href="{{ route('admin.community-posts.index') }}" class="btn btn-outline-secondary">Approval Queue</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="community-admin-action-group">
                <h6 class="mb-2">Workflow actions</h6>
                <div class="d-flex flex-wrap gap-2">
                    @if(! $post->isArchived())
                        <button type="button" class="btn btn-success js-approve" data-slug="{{ $post->slug }}">Approve</button>
                        <button type="button" class="btn btn-outline-danger js-reject" data-slug="{{ $post->slug }}">Reject</button>
                    @endif
                    @if($post->status !== \App\Models\CommunityPost::STATUS_DRAFT && ! $post->isArchived())
                        <button type="button" class="btn btn-outline-secondary js-draft" data-slug="{{ $post->slug }}">Draft</button>
                    @endif
                    @if(! $post->isArchived())
                        <button type="button" class="btn btn-outline-dark js-archive" data-slug="{{ $post->slug }}">Archive</button>
                    @endif
                    @if($post->isArchived())
                        <button type="button" class="btn btn-success js-approve" data-slug="{{ $post->slug }}">Restore &amp; Publish</button>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="community-admin-action-group h-100">
                <h6 class="mb-2">Promotion flags</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="btn btn-sm {{ $post->is_featured ? 'btn-primary' : 'btn-outline-primary' }} js-feature"
                        data-slug="{{ $post->slug }}"
                        data-enabled="{{ $post->is_featured ? '1' : '0' }}"
                    >Feature</button>
                    <button
                        type="button"
                        class="btn btn-sm {{ $post->is_sponsored ? 'btn-info' : 'btn-outline-info' }} js-sponsor"
                        data-slug="{{ $post->slug }}"
                        data-enabled="{{ $post->is_sponsored ? '1' : '0' }}"
                    >Sponsor</button>
                    <button
                        type="button"
                        class="btn btn-sm {{ $post->is_highlighted ? 'btn-warning' : 'btn-outline-warning' }} js-highlight"
                        data-slug="{{ $post->slug }}"
                        data-enabled="{{ $post->is_highlighted ? '1' : '0' }}"
                    >Highlight</button>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="communityReviewTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab">Post details</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-pane" type="button" role="tab">Frontend preview</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="details-pane" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="chart-card p-3 p-lg-4 h-100">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <h3 class="mb-1">{{ $post->title }}</h3>
                                <div class="text-muted small">{{ $post->typeLabel() }} · {{ $post->category }}</div>
                            </div>
                        </div>

                        @if($post->excerpt)
                            <h5 class="mb-2">Excerpt</h5>
                            <p class="text-secondary">{{ $post->excerpt }}</p>
                        @endif

                        <h5 class="mb-2">Content</h5>
                        @if($post->usesBookLayout() && $post->bookPages() !== [])
                            @include('community.partials.book-reader', ['post' => $post])
                        @else
                            <div class="border rounded p-3 bg-white community-review-body">
                                {!! $post->body !!}
                            </div>
                        @endif

                        @if($post->featuredImageUrls() !== [])
                            <h5 class="mt-4 mb-2">Featured images</h5>
                            <div class="row g-2">
                                @foreach($post->featuredImageUrls() as $imageUrl)
                                    <div class="col-md-4">
                                        <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="img-fluid rounded border">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($post->hasVideo())
                            <h5 class="mt-4 mb-2">Video</h5>
                            @if($post->youtubeEmbedUrl())
                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                    <iframe src="{{ $post->youtubeEmbedUrl() }}" title="Video preview" allowfullscreen></iframe>
                                </div>
                            @elseif($post->videoFileUrl())
                                <video controls class="w-100 rounded border" preload="metadata">
                                    <source src="{{ $post->videoFileUrl() }}">
                                </video>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card p-3 p-lg-4 mb-4 community-review-meta">
                        <h5 class="mb-3">Submission info</h5>
                        <div class="mb-3">
                            <div class="label">Author</div>
                            <div>{{ $post->user?->full_name ?: ($post->user?->name ?? 'Unknown user') }}</div>
                            <div class="small text-muted">{{ $post->user?->email }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Submitted</div>
                            <div>{{ optional($post->submitted_at)->format('d M Y, h:i A') ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Published</div>
                            <div>{{ optional($post->published_at)->format('d M Y, h:i A') ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Location type</div>
                            <div>{{ $post->locationTypeLabel() }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Location</div>
                            <div>{{ $post->location ?: '—' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Publish as</div>
                            <div>{{ $post->publishAsLabel() }}</div>
                            @if($post->resolvedPublishAs() === \App\Models\CommunityPost::PUBLISH_AS_PEN_NAME && filled($post->pen_name))
                                <div class="small text-muted">Pen name: {{ $post->pen_name }}</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <div class="label">Sharing</div>
                            <div>{{ $post->allowsSharing() ? 'Enabled' : 'Disabled' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="label">Poll</div>
                            <div>{{ $post->allowsPoll() ? 'Enabled' : 'Disabled' }}</div>
                            @if($post->allowsPoll())
                                <div class="small text-muted">{{ $post->pollQuestion() }}</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <div class="label">Comments</div>
                            <div>{{ $post->allow_comments ? 'Enabled' : 'Disabled' }}</div>
                        </div>
                        @if(filled($post->review_note))
                            <div class="mb-3">
                                <div class="label">Review note</div>
                                <div>{{ $post->review_note }}</div>
                            </div>
                        @endif
                        @if(is_array($post->tags) && $post->tags !== [])
                            <div class="mb-3">
                                <div class="label">Tags</div>
                                <div>{{ implode(', ', $post->tags) }}</div>
                            </div>
                        @endif
                    </div>

                    @if(is_array($post->meta) && $post->meta !== [])
                        <div class="chart-card p-3 p-lg-4">
                            <h5 class="mb-3">Additional metadata</h5>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach($post->meta as $key => $value)
                                            @continue(is_array($value) || is_object($value))
                                            @continue(blank($value))
                                            <tr>
                                                <th class="text-muted">{{ \Illuminate\Support\Str::headline($key) }}</th>
                                                <td>{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="preview-pane" role="tabpanel">
            <div class="chart-card p-3">
                <p class="text-muted mb-3">This preview shows how the post will appear on the public community page when published.</p>
                <iframe
                    class="community-review-preview"
                    src="{{ route('admin.community-posts.preview', $post) }}"
                    title="Community post frontend preview"
                    loading="lazy"
                ></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.communityPostApprovalConfig = {
        approveUrl: @json(route('admin.community-posts.approve', $post)),
        rejectUrl: @json(route('admin.community-posts.reject', $post)),
        draftUrl: @json(route('admin.community-posts.draft', $post)),
        archiveUrl: @json(route('admin.community-posts.archive', $post)),
        featureUrl: @json(route('admin.community-posts.feature', $post)),
        sponsorUrl: @json(route('admin.community-posts.sponsor', $post)),
        highlightUrl: @json(route('admin.community-posts.highlight', $post)),
        redirectUrl: @json(route('admin.community-posts.all.index')),
    };
</script>
<script src="{{ asset('assets/js/admin-community-posts.js') }}?v={{ now()->timestamp }}"></script>
@endpush
