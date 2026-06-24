@if($post->isYouthCornerPost())
    <div class="chart-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Youth Corner post settings</h5>
                <p class="text-muted small mb-0">Privacy, sharing, mentorship, and engagement summary.</p>
            </div>
            @if($post->isPubliclyVisible())
                <a href="{{ route('community.show', $post) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                    View public page
                </a>
            @endif
        </div>

        <div class="row g-3 small mb-3">
            <div class="col-md-3">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Publish as</div>
                    <strong>{{ \App\Support\CommunityContentTaxonomy::youthCornerPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Visibility</div>
                    <strong>{{ $post->youthCornerVisibilityLabel() }}</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Content type</div>
                    <strong>{{ data_get($post->meta, 'youth_corner_content_type', '—') }}</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Reactions</div>
                    <strong>{{ $post->reactions?->count() ?? 0 }}</strong>
                </div>
            </div>
        </div>

        @if($post->requiresYouthCornerPrivateLink() && filled($post->youthCornerPrivateLinkUrl()))
            <div class="alert alert-info py-2 px-3 small mb-3">
                <strong>Private link:</strong>
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" value="{{ $post->youthCornerPrivateLinkUrl() }}" readonly id="youthCornerManagePrivateLink">
                    <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('youthCornerManagePrivateLink').value)">Copy</button>
                </div>
            </div>
        @endif

        @if(filled(data_get($post->meta, 'youth_corner_ask_community')))
            <div class="alert alert-primary py-2 px-3 small mb-3">
                <strong>Ask the community:</strong> {{ data_get($post->meta, 'youth_corner_ask_community') }}
            </div>
        @endif

        @php
            $mentorshipRequests = array_values(array_filter((array) data_get($post->meta, 'youth_corner_mentorship_requests', [])));
        @endphp
        @if($mentorshipRequests !== [])
            <div class="mb-3">
                <span class="text-muted small d-block mb-1">Mentorship requests</span>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($mentorshipRequests as $request)
                        <span class="badge bg-info-subtle text-info-emphasis border">{{ $request }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-0">
            @if($post->allow_comments)<span class="badge bg-success">Comments</span>@endif
            @if($post->allow_questions)<span class="badge bg-success">Questions</span>@endif
            @if($post->allow_feedback)<span class="badge bg-success">Peer discussion</span>@endif
            @if($post->allow_sharing)<span class="badge bg-success">Sharing</span>@endif
            @if($post->allowsPoll())<span class="badge bg-primary">Poll</span>@endif
        </div>
    </div>
@endif
