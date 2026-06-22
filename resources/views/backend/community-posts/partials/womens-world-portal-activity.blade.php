@if($post->isWomensWorldPost())
    <div class="chart-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Women's World post settings</h5>
                <p class="text-muted small mb-0">Privacy, sharing, and engagement summary for this post.</p>
            </div>
            @if($post->isPubliclyVisible())
                <a href="{{ route('community.show', $post) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                    View public page
                </a>
            @endif
        </div>

        <div class="row g-3 small mb-3">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Publish as</div>
                    <strong>{{ \App\Support\CommunityContentTaxonomy::womensWorldPublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Visibility</div>
                    <strong>{{ $post->womensWorldVisibilityLabel() }}</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Reactions</div>
                    <strong>{{ $post->reactions?->count() ?? 0 }}</strong>
                </div>
            </div>
        </div>

        @if($post->requiresWomensWorldPrivateLink() && filled($post->womensWorldPrivateLinkUrl()))
            <div class="alert alert-info py-2 px-3 small mb-3">
                <strong>Private link:</strong>
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" value="{{ $post->womensWorldPrivateLinkUrl() }}" readonly id="womensWorldManagePrivateLink">
                    <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('womensWorldManagePrivateLink').value)">Copy</button>
                </div>
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-0">
            @if($post->allow_comments)<span class="badge bg-success">Comments</span>@endif
            @if($post->allow_questions)<span class="badge bg-success">Questions</span>@endif
            @if($post->allow_suggestions)<span class="badge bg-success">Suggestions</span>@endif
            @if($post->allow_sharing)<span class="badge bg-success">Sharing</span>@endif
            @if($post->allowsPoll())<span class="badge bg-primary">Poll</span>@endif
        </div>
    </div>
@endif
