@php
    $metaLabels = \App\Support\CommunityPostFormFields::communityIssueDetailMetaOrder();
    $orderedMeta = collect($metaLabels)
        ->mapWithKeys(fn ($label, $key) => [$key => data_get($post->meta, $key)])
        ->filter(fn ($value) => filled($value) || is_bool($value));
    $pillKeys = ['community_issue_affected_groups', 'community_issue_support_requests', 'community_issue_poll_options'];
    $textareaKeys = ['community_issue_suggested_solution', 'community_issue_resolution_timeline'];
@endphp

@if($post->isCommunityIssuesPost() && ($orderedMeta->isNotEmpty() || $includeAdmin))
    <div class="about-box mt-4 business-meta-grid chart-card p-3 p-lg-4">
        <h4>{{ $heading ?? ($includeAdmin ? 'Saved Community Issues metadata' : 'Community issue details') }}</h4>

        @if($includeAdmin)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Publish as</span>
                        <span>{{ \App\Support\CommunityContentTaxonomy::communityIssuePublishAsOptions()[$post->resolvedPublishAs()] ?? $post->publishAsLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Visibility</span>
                        <span>{{ $post->communityIssueVisibilityLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Tags</span>
                        <span>{{ !empty($post->tags) ? implode(', ', $post->tags) : '—' }}</span>
                    </div>
                </div>
            </div>

            @if($post->requiresCommunityIssuePrivateLink() && $post->communityIssuePrivateLinkUrl())
                <div class="alert alert-info py-2 px-3 small mb-3">
                    <strong>Private link:</strong>
                    <a href="{{ $post->communityIssuePrivateLinkUrl() }}" target="_blank" rel="noopener">{{ $post->communityIssuePrivateLinkUrl() }}</a>
                </div>
            @endif

            @if($reportEngagement ?? null)
                <div class="row g-2 mb-3 small">
                    <div class="col-6 col-md-3"><strong>Supports:</strong> {{ number_format($reportEngagement['supports_count']) }}</div>
                    <div class="col-6 col-md-3"><strong>Verified:</strong> {{ number_format($reportEngagement['agreements_count']) }}</div>
                    <div class="col-6 col-md-3"><strong>Followers:</strong> {{ number_format($reportEngagement['follows_count']) }}</div>
                    <div class="col-6 col-md-3"><strong>Evidence:</strong> {{ number_format($reportEngagement['evidence_count']) }}</div>
                </div>
            @endif
        @endif

        @if($orderedMeta->isNotEmpty())
            <div class="row g-3">
                @foreach($orderedMeta as $key => $value)
                    <div class="col-md-6">
                        <div class="business-meta-item h-100">
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true) && is_array($value))
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($value as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @elseif(in_array($key, $textareaKeys, true))
                                <span>{!! nl2br(e((string) $value)) !!}</span>
                            @elseif(is_bool($value))
                                <span>{{ $value ? 'Yes' : 'No' }}</span>
                            @elseif($key === 'community_issue_visibility')
                                <span>{{ $post->communityIssueVisibilityLabel() }}</span>
                            @else
                                <span>{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($post->communityIssuePhotoEvidence() !== [] || $post->communityIssueDocuments() !== [])
            <div class="mt-4">
                <h5 class="h6 mb-3">Uploaded evidence files</h5>
                @if($post->communityIssuePhotoEvidence() !== [])
                    <div class="business-gallery-grid mb-3">
                        @foreach($post->communityIssuePhotoEvidence() as $photo)
                            <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                                <img src="{{ data_get($photo, 'url') }}" alt="Evidence" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($post->communityIssueDocuments() !== [])
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->communityIssueDocuments() as $document)
                            <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">{{ data_get($document, 'name', 'Document') }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
