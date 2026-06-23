@if($post->isSeniorCitizensForumPost())
    <div class="chart-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Senior Citizens Forum post settings</h5>
                <p class="text-muted small mb-0">Visibility, legacy, and engagement summary for this post.</p>
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
                    <div class="text-muted mb-1">Visibility</div>
                    <strong>{{ $post->seniorCitizensForumVisibilityLabel() }}</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Digital legacy</div>
                    <strong>{{ data_get($post->meta, 'senior_citizens_forum_preserve_digital_legacy') ? 'Enabled' : 'Off' }}</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Achievements</div>
                    <strong>{{ count((array) data_get($post->meta, 'senior_citizens_forum_achievements', [])) }}</strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted mb-1">Reactions</div>
                    <strong>{{ $post->reactions?->count() ?? 0 }}</strong>
                </div>
            </div>
        </div>

        @if($post->requiresSeniorCitizensForumPrivateLink() && filled($post->seniorCitizensForumPrivateLinkUrl()))
            <div class="alert alert-info py-2 px-3 small mb-3">
                <strong>Private link:</strong>
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" value="{{ $post->seniorCitizensForumPrivateLinkUrl() }}" readonly id="seniorCitizensForumManagePrivateLink">
                    <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('seniorCitizensForumManagePrivateLink').value)">Copy</button>
                </div>
            </div>
        @endif

        @php
            $intergenerational = array_values(array_filter((array) data_get($post->meta, 'senior_citizens_forum_intergenerational_connections', [])));
        @endphp
        @if($intergenerational !== [])
            <div class="mb-3">
                <div class="text-muted small mb-1">Intergenerational tags</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($intergenerational as $tag)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(filled(data_get($post->meta, 'senior_citizens_forum_ask_community')))
            <div class="alert alert-primary py-2 px-3 small mb-3">
                <strong>Community question:</strong> {{ data_get($post->meta, 'senior_citizens_forum_ask_community') }}
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-0">
            @if($post->allow_comments)<span class="badge bg-success">Comments</span>@endif
            @if($post->allow_questions)<span class="badge bg-success">Questions</span>@endif
            @if($post->allow_suggestions)<span class="badge bg-success">Suggestions</span>@endif
            @if($post->allow_sharing)<span class="badge bg-success">Sharing</span>@endif
        </div>
    </div>
@endif
