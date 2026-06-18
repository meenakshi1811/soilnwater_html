@php
    $participationSuggestions = $participationSuggestions ?? collect();
    $participationFeedback = $participationFeedback ?? collect();
    $communityParticipationEvidence = $communityParticipationEvidence ?? collect();
    $hideSectionHeader = $hideSectionHeader ?? false;
    $isAuthor = auth()->check() && auth()->id() === $post->user_id;
    $canParticipate = auth()->check() && $post->isPubliclyVisible() && ! $isAuthor;
@endphp

@if($post->isPubliclyVisible() && $post->allowsPublicParticipation())
    <section class="about-box mt-4 public-participation {{ $hideSectionHeader ? 'public-participation--embedded border-0 p-0 mt-0 bg-transparent shadow-none' : '' }}" id="public-participation">
        @unless($hideSectionHeader)
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h4 class="mb-1">{{ $post->content_type === 'news' ? 'Comments & Discussion' : 'Public Participation' }}</h4>
                <p class="text-muted mb-0">{{ $post->content_type === 'news' ? 'Share comments and suggestions on this news story.' : 'Share comments, suggestions, feedback, or additional evidence on this post.' }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($post->allow_comments)
                    <span class="badge bg-success">Comments open</span>
                @endif
                @if($post->allow_suggestions)
                    <span class="badge bg-success">Suggestions open</span>
                @endif
                @if($post->allow_feedback)
                    <span class="badge bg-success">Feedback open</span>
                @endif
                @if($post->allow_additional_evidence)
                    <span class="badge bg-success">Evidence open</span>
                @endif
            </div>
        </div>
        @endunless

        @if($post->allow_comments)
            <div class="public-participation__block mb-4" id="participation-comments">
                <h5 class="h6 mb-2">Comments</h5>
                <p class="text-muted small mb-3">Ask questions, share answers, and reply to other readers.</p>

                @if($canParticipate)
                    <form method="POST" action="{{ route('community.comments.store', $post) }}" class="mb-4">
                        @csrf
                        <label class="form-label" for="discussionBody">Add a comment</label>
                        <textarea name="body" id="discussionBody" class="form-control{{ $errors->has('body') ? ' is-invalid' : '' }}" rows="4" maxlength="2000" required placeholder="Write your question, answer, or experience...">{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-success mt-2">Post comment</button>
                    </form>
                @elseif($isAuthor)
                    <p class="text-muted small mb-3">Readers can comment here. You will be notified in the portal and by email.</p>
                @else
                    <p class="mb-3"><a href="{{ route('login') }}">Login</a> to add a comment.</p>
                @endif

                @forelse($post->discussionComments as $comment)
                    <div class="discussion-comment border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                            <strong>{{ $comment->user->name ?? $comment->user->full_name ?? 'Community member' }}</strong>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-2">{!! nl2br(e($comment->body)) !!}</p>

                        @if($canParticipate)
                            <details class="mb-3">
                                <summary class="text-success fw-semibold">Reply</summary>
                                <form method="POST" action="{{ route('community.comments.store', $post) }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea name="body" class="form-control" rows="2" maxlength="2000" required placeholder="Reply to this comment..."></textarea>
                                    <button type="submit" class="btn btn-outline-success btn-sm mt-2">Post reply</button>
                                </form>
                            </details>
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
                    <p class="text-muted mb-0">No comments yet.</p>
                @endforelse
            </div>
        @endif

        @if($post->allow_suggestions)
            <div class="public-participation__block mb-4" id="participation-suggestions">
                <h5 class="h6 mb-2">Suggestions</h5>
                <p class="text-muted small mb-3">Recommend actions, improvements, or next steps for the author.</p>

                @if($canParticipate)
                    <form
                        class="js-participation-text-form mb-3"
                        action="{{ route('community.participation.suggestion', $post) }}"
                        method="POST"
                    >
                        @csrf
                        <textarea name="body" class="form-control" rows="3" maxlength="2000" required placeholder="Your suggestion..."></textarea>
                        <button type="submit" class="btn btn-success btn-sm mt-2">Submit suggestion</button>
                    </form>
                @elseif($isAuthor)
                    <p class="text-muted small mb-3">Readers can submit suggestions here. You will be notified in the portal and by email.</p>
                @else
                    <p class="mb-3"><a href="{{ route('login') }}">Login</a> to submit a suggestion.</p>
                @endif

                <div class="d-flex flex-column gap-2" id="participationSuggestionsList">
                    @forelse($participationSuggestions as $entry)
                        <div class="border rounded-3 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <strong class="small">{{ $entry->user?->full_name ?: ($entry->user?->name ?? 'Community member') }}</strong>
                                <small class="text-muted">{{ $entry->created_at?->diffForHumans() }}</small>
                            </div>
                            <p class="small mb-0">{!! nl2br(e($entry->body)) !!}</p>
                        </div>
                    @empty
                        <p class="text-muted small mb-0 js-participation-empty">No suggestions yet.</p>
                    @endforelse
                </div>
            </div>
        @endif

        @if($post->allow_feedback)
            <div class="public-participation__block mb-4" id="participation-feedback">
                <h5 class="h6 mb-2">Feedback</h5>
                <p class="text-muted small mb-3">Share constructive feedback about this post or issue.</p>

                @if($canParticipate)
                    <form
                        class="js-participation-text-form mb-3"
                        action="{{ route('community.participation.feedback', $post) }}"
                        method="POST"
                    >
                        @csrf
                        <textarea name="body" class="form-control" rows="3" maxlength="2000" required placeholder="Your feedback..."></textarea>
                        <button type="submit" class="btn btn-success btn-sm mt-2">Submit feedback</button>
                    </form>
                @elseif($isAuthor)
                    <p class="text-muted small mb-3">Readers can submit feedback here. You will be notified in the portal and by email.</p>
                @else
                    <p class="mb-3"><a href="{{ route('login') }}">Login</a> to submit feedback.</p>
                @endif

                <div class="d-flex flex-column gap-2" id="participationFeedbackList">
                    @forelse($participationFeedback as $entry)
                        <div class="border rounded-3 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <strong class="small">{{ $entry->user?->full_name ?: ($entry->user?->name ?? 'Community member') }}</strong>
                                <small class="text-muted">{{ $entry->created_at?->diffForHumans() }}</small>
                            </div>
                            <p class="small mb-0">{!! nl2br(e($entry->body)) !!}</p>
                        </div>
                    @empty
                        <p class="text-muted small mb-0 js-participation-empty">No feedback yet.</p>
                    @endforelse
                </div>
            </div>
        @endif

        @if($post->allow_additional_evidence)
            <div class="public-participation__block" id="participation-evidence">
                <h5 class="h6 mb-2">Additional Evidence</h5>
                <p class="text-muted small mb-3">Upload photos or documents that support or clarify this post.</p>

                @if($canParticipate)
                    <form
                        class="js-participation-evidence-form mb-3"
                        action="{{ route('community.participation.evidence', $post) }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-7">
                                <label class="form-label small mb-1" for="participationEvidenceFiles">Photos / documents</label>
                                <input
                                    type="file"
                                    name="evidence_files[]"
                                    id="participationEvidenceFiles"
                                    class="form-control form-control-sm"
                                    accept="image/*,video/*,.pdf,.doc,.docx"
                                    multiple
                                    required
                                >
                                <small class="text-muted">Up to 3 files, 20 MB each.</small>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small mb-1" for="participationEvidenceNote">Short note (optional)</label>
                                <input
                                    type="text"
                                    name="note"
                                    id="participationEvidenceNote"
                                    class="form-control form-control-sm"
                                    maxlength="500"
                                    placeholder="What does this evidence show?"
                                >
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-upload me-1" aria-hidden="true"></i>Upload evidence
                                </button>
                            </div>
                        </div>
                    </form>
                @elseif($isAuthor)
                    <p class="text-muted small mb-3">Readers can upload evidence here. You will be notified in the portal and by email.</p>
                @else
                    <p class="mb-3"><a href="{{ route('login') }}">Login</a> to upload evidence.</p>
                @endif

                @if($communityParticipationEvidence->isNotEmpty())
                    <div class="row g-3" id="communityParticipationEvidenceList">
                        @foreach($communityParticipationEvidence as $evidence)
                            <div class="col-md-6" data-evidence-id="{{ $evidence->id }}">
                                <div class="border rounded-3 p-3 h-100 bg-white">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <strong class="small">{{ $evidence->user?->full_name ?: ($evidence->user?->name ?? 'Community member') }}</strong>
                                        <small class="text-muted">{{ $evidence->created_at?->diffForHumans() }}</small>
                                    </div>
                                    <a href="{{ $evidence->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary mb-2">
                                        <i class="fa-solid fa-paperclip me-1"></i>{{ $evidence->name }}
                                    </a>
                                    @if(filled($evidence->note))
                                        <p class="small text-muted mb-0">{{ $evidence->note }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div id="communityParticipationEvidenceSection" style="display:none;">
                        <div class="row g-3" id="communityParticipationEvidenceList"></div>
                    </div>
                    <p class="text-muted small mb-0 js-participation-empty" id="participationEvidenceEmpty">No additional evidence yet.</p>
                @endif
            </div>
        @endif
    </section>
@endif
