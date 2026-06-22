<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Community\CommunityEngagementController;
use App\Models\CommunityAuthorQuestion;
use App\Models\CommunityPost;
use App\Models\CommunityPostParticipation;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostPollVote;
use App\Models\CommunityPostStarRating;
use App\Models\CommunityPostReaction;
use App\Models\User;
use App\Services\CommunityPostParticipationNotificationService;
use App\Services\CommunityReportEngagementNotificationService;
use App\Services\CommunityStoryAchievementService;
use App\Services\CommunityStoryEngagementNotificationService;
use App\Services\CommunityEngagementNotificationService;
use App\Services\CommunityWomensWorldEngagementNotificationService;
use App\Services\CommunityArticleScoreService;
use App\Services\CommunityReportTrustScoreService;
use App\Services\PortalNotificationService;
use App\Support\CommunityContentTaxonomy;
use App\Support\CommunityPostAuditLogger;
use App\Support\CommunityPostFileUploader;
use App\Support\CommunityPostFormFields;
use App\Support\UserFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CommunityPostController extends Controller
{
    private const MAX_FEATURED_IMAGES = 5;

    private const MAX_STORY_GALLERY = 10;

    private const MAX_LIFE_TIMELINE = 30;

    private const MAX_AUTOBIOGRAPHY_ACHIEVEMENTS = 15;

    private const MAX_AUTOBIOGRAPHY_DOCUMENTS = 10;

    private const MAX_AWARENESS_INFOGRAPHICS = 10;

    private const MAX_AWARENESS_DOCUMENTS = 6;

    private const MAX_BUSINESS_DOCUMENTS = 6;

    private const MAX_BUSINESS_GALLERY = 10;

    private const MAX_WOMENS_WORLD_GALLERY = 10;

    private const MAX_STORY_AUDIO_KB = 20480;

    private const MAX_TAGS = 10;

    private const MAX_VIDEO_FILE_KB = 51200;

    private const MAX_CHILDRENS_CORNER_PROJECT_FILES = 6;

    private const MAX_CHILDRENS_CORNER_QUIZ_QUESTIONS = 20;

    private const MAX_CHILDRENS_CORNER_GALLERY = 10;

    public function index(Request $request): View|JsonResponse
    {
        $posts = $this->paginateCommunityPosts($request);

        if ($request->ajax()) {
            return $this->communityPostsAjaxResponse($posts);
        }

        return view('community.index', [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::formTypes(),
            'activeType' => $request->string('type')->toString(),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ]);
    }

    public function author(Request $request, string $uniqueName): View|JsonResponse
    {
        $author = $this->resolveAuthor($uniqueName);
        $posts = $this->paginateCommunityPosts($request, $author);

        if ($request->ajax()) {
            return $this->communityPostsAjaxResponse($posts);
        }

        return view('community.index', [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::formTypes(),
            'activeType' => $request->string('type')->toString(),
            'activeAuthor' => $author,
            'answeredAuthorQuestions' => $this->answeredQuestionsForAuthor($author),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ]);
    }

    public function show(Request $request, CommunityPost $post): View
    {
        $viewer = auth()->user();
        $canManagePreview = $viewer !== null && ($viewer->id === $post->user_id || $viewer->isAdmin());
        $privateLinkAccess = $post->allowsWomensWorldPrivateLinkAccess($request->query('access'));

        abort_unless(
            $post->isPubliclyVisible() || $canManagePreview,
            404
        );

        if ($post->isPubliclyVisible() && ! $privateLinkAccess && ! $post->isVisibleInCommunityTo($viewer) && ! $canManagePreview) {
            return view('community.privacy-gate', [
                'post' => $post,
                'types' => CommunityContentTaxonomy::formTypes(),
            ]);
        }

        $post->load([
            'user',
            'reactions',
            'pollVotes',
            'starRatings',
            'discussionComments.user',
            'discussionComments.replies.user',
        ])->loadCount(['starRatings', 'awarenessSupports', 'awarenessPledges', 'awarenessVolunteers', 'businessQueries']);

        if ($post->isPubliclyVisible()) {
            $this->recordPostView($request, $post);
        }

        return view('community.show', array_merge([
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'answeredAuthorQuestions' => $post->user_id
                ? $this->answeredQuestionsForPost($post)
                : collect(),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ], $this->participationViewData($post)));
    }

    public function authorShow(Request $request, CommunityPost $post): View
    {
        $this->authorizeOwner($request, $post);

        $post->load([
            'user',
            'discussionComments.user',
            'discussionComments.replies.user',
            'starRatings',
        ])->loadCount([
            'starRatings',
            'awarenessSupports',
            'awarenessPledges',
            'awarenessVolunteers',
            'businessQueries',
        ]);

        $participation = $this->participationViewData($post, limit: 50);

        return view('backend.community-posts.show', array_merge([
            'post' => $post,
            'engagementSummary' => $post->engagementSummary(),
            'pendingAuthorQuestions' => $post->authorQuestions()
                ->with(['asker:id,name,full_name,email'])
                ->whereNull('answered_at')
                ->latest()
                ->get(),
            'answeredAuthorQuestions' => $post->authorQuestions()
                ->with(['asker:id,name,full_name'])
                ->whereNotNull('answered_at')
                ->latest()
                ->limit(20)
                ->get(),
        ], $participation));
    }

    /**
     * @return array<string, mixed>
     */
    public function participationViewData(CommunityPost $post, int $limit = 20): array
    {
        return [
            'reportEngagement' => $post->isReportContent()
                ? CommunityReportEngagementNotificationService::stateForPost($post, auth()->id())
                : null,
            'awarenessEngagement' => $post->isAwarenessPost()
                ? \App\Services\CommunityAwarenessEngagementService::stateForPost($post, auth()->id())
                : null,
            'awarenessPledgeCounts' => $post->isAwarenessPost()
                ? \App\Services\CommunityAwarenessEngagementService::pledgeCounts($post)
                : [],
            'awarenessEngagementActivity' => $post->isAwarenessPost()
                ? \App\Services\CommunityAwarenessEngagementService::activityForPost($post)
                : null,
            'businessEngagement' => $post->isBusinessPost()
                ? \App\Services\CommunityBusinessEngagementService::stateForPost($post, auth()->id())
                : null,
            'businessEngagementActivity' => $post->isBusinessPost()
                ? \App\Services\CommunityBusinessEngagementService::activityForPost($post)
                : null,
            'communityParticipationEvidence' => $post->allow_additional_evidence
                ? CommunityReportEngagementNotificationService::recentEvidence($post, $limit)
                : collect(),
            'participationSuggestions' => $post->allow_suggestions
                ? $post->participations()->with('user:id,name,full_name')->where('type', CommunityPostParticipation::TYPE_SUGGESTION)->latest()->limit($limit)->get()
                : collect(),
            'participationFeedback' => $post->allow_feedback
                ? $post->participations()->with('user:id,name,full_name')->where('type', CommunityPostParticipation::TYPE_FEEDBACK)->latest()->limit($limit)->get()
                : collect(),
        ];
    }

    public function react(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        $this->ensureCommunityAudienceAccess($post, $request);

        $data = $request->validate([
            'reaction' => ['required', Rule::in($post->allowedReactionLabels())],
        ]);

        $reaction = CommunityPostReaction::query()->where([
            'community_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'reaction' => $data['reaction'],
        ])->first();

        if ($reaction) {
            $reaction->delete();
            $message = 'Reaction removed.';
            $active = false;
        } else {
            CommunityPostReaction::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
                'reaction' => $data['reaction'],
            ]);
            $message = 'Reaction added.';
            $active = true;
        }

        $post = $this->syncReportTrustScore($post->fresh());

        if ($post->content_type === 'stories') {
            $post = CommunityStoryAchievementService::recalculate($post->fresh());

            if ($active && $data['reaction'] === 'Inspiring') {
                CommunityStoryEngagementNotificationService::notifyAuthorOfInspiringReaction(
                    $post,
                    $request->user()
                );
            }
        }

        if ($post->content_type === 'poetry' && $active && $data['reaction'] === 'Inspiring') {
            CommunityStoryEngagementNotificationService::notifyAuthorOfInspiringReaction(
                $post,
                $request->user()
            );
        }

        if ($post->content_type === 'autobiography' && $active && $data['reaction'] === 'Inspiring') {
            CommunityStoryEngagementNotificationService::notifyAuthorOfInspiringReaction(
                $post,
                $request->user()
            );
        }

        if ($post->isChildrensCornerPost() && $active && $data['reaction'] === 'Inspiring') {
            CommunityStoryEngagementNotificationService::notifyAuthorOfInspiringReaction(
                $post,
                $request->user()
            );
        }

        if ($post->isWomensWorldPost() && $active) {
            CommunityWomensWorldEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'reaction' => $data['reaction'],
                'active' => $active,
                'counts' => $post->reactions()
                    ->selectRaw('reaction, count(*) as total')
                    ->groupBy('reaction')
                    ->pluck('total', 'reaction'),
                'report_trust_score' => $post->isReportContent() ? $post->reportTrustScore() : null,
            ]);
        }

        return back()->with('success', $message);
    }

    public function rateStory(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        $this->ensureCommunityAudienceAccess($post, $request);
        abort_unless(CommunityPost::supportsStarRating($post->content_type), 404);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        CommunityPostStarRating::query()->updateOrCreate(
            [
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $data['rating'],
            ]
        );

        CommunityArticleScoreService::recalculate($post->fresh());

        if (CommunityPost::supportsStarRating($post->content_type)) {
            CommunityStoryEngagementNotificationService::notifyAuthorOfRating(
                $post,
                $request->user(),
                (int) $data['rating']
            );
        }

        $post = $post->fresh()->loadCount('starRatings');
        $message = match ($post->content_type) {
            'poetry' => 'Poetry rating saved.',
            'autobiography' => 'Autobiography rating saved.',
            default => 'Story rating saved.',
        };

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'rating' => (int) $data['rating'],
                'average_rating' => $post->averageStarRating(),
                'ratings_count' => (int) $post->star_ratings_count,
                'achievement_badges' => $post->storyAchievementBadges(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function votePoll(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        $this->ensureCommunityAudienceAccess($post, $request);
        abort_unless($post->allowsPoll(), 403, 'Polls are disabled for this post.');

        $data = $request->validate([
            'option' => ['required', Rule::in(array_keys($post->pollOptionsForDisplay()))],
        ]);

        CommunityPostPollVote::query()->updateOrCreate(
            [
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ],
            [
                'option' => $data['option'],
            ]
        );

        $message = 'Poll vote saved.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'option' => $data['option'],
                'counts' => $post->pollCounts(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function comment(Request $request, CommunityPost $post): RedirectResponse
    {
        $this->ensureCommunityAudienceAccess($post, $request);
        abort_unless($post->allow_comments, 403, 'Discussions are disabled for this post.');

        $data = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:2000'],
            'parent_id' => [
                'nullable',
                Rule::exists('community_post_comments', 'id')->where(fn ($query) => $query
                    ->where('community_post_id', $post->id)
                    ->whereNull('parent_id')),
            ],
        ]);

        CommunityPostComment::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
            'is_approved' => ! $post->commentsModerated(),
        ]);

        if ($post->commentsModerated()) {
            CommunityPostParticipationNotificationService::notifyAuthorOfPendingComment(
                $post,
                $request->user(),
                $data['body'],
                filled($data['parent_id'] ?? null)
            );

            return back()->with('success', 'Your comment was submitted and is awaiting approval from the author.');
        }

        CommunityPostParticipationNotificationService::notifyAuthorOfComment(
            $post,
            $request->user(),
            $data['body'],
            filled($data['parent_id'] ?? null)
        );

        $this->syncReportTrustScore($post->fresh());

        return back()->with('success', filled($data['parent_id'] ?? null) ? 'Reply added to the discussion.' : 'Comment added to the discussion.');
    }

    public function approveComment(Request $request, CommunityPost $post, CommunityPostComment $comment): RedirectResponse
    {
        $this->authorizeOwner($request, $post);

        abort_unless($comment->community_post_id === $post->id, 404);

        $comment->forceFill(['is_approved' => true])->save();

        $comment->loadMissing('user');

        if ($comment->user) {
            CommunityPostParticipationNotificationService::notifyParticipantOfApprovedComment(
                $post,
                $comment,
                $comment->user
            );
        }

        return back()->with('success', 'Comment approved and now visible publicly.');
    }

    public function followAuthor(Request $request, User $author): JsonResponse|RedirectResponse
    {
        abort_if($request->user()->id === $author->id, 422, 'You cannot follow yourself.');

        $existing = \Illuminate\Support\Facades\DB::table('community_author_follows')
            ->where('user_id', $request->user()->id)
            ->where('author_id', $author->id)
            ->first();

        if ($existing) {
            \Illuminate\Support\Facades\DB::table('community_author_follows')
                ->where('id', $existing->id)
                ->delete();
            $following = false;
            $message = 'Author unfollowed successfully.';
        } else {
            \Illuminate\Support\Facades\DB::table('community_author_follows')->updateOrInsert(
                ['user_id' => $request->user()->id, 'author_id' => $author->id],
                ['updated_at' => now(), 'created_at' => now()]
            );
            $following = true;
            $message = 'Author followed successfully.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'following' => $following,
            ]);
        }

        return back()->with('success', $message);
    }

    public function myPosts(Request $request): View
    {
        return view('backend.community-posts.index');
    }

    public function myPostsData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = CommunityPost::query()
            ->where('user_id', $request->user()->id)
            ->withCount([
                'comments',
                'suggestions',
                'authorQuestions as questions_count',
                'authorQuestions as pending_questions_count' => fn ($builder) => $builder->whereNull('answered_at'),
            ])
            ->select([
                'id',
                'slug',
                'content_type',
                'category',
                'writing_purpose',
                'meta',
                'title',
                'status',
                'published_at',
                'submitted_at',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('type_label', fn (CommunityPost $post): string => e($post->typeLabel()))
            ->addColumn('writing_purpose_display', fn (CommunityPost $post): string => e($post->writingPurposeLabel() ?? '—'))
            ->addColumn('category_display', fn (CommunityPost $post): string => e($post->listingCategoryLabel()))
            ->addColumn('trust_score_display', fn (CommunityPost $post): string => CommunityReportTrustScoreService::badgeHtml($post))
            ->addColumn('status_badge', fn (CommunityPost $post): string => '<span class="badge '.$post->statusBadgeClass().'">'.e($post->statusLabel()).'</span>')
            ->addColumn('published_display', function (CommunityPost $post): string {
                if ($post->published_at) {
                    return $post->published_at->timezone(config('app.timezone'))->format('d M Y, h:i A');
                }

                return $post->isPendingApproval() ? 'Awaiting approval' : '—';
            })
            ->addColumn('actions', function (CommunityPost $post): string {
                $engagementTotal = ($post->comments_count ?? 0) + ($post->suggestions_count ?? 0) + ($post->pending_questions_count ?? 0);
                $engagementBadge = $engagementTotal > 0
                    ? ' <span class="badge bg-info text-dark" title="Comments / suggestions / pending questions">'.($post->comments_count ?? 0).'/'.($post->suggestions_count ?? 0).'/'.($post->pending_questions_count ?? 0).'</span>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end align-items-center">'
                    .$engagementBadge
                    .'<a href="'.route('community.posts.manage', $post).'" class="btn btn-sm btn-outline-success" title="Manage post"><i class="fa-solid fa-comments"></i></a>'
                    .'<a href="'.route('community.show', $post).'" class="btn btn-sm btn-outline-primary" title="Public page" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i></a>'
                    .'<a href="'.route('community.posts.edit', $post).'" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fa-solid fa-pen"></i></a>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-post" data-slug="'.e($post->slug).'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['status_badge', 'trust_score_display', 'actions'])
            ->make(true);
    }

    public function destroyPost(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        $this->authorizeOwner($request, $post);

        foreach ($post->featuredImages() as $imagePath) {
            CommunityPostFileUploader::deleteIfExists($imagePath);
        }

        foreach ((array) data_get($post->meta, 'issue_attachments', []) as $attachment) {
            CommunityPostFileUploader::deleteIfExists(data_get($attachment, 'path'));
        }

        foreach ((array) data_get($post->meta, 'news_documents', []) as $document) {
            CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
        }

        foreach ((array) data_get($post->meta, 'story_gallery', []) as $image) {
            CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
        }

        foreach ((array) data_get($post->meta, 'awareness_infographics', []) as $infographic) {
            CommunityPostFileUploader::deleteIfExists(data_get($infographic, 'path'));
        }

        foreach ((array) data_get($post->meta, 'awareness_documents', []) as $document) {
            CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
        }

        foreach ((array) data_get($post->meta, 'business_documents', []) as $document) {
            CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
        }

        foreach ((array) data_get($post->meta, 'business_gallery', []) as $image) {
            CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
        }

        CommunityPostFileUploader::deleteIfExists(data_get($post->meta, 'childrens_corner_art.path'));

        foreach ((array) data_get($post->meta, 'childrens_corner_project_files', []) as $file) {
            CommunityPostFileUploader::deleteIfExists(data_get($file, 'path'));
        }

        foreach ((array) data_get($post->meta, 'childrens_corner_gallery', []) as $image) {
            CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
        }

        CommunityPostFileUploader::deleteIfExists(data_get($post->meta, 'childrens_corner_certificate.path'));

        $this->deleteVideoFile(data_get($post->meta, 'childrens_corner_video'));
        $this->deleteStoryAudioFile(data_get($post->meta, 'childrens_corner_audio'));

        $this->deleteStoryAudioFile(data_get($post->meta, 'story_audio'));
        $this->deleteStoryAudioFile(data_get($post->meta, 'poetry_audio'));
        $this->deleteStoryAudioFile(data_get($post->meta, 'womens_world_audio'));

        $this->deleteVideoFile($post->videoData());
        $post->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Community post deleted successfully.']);
        }

        return redirect()->route('community.posts.index')->with('success', 'Community post deleted successfully.');
    }

    public function updateAuthorUrl(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'author_slug' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('users', 'author_slug')->ignore($user->id),
            ],
            'author_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_author_image' => ['nullable', 'boolean'],
        ], [
            'author_slug.regex' => 'Use lowercase letters, numbers, and single hyphens only.',
        ]);

        if ($request->boolean('remove_author_image')) {
            UserFileUploader::deleteIfExists($user->author_image);
            $user->author_image = null;
        } elseif ($request->hasFile('author_image')) {
            UserFileUploader::deleteIfExists($user->author_image);
            $user->author_image = UserFileUploader::storeImage($request->file('author_image'), 'author-profiles');
        }

        $user->forceFill(['author_slug' => Str::slug($data['author_slug'])])->save();

        return back()->with('success', 'Author profile updated successfully.');
    }

    public function create(): View
    {
        return view('backend.community-posts.form', [
            'post' => new CommunityPost(['status' => CommunityPost::STATUS_PUBLISHED, 'allow_comments' => true, 'allow_questions' => true, 'allow_sharing' => true, 'allow_poll' => false]),
            'types' => CommunityContentTaxonomy::formTypes(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request);
        CommunityPostAuditLogger::applySubmissionAcceptance($request, $data, isCreate: true);
        CommunityPostAuditLogger::stripAcceptanceFields($data);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['tags'] = $this->normalizeTags($data['tags'] ?? null);
        $data['meta'] = $this->metaPayload($request);
        $data['meta'] = $this->applyWomensWorldPrivacyMeta($data['meta'], $request);
        $this->mergeBookPagesIntoMeta($request, $data);

        if ($request->hasFile('issue_attachments')) {
            $data['meta']['issue_attachments'] = $this->storeIssueAttachments($request);
        }
        if ($request->hasFile('news_documents')) {
            $documents = $this->storeNewsDocuments($request);
            if ($documents !== []) {
                $data['meta']['news_documents'] = $documents;
            }
        }
        $storyGallery = $this->resolveStoryGallery($request);
        if ($storyGallery !== null) {
            $data['meta']['story_gallery'] = $storyGallery;
        }
        $storyAudio = $this->resolveStoryAudio($request);
        if ($storyAudio !== null || ($request->input('content_type') === 'stories' && $request->input('story_audio_source_type') === 'none')) {
            if ($storyAudio !== null) {
                $data['meta']['story_audio'] = $storyAudio;
            } else {
                unset($data['meta']['story_audio']);
            }
        }
        $poetryAudio = $this->resolvePoetryAudio($request);
        if ($poetryAudio !== null || ($request->input('content_type') === 'poetry' && $request->input('poetry_audio_source_type') === 'none')) {
            if ($poetryAudio !== null) {
                $data['meta']['poetry_audio'] = $poetryAudio;
            } else {
                unset($data['meta']['poetry_audio']);
            }
        }
        $lifeTimeline = $this->resolveLifeTimeline($request);
        if ($lifeTimeline !== null) {
            $data['meta']['life_timeline'] = $lifeTimeline;
        }
        $autobiographyAudio = $this->resolveAutobiographyAudio($request);
        if ($autobiographyAudio !== null || (CommunityPost::usesAutobiographyFlow($request->input('content_type')) && $request->input('autobiography_audio_source_type') === 'none')) {
            if ($autobiographyAudio !== null) {
                $data['meta']['autobiography_audio'] = $autobiographyAudio;
            } else {
                unset($data['meta']['autobiography_audio']);
            }
        }
        $autobiographyAchievements = $this->resolveAutobiographyAchievements($request);
        if ($autobiographyAchievements !== null) {
            $data['meta']['autobiography_achievements'] = $autobiographyAchievements;
        }
        $autobiographyDocuments = $this->resolveAutobiographyDocuments($request);
        if ($autobiographyDocuments !== null) {
            $data['meta']['autobiography_documents'] = $autobiographyDocuments;
        }
        $childrensCornerArt = $this->resolveChildrensCornerArt($request);
        if ($childrensCornerArt !== null) {
            $data['meta']['childrens_corner_art'] = $childrensCornerArt;
        }
        $childrensCornerProjectFiles = $this->resolveChildrensCornerProjectFiles($request);
        if ($childrensCornerProjectFiles !== null) {
            $data['meta']['childrens_corner_project_files'] = $childrensCornerProjectFiles;
        }
        $childrensCornerQuiz = $this->resolveChildrensCornerQuiz($request);
        if ($childrensCornerQuiz !== null) {
            $data['meta']['childrens_corner_quiz'] = $childrensCornerQuiz;
        }
        $childrensCornerGallery = $this->resolveChildrensCornerGallery($request);
        if ($childrensCornerGallery !== null) {
            $data['meta']['childrens_corner_gallery'] = $childrensCornerGallery;
        }
        $childrensCornerVideo = $this->resolveChildrensCornerVideo($request);
        if ($childrensCornerVideo !== null) {
            $data['meta']['childrens_corner_video'] = $childrensCornerVideo;
        }
        $childrensCornerAudio = $this->resolveChildrensCornerAudio($request);
        if ($childrensCornerAudio !== null) {
            $data['meta']['childrens_corner_audio'] = $childrensCornerAudio;
        }
        $childrensCornerCertificate = $this->resolveChildrensCornerCertificate($request);
        if ($childrensCornerCertificate !== null) {
            $data['meta']['childrens_corner_certificate'] = $childrensCornerCertificate;
        }
        $awarenessInfographics = $this->resolveAwarenessInfographics($request);
        if ($awarenessInfographics !== null) {
            $data['meta']['awareness_infographics'] = $awarenessInfographics;
        }
        $awarenessDocuments = $this->resolveAwarenessDocuments($request);
        if ($awarenessDocuments !== null) {
            $data['meta']['awareness_documents'] = $awarenessDocuments;
        }
        $businessDocuments = $this->resolveBusinessDocuments($request);
        if ($businessDocuments !== null) {
            $data['meta']['business_documents'] = $businessDocuments;
        }
        $businessGallery = $this->resolveBusinessGallery($request);
        if ($businessGallery !== null) {
            $data['meta']['business_gallery'] = $businessGallery;
        }
        $womensWorldGallery = $this->resolveWomensWorldGallery($request);
        if ($womensWorldGallery !== null) {
            $data['meta']['womens_world_gallery'] = $womensWorldGallery;
        }
        $womensWorldAudio = $this->resolveWomensWorldAudio($request);
        if ($womensWorldAudio !== null || ($request->input('content_type') === 'womens-world' && $request->input('womens_world_audio_source_type') === 'none')) {
            if ($womensWorldAudio !== null) {
                $data['meta']['womens_world_audio'] = $womensWorldAudio;
            } else {
                unset($data['meta']['womens_world_audio']);
            }
        }
        if (CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            $data['allow_comments'] = $this->shouldAllowComments($request);
        }
        $data = $this->applyPoetryRegionalLocation($data);
        $data['allow_comments'] = $this->shouldAllowComments($request);
        $data['allow_questions'] = $this->shouldAllowQuestions($request);
        $data['allow_suggestions'] = $this->shouldAllowSuggestions($request);
        $data['allow_feedback'] = $this->shouldAllowFeedback($request);
        $data['allow_additional_evidence'] = $this->shouldAllowAdditionalEvidence($request);
        $data['allow_sharing'] = $this->shouldAllowSharing($request);
        $data['allow_poll'] = $this->shouldAllowPoll($request, $data['content_type'] ?? $post?->content_type);
        $data = array_merge($data, $this->resolvePublicationState($request, $post = null));
        [$data['featured_images'], $data['featured_image_path']] = $this->resolveFeaturedImages($request);
        $data['video'] = CommunityPost::usesChildrensCornerFlow($request->input('content_type'))
            ? null
            : $this->resolveVideo($request);

        $post = CommunityPost::create($data);

        $this->syncReportTrustScore($post);

        CommunityPostAuditLogger::logCreated($post, $request);

        if ($post->isPendingApproval()) {
            $this->notifyAdminsOfPendingPost($post);
        } elseif ($post->isWomensWorldPost() && $post->isPubliclyVisible()) {
            CommunityWomensWorldEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
            CommunityEngagementNotificationService::notifySubscribersOfPublishedPost($post->fresh());
        } elseif (in_array($post->content_type, ['poetry', 'biography', 'autobiography'], true) && $post->isPubliclyVisible()) {
            CommunityStoryEngagementNotificationService::notifyAuthorOfPublishedWithoutAudio($post->fresh());
        }

        $message = $post->isPendingApproval()
            ? 'Community post submitted for admin approval.'
            : 'Community post created successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('community.posts.show', $post),
            ]);
        }

        return redirect()->route('community.posts.show', $post)->with('success', $message);
    }

    public function edit(Request $request, CommunityPost $post): View
    {
        $this->authorizeOwner($request, $post);

        return view('backend.community-posts.form', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        $this->authorizeOwner($request, $post);

        $data = $this->validated($request, $post);
        CommunityPostAuditLogger::applySubmissionAcceptance($request, $data, isCreate: false);
        CommunityPostAuditLogger::stripAcceptanceFields($data);
        $data['tags'] = $this->normalizeTags($data['tags'] ?? null);
        $data['meta'] = $this->metaPayload($request);
        $data['meta'] = $this->applyWomensWorldPrivacyMeta($data['meta'], $request, $post);
        $this->mergeBookPagesIntoMeta($request, $data);

        if ($request->hasFile('issue_attachments')) {
            $data['meta']['issue_attachments'] = array_values(array_merge(
                (array) data_get($post->meta, 'issue_attachments', []),
                $this->storeIssueAttachments($request)
            ));
        } elseif (data_get($post->meta, 'issue_attachments')) {
            $data['meta']['issue_attachments'] = data_get($post->meta, 'issue_attachments');
        }

        $newsDocuments = $this->resolveNewsDocuments($request, $post);
        if ($newsDocuments !== null) {
            $data['meta']['news_documents'] = $newsDocuments;
        } elseif (data_get($post->meta, 'news_documents')) {
            unset($data['meta']['news_documents']);
        }

        $storyGallery = $this->resolveStoryGallery($request, $post);
        if ($storyGallery !== null) {
            $data['meta']['story_gallery'] = $storyGallery;
        } elseif (data_get($post->meta, 'story_gallery')) {
            unset($data['meta']['story_gallery']);
        }

        $storyAudio = $this->resolveStoryAudio($request, $post);
        if ($storyAudio !== null) {
            $data['meta']['story_audio'] = $storyAudio;
        } elseif ($request->input('content_type') === 'stories' && $request->input('story_audio_source_type') === 'none') {
            unset($data['meta']['story_audio']);
        } elseif ($request->boolean('remove_story_audio')) {
            unset($data['meta']['story_audio']);
        }

        $poetryAudio = $this->resolvePoetryAudio($request, $post);
        if ($poetryAudio !== null) {
            $data['meta']['poetry_audio'] = $poetryAudio;
        } elseif ($request->input('content_type') === 'poetry' && $request->input('poetry_audio_source_type') === 'none') {
            unset($data['meta']['poetry_audio']);
        } elseif ($request->boolean('remove_poetry_audio')) {
            unset($data['meta']['poetry_audio']);
        }

        $lifeTimeline = $this->resolveLifeTimeline($request, $post);
        if ($lifeTimeline !== null) {
            $data['meta']['life_timeline'] = $lifeTimeline;
        } elseif (CommunityPost::usesAutobiographyFlow($request->input('content_type')) && data_get($post->meta, 'life_timeline')) {
            unset($data['meta']['life_timeline']);
        }

        $autobiographyAudio = $this->resolveAutobiographyAudio($request, $post);
        if ($autobiographyAudio !== null) {
            $data['meta']['autobiography_audio'] = $autobiographyAudio;
        } elseif (CommunityPost::usesAutobiographyFlow($request->input('content_type')) && $request->input('autobiography_audio_source_type') === 'none') {
            unset($data['meta']['autobiography_audio']);
        } elseif ($request->boolean('remove_autobiography_audio')) {
            unset($data['meta']['autobiography_audio']);
        }

        $autobiographyAchievements = $this->resolveAutobiographyAchievements($request, $post);
        if ($autobiographyAchievements !== null) {
            $data['meta']['autobiography_achievements'] = $autobiographyAchievements;
        } elseif (CommunityPost::usesAutobiographyFlow($request->input('content_type')) && data_get($post->meta, 'autobiography_achievements')) {
            unset($data['meta']['autobiography_achievements']);
        }

        $autobiographyDocuments = $this->resolveAutobiographyDocuments($request, $post);
        if ($autobiographyDocuments !== null) {
            $data['meta']['autobiography_documents'] = $autobiographyDocuments;
        } elseif (CommunityPost::usesAutobiographyFlow($request->input('content_type')) && data_get($post->meta, 'autobiography_documents')) {
            unset($data['meta']['autobiography_documents']);
        }

        $childrensCornerArt = $this->resolveChildrensCornerArt($request, $post);
        if ($childrensCornerArt !== null) {
            $data['meta']['childrens_corner_art'] = $childrensCornerArt;
        } elseif (CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            unset($data['meta']['childrens_corner_art']);
        }

        $childrensCornerProjectFiles = $this->resolveChildrensCornerProjectFiles($request, $post);
        if ($childrensCornerProjectFiles !== null) {
            $data['meta']['childrens_corner_project_files'] = $childrensCornerProjectFiles;
        } elseif (CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            unset($data['meta']['childrens_corner_project_files']);
        }

        $childrensCornerQuiz = $this->resolveChildrensCornerQuiz($request);
        if ($childrensCornerQuiz !== null) {
            $data['meta']['childrens_corner_quiz'] = $childrensCornerQuiz;
        } elseif (CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            unset($data['meta']['childrens_corner_quiz']);
        }

        $childrensCornerGallery = $this->resolveChildrensCornerGallery($request, $post);
        if ($childrensCornerGallery !== null) {
            $data['meta']['childrens_corner_gallery'] = $childrensCornerGallery;
        } elseif (CommunityPost::usesChildrensCornerFlow($request->input('content_type')) && data_get($post->meta, 'childrens_corner_gallery')) {
            unset($data['meta']['childrens_corner_gallery']);
        }

        $childrensCornerVideo = $this->resolveChildrensCornerVideo($request, $post);
        if ($childrensCornerVideo !== null) {
            $data['meta']['childrens_corner_video'] = $childrensCornerVideo;
        } elseif (CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            unset($data['meta']['childrens_corner_video']);
        }

        $childrensCornerAudio = $this->resolveChildrensCornerAudio($request, $post);
        if ($childrensCornerAudio !== null) {
            $data['meta']['childrens_corner_audio'] = $childrensCornerAudio;
        } elseif (CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            unset($data['meta']['childrens_corner_audio']);
        }

        $childrensCornerCertificate = $this->resolveChildrensCornerCertificate($request, $post);
        if ($childrensCornerCertificate !== null) {
            $data['meta']['childrens_corner_certificate'] = $childrensCornerCertificate;
        } elseif (CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            unset($data['meta']['childrens_corner_certificate']);
        }

        $awarenessInfographics = $this->resolveAwarenessInfographics($request, $post);
        if ($awarenessInfographics !== null) {
            $data['meta']['awareness_infographics'] = $awarenessInfographics;
        } elseif (data_get($post->meta, 'awareness_infographics')) {
            unset($data['meta']['awareness_infographics']);
        }

        $awarenessDocuments = $this->resolveAwarenessDocuments($request, $post);
        if ($awarenessDocuments !== null) {
            $data['meta']['awareness_documents'] = $awarenessDocuments;
        } elseif (data_get($post->meta, 'awareness_documents')) {
            unset($data['meta']['awareness_documents']);
        }

        $businessDocuments = $this->resolveBusinessDocuments($request, $post);
        if ($businessDocuments !== null) {
            $data['meta']['business_documents'] = $businessDocuments;
        } elseif (data_get($post->meta, 'business_documents')) {
            unset($data['meta']['business_documents']);
        }

        $businessGallery = $this->resolveBusinessGallery($request, $post);
        if ($businessGallery !== null) {
            $data['meta']['business_gallery'] = $businessGallery;
        } elseif (data_get($post->meta, 'business_gallery')) {
            unset($data['meta']['business_gallery']);
        }

        $womensWorldGallery = $this->resolveWomensWorldGallery($request, $post);
        if ($womensWorldGallery !== null) {
            $data['meta']['womens_world_gallery'] = $womensWorldGallery;
        } elseif (data_get($post->meta, 'womens_world_gallery')) {
            unset($data['meta']['womens_world_gallery']);
        }

        $womensWorldAudio = $this->resolveWomensWorldAudio($request, $post);
        if ($womensWorldAudio !== null) {
            $data['meta']['womens_world_audio'] = $womensWorldAudio;
        } elseif ($request->input('content_type') === 'womens-world' && $request->input('womens_world_audio_source_type') === 'none') {
            unset($data['meta']['womens_world_audio']);
        } elseif ($request->boolean('remove_womens_world_audio')) {
            unset($data['meta']['womens_world_audio']);
        }

        $data = $this->applyPoetryRegionalLocation($data);
        $data['allow_comments'] = $this->shouldAllowComments($request);
        $data['allow_questions'] = $this->shouldAllowQuestions($request);
        $data['allow_suggestions'] = $this->shouldAllowSuggestions($request);
        $data['allow_feedback'] = $this->shouldAllowFeedback($request);
        $data['allow_additional_evidence'] = $this->shouldAllowAdditionalEvidence($request);
        $data['allow_sharing'] = $this->shouldAllowSharing($request);
        $data['allow_poll'] = $this->shouldAllowPoll($request, $data['content_type'] ?? $post?->content_type);
        $wasPending = $post->isPendingApproval();
        $data = array_merge($data, $this->resolvePublicationState($request, $post));

        [$featuredImages, $featuredImagePath] = $this->resolveFeaturedImages($request, $post);
        $this->deleteRemovedFeaturedImages($post->featuredImages(), $featuredImages);
        $data['featured_images'] = $featuredImages;
        $data['featured_image_path'] = $featuredImagePath;
        $data['video'] = CommunityPost::usesChildrensCornerFlow($request->input('content_type'))
            ? null
            : $this->resolveVideo($request, $post);

        if ($post->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        $originalAttributes = $post->getOriginal();
        $post->update($data);

        $this->syncReportTrustScore($post->fresh());

        CommunityPostAuditLogger::logUpdated($post, $request, $originalAttributes);

        if ($post->isReportContent() && $post->isPubliclyVisible()) {
            CommunityReportEngagementNotificationService::notifyFollowersOfReportUpdate(
                $post->fresh(),
                'The author published an update to this report.'
            );
        }

        if ($post->isPendingApproval() && ! $wasPending) {
            $this->notifyAdminsOfPendingPost($post->fresh());
        } elseif (
            $post->isWomensWorldPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityWomensWorldEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
            CommunityEngagementNotificationService::notifySubscribersOfPublishedPost($post->fresh());
        } elseif (
            in_array($post->content_type, ['poetry', 'autobiography'], true)
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityStoryEngagementNotificationService::notifyAuthorOfPublishedWithoutAudio($post->fresh());
        }

        $message = $post->isPendingApproval()
            ? 'Community post submitted for admin approval.'
            : 'Community post updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('community.posts.show', $post),
            ]);
        }

        return redirect()->route('community.posts.show', $post)->with('success', $message);
    }

    public function destroy(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        return $this->destroyPost($request, $post);
    }

    public function uploadInlineImage(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => ['required', 'image', 'max:4096'],
        ]);

        $file = $request->file('upload');

        return response()->json([
            'url' => CommunityPostFileUploader::storeInlineImage($file),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?CommunityPost $post = null): array
    {
        $typeKeys = array_keys(CommunityContentTaxonomy::formTypes());
        $contentType = $request->input('content_type');
        $isReport = $contentType === 'reports';
        $usesStructuredLocation = CommunityPost::usesStructuredLocation(is_string($contentType) ? $contentType : null);
        $mountsStructuredLocation = CommunityPost::mountsStructuredLocationFields(is_string($contentType) ? $contentType : null);
        $isChildrensCorner = CommunityPost::usesChildrensCornerFlow(is_string($contentType) ? $contentType : null);
        $isAwareness = CommunityPost::usesAwarenessFlow(is_string($contentType) ? $contentType : null);
        $isBusiness = CommunityPost::usesBusinessFlow(is_string($contentType) ? $contentType : null);
        $isWomensWorld = CommunityPost::usesWomensWorldFlow(is_string($contentType) ? $contentType : null);
        $childShareType = $request->input('child_share_type');
        $childContentMode = CommunityContentTaxonomy::childrensCornerContentMode(is_string($childShareType) ? $childShareType : null);

        if ($isChildrensCorner && $request->filled('child_share_type')) {
            $request->merge(['category' => $request->input('child_share_type')]);
        }

        if ($isAwareness && $request->filled('awareness_category')) {
            $request->merge(['category' => $request->input('awareness_category')]);
        }

        if ($isBusiness && $request->filled('business_category')) {
            $request->merge(['category' => $request->input('business_category')]);
        }

        if ($isWomensWorld && $request->filled('womens_world_category')) {
            $request->merge(['category' => $request->input('womens_world_category')]);
        }

        $rules = [
            'content_type' => ['required', Rule::in($typeKeys)],
            'writing_purpose' => ['required', 'string', 'max:120'],
            'category' => [
                'required',
                'string',
                'max:120',
                function (string $attribute, mixed $value, \Closure $fail) use ($contentType): void {
                    if (! is_string($contentType) || ! CommunityContentTaxonomy::isValidCategory($contentType, (string) $value)) {
                        $fail('Please choose a valid category for the selected content type.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => [
                Rule::requiredIf(function () use ($contentType, $childContentMode): bool {
                    if (CommunityPost::isBookContentType(is_string($contentType) ? $contentType : null)) {
                        return false;
                    }

                    if (CommunityPost::usesChildrensCornerFlow(is_string($contentType) ? $contentType : null)) {
                        return in_array($childContentMode, ['rich_text', 'poem'], true);
                    }

                    return true;
                }),
            ],
            'book_pages' => [Rule::requiredIf(fn () => CommunityPost::isBookContentType($contentType)), 'array', 'min:1'],
            'book_pages.*.content' => ['nullable', 'string'],
            'book_pages.*.language' => ['nullable', Rule::in(['en', 'hi'])],
            'book_pages.*.title' => [
                Rule::requiredIf(fn () => CommunityPost::usesChapterLayout(is_string($contentType) ? $contentType : null)),
                'nullable',
                'string',
                'max:160',
            ],
            'book_pages.*.summary' => ['nullable', 'string', 'max:500'],
            'editor_language' => [
                'nullable',
                Rule::in(CommunityContentTaxonomy::editorLanguageCodesFor(is_string($contentType) ? $contentType : null)),
            ],
            'featured_images' => ['nullable', 'array', 'max:'.self::MAX_FEATURED_IMAGES],
            'featured_images.*' => ['image', 'max:4096'],
            'removed_featured_images' => ['nullable', 'array'],
            'removed_featured_images.*' => ['string', 'max:255'],
            'tags' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $count = collect(explode(',', $value))
                        ->map(fn (string $tag) => trim($tag))
                        ->filter()
                        ->unique()
                        ->count();

                    if ($count > self::MAX_TAGS) {
                        $fail('You can add up to '.self::MAX_TAGS.' tags only.');
                    }
                },
            ],
            'status' => ['required', Rule::in([CommunityPost::STATUS_DRAFT, CommunityPost::STATUS_PUBLISHED])],
            'publish_as' => [
                Rule::requiredIf(fn () => $request->input('status') === CommunityPost::STATUS_PUBLISHED),
                'nullable',
                Rule::in(array_keys(CommunityPost::PUBLISH_AS_OPTIONS)),
            ],
            'pen_name' => [
                Rule::requiredIf(fn () => $request->input('status') === CommunityPost::STATUS_PUBLISHED
                    && $request->input('publish_as') === CommunityPost::PUBLISH_AS_PEN_NAME),
                'nullable',
                'string',
                'max:120',
            ],
            'allow_comments' => ['nullable', 'boolean'],
            'allow_questions' => ['nullable', 'boolean'],
            'allow_suggestions' => ['nullable', 'boolean'],
            'allow_feedback' => ['nullable', 'boolean'],
            'allow_additional_evidence' => ['nullable', 'boolean'],
            'allow_sharing' => ['nullable', 'boolean'],
            'allow_poll' => ['nullable', 'boolean'],
            'poll_subject' => [
                Rule::requiredIf(fn () => $request->boolean('allow_poll')),
                'nullable',
                'string',
                'max:160',
            ],
            'author_bio' => [
                Rule::requiredIf(fn () => CommunityPost::usesAutobiographyFlow(is_string($contentType) ? $contentType : null)),
                'nullable',
                'string',
                'max:500',
            ],
            'location_type' => ($usesStructuredLocation || $mountsStructuredLocation || $isChildrensCorner)
                ? ['nullable', Rule::in(array_keys(CommunityPost::locationTypeOptions($contentType)))]
                : ['required', Rule::in(array_keys(CommunityPost::locationTypeOptions($contentType)))],
            'location' => ['nullable', 'string', 'max:160'],
            'location_lat' => ($usesStructuredLocation || $mountsStructuredLocation)
                ? ['nullable', 'numeric', 'between:-90,90']
                : [
                    Rule::requiredIf(fn () => ! $isChildrensCorner && in_array($request->input('location_type'), CommunityPost::locationTypesRequiringPlace(), true)),
                    'nullable',
                    'numeric',
                    'between:-90,90',
                ],
            'location_lng' => ($usesStructuredLocation || $mountsStructuredLocation)
                ? ['nullable', 'numeric', 'between:-180,180']
                : [
                    Rule::requiredIf(fn () => ! $isChildrensCorner && in_array($request->input('location_type'), CommunityPost::locationTypesRequiringPlace(), true)),
                    'nullable',
                    'numeric',
                    'between:-180,180',
                ],
            'video_source_type' => ['nullable', Rule::in(['none', 'youtube', 'upload'])],
            'video_youtube_url' => [
                Rule::requiredIf(fn () => $request->input('video_source_type') === 'youtube'),
                'nullable',
                'url',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || ! CommunityPost::parseYoutubeVideoId($value)) {
                        $fail('Please enter a valid YouTube video link.');
                    }
                },
            ],
            'video_file' => [
                Rule::requiredIf(fn () => $request->input('video_source_type') === 'upload' && ! $request->boolean('keep_existing_video')),
                'nullable',
                'file',
                'max:'.self::MAX_VIDEO_FILE_KB,
                'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska',
            ],
            'remove_video' => ['nullable', 'boolean'],
            'keep_existing_video' => ['nullable', 'boolean'],
            'report_subtitle' => ['nullable', 'string', 'max:255'],
            'reporting_period' => ['nullable', 'string', 'max:120'],
            'report_date' => ['nullable', 'date'],
            'prepared_by' => ['nullable', 'string', 'max:160'],
            'report_scope' => ['nullable', 'string', 'max:1000'],
            'methodology' => ['nullable', 'string', 'max:2000'],
            'data_sources' => ['nullable', 'string', 'max:2000'],
            'key_findings' => ['nullable', 'string', 'max:3000'],
            'report_analysis' => ['nullable', 'string', 'max:3000'],
            'recommendations' => ['nullable', 'string', 'max:3000'],
            'report_conclusion' => ['nullable', 'string', 'max:3000'],
            'issue_attachments' => ['nullable', 'array', 'max:6'],
            'issue_attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi,pdf,doc,docx'],
            'news_documents' => ['nullable', 'array', 'max:6'],
            'news_documents.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx'],
            'removed_news_documents' => ['nullable', 'array'],
            'removed_news_documents.*' => ['string', 'max:255'],
            'story_gallery' => ['nullable', 'array', 'max:'.self::MAX_STORY_GALLERY],
            'story_gallery.*' => ['image', 'max:4096', 'mimes:jpg,jpeg,png,webp,gif'],
            'removed_story_gallery' => ['nullable', 'array'],
            'removed_story_gallery.*' => ['string', 'max:255'],
            'story_audio_source_type' => ['nullable', Rule::in(['none', 'upload', 'recording'])],
            'story_audio_file' => [
                Rule::requiredIf(fn () => $request->input('content_type') === 'stories'
                    && $request->input('story_audio_source_type') === 'upload'
                    && ! $request->boolean('keep_existing_story_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'story_audio_recording' => [
                Rule::requiredIf(fn () => $request->input('content_type') === 'stories'
                    && $request->input('story_audio_source_type') === 'recording'
                    && ! $request->boolean('keep_existing_story_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'keep_existing_story_audio' => ['nullable', 'boolean'],
            'remove_story_audio' => ['nullable', 'boolean'],
            'story_target_audience' => ['nullable', 'array'],
            'story_target_audience.*' => ['string', Rule::in(CommunityContentTaxonomy::storyTargetAudiences())],
            'story_themes' => ['nullable', 'array'],
            'story_themes.*' => ['string', Rule::in(CommunityContentTaxonomy::storyThemes())],
            'sub_category' => [
                Rule::requiredIf(fn () => $contentType === 'poetry'),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::poetrySubCategories()),
            ],
            'poetry_themes' => ['nullable', 'array'],
            'poetry_themes.*' => ['string', Rule::in(CommunityContentTaxonomy::poetryThemes())],
            'poetry_target_audience' => ['nullable', 'array'],
            'poetry_target_audience.*' => ['string', Rule::in(CommunityContentTaxonomy::poetryTargetAudiences())],
            'poetry_inspiration' => ['nullable', 'string', 'max:2000'],
            'poetry_part_of_series' => ['nullable', Rule::in(['Yes', 'No'])],
            'poetry_series_name' => [
                Rule::requiredIf(fn () => $contentType === 'poetry' && $request->input('poetry_part_of_series') === 'Yes'),
                'nullable',
                'string',
                'max:160',
            ],
            'poetry_series_part' => ['nullable', 'string', 'max:40'],
            'poetry_audio_source_type' => ['nullable', Rule::in(['none', 'upload', 'recording'])],
            'poetry_audio_file' => [
                Rule::requiredIf(fn () => $contentType === 'poetry'
                    && $request->input('poetry_audio_source_type') === 'upload'
                    && ! $request->boolean('keep_existing_poetry_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'poetry_audio_recording' => [
                Rule::requiredIf(fn () => $contentType === 'poetry'
                    && $request->input('poetry_audio_source_type') === 'recording'
                    && ! $request->boolean('keep_existing_poetry_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'keep_existing_poetry_audio' => ['nullable', 'boolean'],
            'remove_poetry_audio' => ['nullable', 'boolean'],
            'location_country' => [
                Rule::requiredIf(fn () => $usesStructuredLocation),
                'nullable',
                'string',
                'max:120',
            ],
            'location_state' => [
                Rule::requiredIf(fn () => $usesStructuredLocation),
                'nullable',
                'string',
                'max:120',
            ],
            'location_district' => [
                Rule::requiredIf(fn () => $usesStructuredLocation),
                'nullable',
                'string',
                'max:120',
            ],
            'location_city' => [
                Rule::requiredIf(fn () => $usesStructuredLocation),
                'nullable',
                'string',
                'max:120',
            ],
            'location_locality' => [
                Rule::requiredIf(fn () => (string) $contentType === 'awareness'),
                'nullable',
                'string',
                'max:120',
            ],
            'autobiography_type' => [
                Rule::requiredIf(fn () => CommunityPost::usesAutobiographyFlow(is_string($contentType) ? $contentType : null)),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::autobiographyTypes()),
            ],
            'life_timeline' => ['nullable', 'array', 'max:'.self::MAX_LIFE_TIMELINE],
            'life_timeline.*.year' => ['required_with:life_timeline', 'string', 'max:10'],
            'life_timeline.*.title' => ['required_with:life_timeline', 'string', 'max:160'],
            'life_timeline.*.description' => ['required_with:life_timeline', 'string', 'max:2000'],
            'life_timeline.*.existing_photo_path' => ['nullable', 'string', 'max:255'],
            'life_timeline.*.photo' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp,gif'],
            'autobiography_audio_source_type' => ['nullable', Rule::in(['none', 'upload', 'recording'])],
            'autobiography_audio_file' => [
                Rule::requiredIf(fn () => CommunityPost::usesAutobiographyFlow(is_string($contentType) ? $contentType : null)
                    && $request->input('autobiography_audio_source_type') === 'upload'
                    && ! $request->boolean('keep_existing_autobiography_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'autobiography_audio_recording' => [
                Rule::requiredIf(fn () => CommunityPost::usesAutobiographyFlow(is_string($contentType) ? $contentType : null)
                    && $request->input('autobiography_audio_source_type') === 'recording'
                    && ! $request->boolean('keep_existing_autobiography_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'keep_existing_autobiography_audio' => ['nullable', 'boolean'],
            'remove_autobiography_audio' => ['nullable', 'boolean'],
            'birth_place' => ['nullable', 'string', 'max:160'],
            'current_location' => ['nullable', 'string', 'max:160'],
            'places_mentioned' => ['nullable', 'array', 'max:20'],
            'places_mentioned.*' => ['nullable', 'string', 'max:120'],
            'key_lessons_learned' => ['nullable', 'array', 'max:15'],
            'key_lessons_learned.*' => ['nullable', 'string', 'max:300'],
            'autobiography_achievements' => ['nullable', 'array', 'max:'.self::MAX_AUTOBIOGRAPHY_ACHIEVEMENTS],
            'autobiography_achievements.*.award_name' => ['nullable', 'string', 'max:160'],
            'autobiography_achievements.*.year' => ['nullable', 'string', 'max:10'],
            'autobiography_achievements.*.description' => ['nullable', 'string', 'max:1000'],
            'autobiography_achievements.*.existing_image_path' => ['nullable', 'string', 'max:255'],
            'autobiography_achievements.*.image' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp,gif'],
            'autobiography_documents' => ['nullable', 'array', 'max:'.self::MAX_AUTOBIOGRAPHY_DOCUMENTS],
            'autobiography_documents.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx'],
            'removed_autobiography_documents' => ['nullable', 'array'],
            'removed_autobiography_documents.*' => ['string', 'max:255'],
            'related_people' => ['nullable', 'array', 'max:20'],
            'related_people.*.name' => ['nullable', 'string', 'max:120'],
            'related_people.*.relationship' => ['nullable', 'string', 'max:80'],
            'child_share_type' => [
                Rule::requiredIf(fn () => $isChildrensCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::childrensCornerShareTypes()),
            ],
            'child_first_name' => [
                Rule::requiredIf(fn () => $isChildrensCorner),
                'nullable',
                'string',
                'max:80',
            ],
            'child_age_group' => [
                Rule::requiredIf(fn () => $isChildrensCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::childrensCornerAgeGroups()),
            ],
            'child_grade_level' => [
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::childrensCornerGradeLevels()),
            ],
            'child_school_name' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                Rule::requiredIf(fn () => $isChildrensCorner && $request->input('childrens_corner_privacy_setting') === 'school_community'),
                'nullable',
                'string',
                'max:160',
            ],
            'parent_name' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'required',
                'string',
                'max:120',
            ],
            'parent_mobile' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'required',
                'string',
                'max:20',
            ],
            'parent_email' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'email',
                'max:160',
            ],
            'parent_relationship' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::childrensCornerParentRelationships()),
            ],
            'child_parent_consent_identity' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'accepted',
            ],
            'child_parent_consent_publication' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'accepted',
            ],
            'child_parent_consent_original' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'accepted',
            ],
            'childrens_corner_privacy_setting' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::childrensCornerPrivacySettings())),
            ],
            'childrens_corner_safety_no_address' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'accepted',
            ],
            'childrens_corner_safety_no_harmful' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'accepted',
            ],
            'childrens_corner_safety_no_copyright' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'accepted',
            ],
            'childrens_corner_safety_no_inappropriate_media' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'accepted',
            ],
            'awareness_category' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessCategories()),
            ],
            'awareness_type' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessTypes()),
            ],
            'awareness_level' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessLevels()),
            ],
            'awareness_target_audience' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'required',
                'array',
                'min:1',
            ],
            'awareness_target_audience.*' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessTargetAudiences()),
            ],
            'awareness_posted_by' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessPostedByOptions()),
            ],
            'awareness_organization_name' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                'max:160',
            ],
            'awareness_campaign_start_date' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'date',
            ],
            'awareness_campaign_end_date' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'date',
                'after_or_equal:awareness_campaign_start_date',
            ],
            'awareness_video_type' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessVideoTypes()),
            ],
            'awareness_infographics' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'array',
                'max:'.self::MAX_AWARENESS_INFOGRAPHICS,
            ],
            'awareness_infographics.*' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'file',
                'max:20480',
                'mimes:png,jpg,jpeg,pdf',
            ],
            'removed_awareness_infographics' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'array',
            ],
            'removed_awareness_infographics.*' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'string',
                'max:255',
            ],
            'awareness_documents' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'array',
                'max:'.self::MAX_AWARENESS_DOCUMENTS,
            ],
            'awareness_documents.*' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,ppt,pptx',
            ],
            'removed_awareness_documents' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'array',
            ],
            'removed_awareness_documents.*' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'string',
                'max:255',
            ],
            'awareness_call_to_action' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'required',
                'string',
                'max:1000',
            ],
            'awareness_action_items' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'array',
            ],
            'awareness_action_items.*' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessCallToActionExamples()),
            ],
            'awareness_allow_campaign_join' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'boolean',
            ],
            'awareness_has_event' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'boolean',
            ],
            'awareness_event_type' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessEventTypes()),
            ],
            'awareness_event_date' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'date',
            ],
            'awareness_event_venue' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                'max:160',
            ],
            'awareness_event_time' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                'max:40',
            ],
            'awareness_event_organizer' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                'max:160',
            ],
            'awareness_social_impact_categories' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'array',
            ],
            'awareness_social_impact_categories.*' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'string',
                Rule::in(CommunityContentTaxonomy::awarenessSocialImpactCategories()),
            ],
            'awareness_allow_cause_support' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'boolean',
            ],
            'awareness_allow_pledges' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'boolean',
            ],
            'awareness_pledge_options' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                'max:5000',
            ],
            'awareness_poll_question' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $isAwareness && $request->boolean('allow_poll')),
            ],
            'awareness_impact_trees_planted' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'integer',
                'min:0',
            ],
            'awareness_impact_volunteers_joined' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'integer',
                'min:0',
            ],
            'awareness_impact_people_reached' => [
                Rule::excludeIf(fn () => ! $isAwareness),
                'nullable',
                'integer',
                'min:0',
            ],
            'business_category' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::businessMainCategories()),
            ],
            'business_content_type' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::businessContentTypes()),
            ],
            'business_stage' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::businessStages()),
            ],
            'business_target_audience' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'required',
                'array',
                'min:1',
            ],
            'business_target_audience.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'string',
                Rule::in(CommunityContentTaxonomy::businessTargetAudiences()),
            ],
            'business_challenges' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
            ],
            'business_challenges.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'string',
                Rule::in(CommunityContentTaxonomy::businessChallenges()),
            ],
            'business_opportunity_type' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::businessOpportunityTypes()),
            ],
            'business_market_segments' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
            ],
            'business_market_segments.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'string',
                Rule::in(CommunityContentTaxonomy::businessMarketSegments()),
            ],
            'business_themes' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
            ],
            'business_themes.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'string',
                Rule::in(CommunityContentTaxonomy::businessThemes()),
            ],
            'business_name' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:160',
            ],
            'business_author_designation' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:120',
            ],
            'business_profile_type' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::businessProfileTypes()),
            ],
            'business_industry' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::businessIndustries()),
            ],
            'business_video_type' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::businessVideoTypes()),
            ],
            'business_ask_community' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:500',
            ],
            'business_useful_links' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:5000',
            ],
            'business_government_schemes' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:5000',
            ],
            'business_training_programs' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:5000',
            ],
            'business_industry_resources' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:5000',
            ],
            'business_contact_options' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
            ],
            'business_contact_options.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'string',
                Rule::in(CommunityContentTaxonomy::businessContactOptions()),
            ],
            'business_poll_question' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $isBusiness && $request->boolean('allow_poll')),
            ],
            'business_poll_options' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'string',
                'max:2000',
            ],
            'business_gallery' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
                'max:'.self::MAX_BUSINESS_GALLERY,
            ],
            'business_gallery.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'image',
                'max:4096',
                'mimes:jpg,jpeg,png,webp,gif',
            ],
            'removed_business_gallery' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
            ],
            'removed_business_gallery.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'string',
                'max:255',
            ],
            'business_documents' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
                'max:'.self::MAX_BUSINESS_DOCUMENTS,
            ],
            'business_documents.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx',
            ],
            'removed_business_documents' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'nullable',
                'array',
            ],
            'removed_business_documents.*' => [
                Rule::excludeIf(fn () => ! $isBusiness),
                'string',
                'max:255',
            ],
            'womens_world_category' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldMainCategories()),
            ],
            'womens_world_content_type' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldContentTypes()),
            ],
            'womens_world_target_audience' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'required',
                'array',
                'min:1',
            ],
            'womens_world_target_audience.*' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldTargetAudiences()),
            ],
            'womens_world_featured_topics' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'array',
            ],
            'womens_world_featured_topics.*' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldFeaturedTopics()),
            ],
            'womens_world_video_type' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldVideoTypes()),
            ],
            'womens_world_gallery' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'array',
                'max:'.self::MAX_WOMENS_WORLD_GALLERY,
            ],
            'womens_world_gallery.*' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'image',
                'max:4096',
            ],
            'removed_womens_world_gallery' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'array',
            ],
            'removed_womens_world_gallery.*' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'string',
                'max:255',
            ],
            'womens_world_life_stage' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldLifeStages()),
            ],
            'womens_world_themes' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'array',
            ],
            'womens_world_themes.*' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldThemes()),
            ],
            'womens_world_audio_source_type' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                Rule::in(['none', 'upload', 'recording']),
            ],
            'womens_world_audio_file' => [
                Rule::requiredIf(fn () => $isWomensWorld
                    && $request->input('womens_world_audio_source_type') === 'upload'
                    && ! $request->boolean('keep_existing_womens_world_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'womens_world_audio_recording' => [
                Rule::requiredIf(fn () => $isWomensWorld
                    && $request->input('womens_world_audio_source_type') === 'recording'
                    && ! $request->boolean('keep_existing_womens_world_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'keep_existing_womens_world_audio' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'boolean',
            ],
            'remove_womens_world_audio' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'boolean',
            ],
            'womens_world_business_name' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:160',
            ],
            'womens_world_business_category' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldBusinessCategories()),
            ],
            'womens_world_website_url' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'url',
                'max:255',
            ],
            'womens_world_vendor_profile_url' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'url',
                'max:255',
            ],
            'womens_world_ask_community' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:500',
            ],
            'womens_world_poll_question' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $isWomensWorld && $request->boolean('allow_poll')),
            ],
            'womens_world_poll_options' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:2000',
            ],
            'womens_world_support_requests' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'array',
            ],
            'womens_world_support_requests.*' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldSupportRequests()),
            ],
            'womens_world_community_groups' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'array',
            ],
            'womens_world_community_groups.*' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'string',
                Rule::in(CommunityContentTaxonomy::womensWorldCommunityGroups()),
            ],
            'womens_world_visibility' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::womensWorldVisibilitySettings())),
            ],
            'womens_world_useful_websites' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:5000',
            ],
            'womens_world_government_schemes' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:5000',
            ],
            'womens_world_training_programs' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:5000',
            ],
            'womens_world_scholarships' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:5000',
            ],
            'womens_world_support_organizations' => [
                Rule::excludeIf(fn () => ! $isWomensWorld),
                'nullable',
                'string',
                'max:5000',
            ],
            'childrens_corner_submitted_through' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::childrensCornerSubmittedThroughOptions()),
            ],
            'childrens_corner_school_competition_entry' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                Rule::in(['Yes', 'No']),
            ],
            'childrens_corner_city' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'string',
                'max:120',
            ],
            'childrens_corner_district' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'string',
                'max:120',
            ],
            'childrens_corner_state' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'string',
                'max:120',
            ],
            'childrens_corner_talent_categories' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'array',
            ],
            'childrens_corner_talent_categories.*' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'string',
                Rule::in(CommunityContentTaxonomy::childrensCornerTalentCategories()),
            ],
            'childrens_corner_achievement' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'string',
                'max:1000',
            ],
            'childrens_corner_video_source_type' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                Rule::in(['none', 'youtube', 'upload']),
            ],
            'childrens_corner_video_youtube_url' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner || $request->input('childrens_corner_video_source_type') !== 'youtube'),
                'required',
                'url',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    if (! CommunityPost::parseYoutubeVideoId($value)) {
                        $fail('Please enter a valid YouTube video link.');
                    }
                },
            ],
            'childrens_corner_video_file' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner
                    || $request->input('childrens_corner_video_source_type') !== 'upload'
                    || $request->boolean('keep_existing_childrens_corner_video')),
                'required',
                'file',
                'max:'.self::MAX_VIDEO_FILE_KB,
                'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska',
            ],
            'keep_existing_childrens_corner_video' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'boolean',
            ],
            'remove_childrens_corner_video' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'boolean',
            ],
            'childrens_corner_audio_source_type' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                Rule::in(['none', 'upload', 'recording']),
            ],
            'childrens_corner_audio_file' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner
                    || $request->input('childrens_corner_audio_source_type') !== 'upload'
                    || $request->boolean('keep_existing_childrens_corner_audio')),
                'required',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'childrens_corner_audio_recording' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner
                    || $request->input('childrens_corner_audio_source_type') !== 'recording'
                    || $request->boolean('keep_existing_childrens_corner_audio')),
                'required',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'keep_existing_childrens_corner_audio' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'boolean',
            ],
            'remove_childrens_corner_audio' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'boolean',
            ],
            'childrens_corner_certificate_file' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner
                    || $request->boolean('keep_existing_childrens_corner_certificate')),
                'nullable',
                'file',
                'max:4096',
                'mimes:pdf,jpg,jpeg,png,webp',
            ],
            'keep_existing_childrens_corner_certificate' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'boolean',
            ],
            'remove_childrens_corner_certificate' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'boolean',
            ],
            'childrens_corner_comments_moderated' => [
                Rule::excludeIf(fn () => ! $isChildrensCorner),
                'nullable',
                'boolean',
            ],
            'childrens_corner_art_file' => [
                Rule::requiredIf(fn () => $isChildrensCorner
                    && $childContentMode === 'image'
                    && ! $request->boolean('keep_existing_childrens_corner_art')
                    && ! ($post && filled(data_get($post->meta, 'childrens_corner_art.path')))),
                'nullable',
                'image',
                'max:4096',
                'mimes:jpg,jpeg,png,webp',
            ],
            'keep_existing_childrens_corner_art' => ['nullable', 'boolean'],
            'childrens_corner_project_description' => [
                Rule::requiredIf(fn () => $isChildrensCorner && $childContentMode === 'project'),
                'nullable',
                'string',
                'max:5000',
            ],
            'childrens_corner_project_files' => ['nullable', 'array', 'max:'.self::MAX_CHILDRENS_CORNER_PROJECT_FILES],
            'childrens_corner_project_files.*' => [
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,pdf,ppt,pptx,doc,docx',
            ],
            'keep_childrens_corner_project_files' => ['nullable', 'array'],
            'keep_childrens_corner_project_files.*' => ['string', 'max:255'],
            'existing_childrens_corner_project_files' => ['nullable', 'array'],
            'childrens_corner_quiz' => [
                Rule::requiredIf(fn () => $isChildrensCorner && $childContentMode === 'quiz'),
                'nullable',
                'array',
                'min:1',
                'max:'.self::MAX_CHILDRENS_CORNER_QUIZ_QUESTIONS,
            ],
            'childrens_corner_quiz.*.question' => ['required_with:childrens_corner_quiz', 'string', 'max:500'],
            'childrens_corner_quiz.*.options' => ['required_with:childrens_corner_quiz', 'array', 'min:2', 'max:6'],
            'childrens_corner_quiz.*.options.*' => ['required', 'string', 'max:255'],
            'childrens_corner_quiz.*.correct_answer' => ['required_with:childrens_corner_quiz', 'string', 'max:255'],
            'childrens_corner_themes' => ['nullable', 'array'],
            'childrens_corner_themes.*' => ['string', Rule::in(CommunityContentTaxonomy::childrensCornerThemes())],
            'childrens_corner_gallery' => ['nullable', 'array', 'max:'.self::MAX_CHILDRENS_CORNER_GALLERY],
            'childrens_corner_gallery.*' => ['image', 'max:4096', 'mimes:jpg,jpeg,png,webp,gif'],
            'removed_childrens_corner_gallery' => ['nullable', 'array'],
            'removed_childrens_corner_gallery.*' => ['string', 'max:255'],
            'accept_content_responsibility' => ['accepted'],
            'accept_original_work_indemnity' => ['accepted'],
        ];

        if (is_string($contentType)) {
            $rules = array_merge($rules, CommunityPostFormFields::validationRules($contentType));
        }

        if ($contentType === 'reports') {
            $rules['observation_period_to'][] = 'after_or_equal:observation_period_from';
            $rules['action_requested_from'][] = Rule::requiredIf(
                fn () => $request->input('action_needed') === 'Yes'
            );
        }

        $validated = $request->validate($rules);
        $this->assertFeaturedImageLimit($request, $post);
        $this->assertAwarenessCampaignBanner($request, $post);

        if ($isChildrensCorner) {
            $validated['category'] = (string) ($validated['child_share_type'] ?? $request->input('child_share_type'));

            if ($childContentMode === 'quiz') {
                $quizErrors = [];

                foreach ((array) $request->input('childrens_corner_quiz', []) as $index => $question) {
                    if (! is_array($question)) {
                        continue;
                    }

                    $options = collect((array) ($question['options'] ?? []))
                        ->map(fn (mixed $option): string => trim((string) $option))
                        ->filter()
                        ->values()
                        ->all();
                    $correctAnswer = trim((string) ($question['correct_answer'] ?? ''));

                    if ($correctAnswer !== '' && ! in_array($correctAnswer, $options, true)) {
                        $quizErrors['childrens_corner_quiz.'.$index.'.correct_answer'] = 'The correct answer must match one of the options exactly.';
                    }
                }

                if ($quizErrors !== []) {
                    throw \Illuminate\Validation\ValidationException::withMessages($quizErrors);
                }
            }
        }

        if ($isAwareness) {
            $validated['category'] = (string) ($validated['awareness_category'] ?? $request->input('awareness_category'));
        }

        if ($isBusiness) {
            $validated['category'] = (string) ($validated['business_category'] ?? $request->input('business_category'));
        }

        if ($isWomensWorld) {
            $validated['category'] = (string) ($validated['womens_world_category'] ?? $request->input('womens_world_category'));
        }

        if (CommunityPost::isBookContentType($contentType)) {
            $bookPages = $this->normalizeBookPages(
                $validated['book_pages'] ?? [],
                is_string($contentType) ? $contentType : null
            );

            if ($bookPages === []) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'book_pages' => CommunityPost::usesChapterLayout(is_string($contentType) ? $contentType : null)
                        ? 'Please add content to at least one chapter.'
                        : 'Please add content to at least one book page.',
                ]);
            }

            $validated['body'] = CommunityPost::bodyFromBookPages($bookPages);
            $validated['book_pages'] = $bookPages;
        }

        unset($validated['book_pages']);

        if ($isChildrensCorner) {
            $validated = $this->applyChildrensCornerBroadLocation($validated, $request);
        } elseif ($isWomensWorld) {
            $validated = $this->applyWomensWorldOptionalLocation($validated, $request);
        } elseif ($usesStructuredLocation) {
            $validated = $this->applyStructuredLocation($validated, $request);
        } elseif ($validated['location_type'] === CommunityPost::LOCATION_TYPE_GPS) {
            $validated['location'] = filled($validated['location'] ?? null)
                ? $validated['location']
                : 'GPS Location';
            $validated['location_lat'] = filled($validated['location_lat'] ?? null) ? $validated['location_lat'] : null;
            $validated['location_lng'] = filled($validated['location_lng'] ?? null) ? $validated['location_lng'] : null;
        } elseif (! in_array($validated['location_type'], CommunityPost::locationTypesRequiringPlace(), true)) {
            $validated = array_merge($validated, CommunityPost::defaultLocationForType($validated['location_type']));
        }

        if (($validated['status'] ?? null) === CommunityPost::STATUS_DRAFT) {
            $validated['publish_as'] = null;
            $validated['pen_name'] = null;
        } elseif (($validated['publish_as'] ?? null) !== CommunityPost::PUBLISH_AS_PEN_NAME) {
            $validated['pen_name'] = null;
        }

        if (($validated['content_type'] ?? null) === 'reports' || ! ($validated['allow_poll'] ?? false)) {
            $validated['allow_poll'] = false;
            $validated['poll_subject'] = null;
        }

        if (! ($validated['allow_poll'] ?? false)) {
            $validated['poll_subject'] = null;
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyWomensWorldOptionalLocation(array $validated, Request $request): array
    {
        $structuredFields = $request->only([
            'location_country',
            'location_state',
            'location_district',
            'location_city',
        ]);

        if (! collect($structuredFields)->contains(fn (mixed $value): bool => filled($value))) {
            return array_merge($validated, CommunityPost::defaultLocationForType(CommunityPost::LOCATION_TYPE_GLOBAL));
        }

        $validated['location_type'] = CommunityPost::inferLocationTypeFromStructured($structuredFields);
        $validated['location'] = CommunityPost::composeStructuredLocation($structuredFields);
        $validated['location_lat'] = null;
        $validated['location_lng'] = null;

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyStructuredLocation(array $validated, Request $request): array
    {
        $structuredFields = $request->only(CommunityPost::structuredLocationMetaKeys());

        $validated['location_type'] = CommunityPost::inferLocationTypeFromStructured($structuredFields);
        $validated['location'] = CommunityPost::composeStructuredLocation($structuredFields);
        $validated['location_lat'] = filled($request->input('location_lat')) ? $request->input('location_lat') : null;
        $validated['location_lng'] = filled($request->input('location_lng')) ? $request->input('location_lng') : null;

        return $validated;
    }

    private function assertFeaturedImageLimit(Request $request, ?CommunityPost $post = null): void
    {
        $contentType = $request->input('content_type');
        $isAwareness = CommunityPost::usesAwarenessFlow(is_string($contentType) ? $contentType : null);
        $maxImages = $isAwareness ? 1 : self::MAX_FEATURED_IMAGES;
        $existing = $post ? $post->featuredImages() : [];
        $removed = (array) $request->input('removed_featured_images', []);
        $remaining = count(array_values(array_filter(
            $existing,
            fn (string $path) => ! in_array($path, $removed, true)
        )));
        $incoming = count($request->file('featured_images', []));

        if (($remaining + $incoming) > $maxImages) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'featured_images' => $isAwareness
                    ? 'Awareness posts can include one campaign banner image.'
                    : 'You can upload up to '.self::MAX_FEATURED_IMAGES.' featured images.',
            ]);
        }
    }

    private function assertAwarenessCampaignBanner(Request $request, ?CommunityPost $post = null): void
    {
        $contentType = $request->input('content_type');

        if (! CommunityPost::usesAwarenessFlow(is_string($contentType) ? $contentType : null)) {
            return;
        }

        if (($request->input('status') ?? CommunityPost::STATUS_DRAFT) === CommunityPost::STATUS_DRAFT) {
            return;
        }

        $existing = $post ? $post->featuredImages() : [];
        $removed = (array) $request->input('removed_featured_images', []);
        $remaining = count(array_values(array_filter(
            $existing,
            fn (string $path) => ! in_array($path, $removed, true)
        )));
        $incoming = count($request->file('featured_images', []));

        if (($remaining + $incoming) === 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'featured_images' => 'Please upload a campaign banner for this awareness post.',
            ]);
        }
    }

    /**
     * @return array{0: list<string>, 1: ?string}
     */
    private function resolveFeaturedImages(Request $request, ?CommunityPost $post = null): array
    {
        $existing = $post ? $post->featuredImages() : [];
        $removed = (array) $request->input('removed_featured_images', []);
        $images = array_values(array_filter(
            $existing,
            fn (string $path) => ! in_array($path, $removed, true)
        ));

        if ($request->hasFile('featured_images')) {
            $images = array_merge($images, $this->storeFeaturedImages($request));
        }

        $images = array_values(array_slice($images, 0, self::MAX_FEATURED_IMAGES));

        return [$images, $images[0] ?? null];
    }

    /**
     * @param  list<string>  $previous
     * @param  list<string>  $next
     */
    private function deleteRemovedFeaturedImages(array $previous, array $next): void
    {
        foreach (array_diff($previous, $next) as $path) {
            $this->deleteFeaturedImage($path);
        }
    }

    /**
     * @return list<string>
     */
    private function storeFeaturedImages(Request $request): array
    {
        return collect($request->file('featured_images', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeImage($file))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function metaPayload(Request $request): array
    {
        $contentType = $request->input('content_type');
        $payload = is_string($contentType)
            ? CommunityPostFormFields::metaPayloadFromRequest($request, $contentType)
            : [];

        $payload['author_bio'] = $request->input('author_bio');
        $payload['report_subtitle'] = $request->input('report_subtitle');
        $payload['reporting_period'] = $request->input('reporting_period');
        $payload['report_date'] = $request->input('report_date');
        $payload['prepared_by'] = $request->input('prepared_by');
        $payload['report_scope'] = $request->input('report_scope');
        $payload['methodology'] = $request->input('methodology');
        $payload['data_sources'] = $request->input('data_sources');
        $payload['key_findings'] = $request->input('key_findings');
        $payload['report_analysis'] = $request->input('report_analysis');
        $payload['recommendations'] = $request->input('recommendations');
        $payload['report_conclusion'] = $request->input('report_conclusion');
        $payload['editor_language'] = CommunityContentTaxonomy::normalizeEditorLanguage(
            is_string($contentType) ? $contentType : null,
            $request->input('editor_language', 'en')
        );

        if ($request->input('content_type') === 'reports') {
            $payload['report_author_name'] = filled($request->input('report_author_name'))
                ? $request->input('report_author_name')
                : ($request->user()?->name ?: $request->user()?->full_name);
        }

        return array_filter($payload, fn ($value) => filled($value) || is_bool($value));
    }

    private function applyWomensWorldPrivacyMeta(array $meta, Request $request, ?CommunityPost $post = null): array
    {
        if ($request->input('content_type') !== 'womens-world') {
            return $meta;
        }

        $visibility = array_key_exists(
            (string) $request->input('womens_world_visibility'),
            CommunityContentTaxonomy::womensWorldVisibilitySettings()
        )
            ? (string) $request->input('womens_world_visibility')
            : CommunityContentTaxonomy::womensWorldDefaultVisibilitySetting();

        if ($visibility === 'private_link') {
            $existing = data_get($post?->meta, 'womens_world_private_link_token');
            $meta['womens_world_private_link_token'] = filled($existing)
                ? $existing
                : \Illuminate\Support\Str::random(48);
        } else {
            unset($meta['womens_world_private_link_token']);
        }

        return $meta;
    }

    private function shouldAllowComments(Request $request): bool
    {
        return $request->boolean('allow_comments');
    }

    private function shouldAllowQuestions(Request $request): bool
    {
        if ($request->input('content_type') === 'news') {
            return $request->boolean('allow_questions');
        }

        return $request->boolean('allow_questions', true);
    }

    private function shouldAllowSharing(Request $request): bool
    {
        return $request->boolean('allow_sharing');
    }

    private function shouldAllowPoll(Request $request, ?string $contentType = null): bool
    {
        $type = $contentType ?? $request->input('content_type');

        if ($type === 'reports') {
            return false;
        }

        return $request->boolean('allow_poll');
    }

    private function shouldAllowSuggestions(Request $request): bool
    {
        return $request->boolean('allow_suggestions');
    }

    private function shouldAllowFeedback(Request $request): bool
    {
        return $request->boolean('allow_feedback');
    }

    private function shouldAllowAdditionalEvidence(Request $request): bool
    {
        return $request->boolean('allow_additional_evidence');
    }

    /**
     * @return list<string>|null
     */
    private function normalizeTags(?string $tags): ?array
    {
        if (! filled($tags)) {
            return null;
        }

        return collect(explode(',', $tags))
            ->map(fn (string $tag) => Str::lower(trim($tag)))
            ->filter()
            ->unique()
            ->take(self::MAX_TAGS)
            ->values()
            ->all();
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'community-post';
        $slug = $base;
        $counter = 2;

        while (CommunityPost::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeIssueAttachments(Request $request): array
    {
        return collect($request->file('issue_attachments', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeNewsDocuments(Request $request): array
    {
        return collect($request->file('news_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'news-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveNewsDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        $existing = (array) data_get($post?->meta, 'news_documents', []);
        $removed = (array) $request->input('removed_news_documents', []);

        if ($existing === [] && ! $request->hasFile('news_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('news_documents')) {
            $kept = array_values(array_merge($kept, $this->storeNewsDocuments($request)));
        }

        return $kept;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeStoryGallery(Request $request): array
    {
        return collect($request->file('story_gallery', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'story-gallery'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveStoryGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if ($request->input('content_type') !== 'stories') {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'story_gallery', []);
        $removed = (array) $request->input('removed_story_gallery', []);

        if ($existing === [] && ! $request->hasFile('story_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('story_gallery')) {
            $kept = array_values(array_merge($kept, $this->storeStoryGallery($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_STORY_GALLERY));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeAwarenessInfographics(Request $request): array
    {
        return collect($request->file('awareness_infographics', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'awareness-infographics'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveAwarenessInfographics(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAwarenessFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'awareness_infographics', []);
        $removed = (array) $request->input('removed_awareness_infographics', []);

        if ($existing === [] && ! $request->hasFile('awareness_infographics')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $file): bool => in_array((string) data_get($file, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('awareness_infographics')) {
            $kept = array_values(array_merge($kept, $this->storeAwarenessInfographics($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_AWARENESS_INFOGRAPHICS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeAwarenessDocuments(Request $request): array
    {
        return collect($request->file('awareness_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'awareness-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveAwarenessDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAwarenessFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'awareness_documents', []);
        $removed = (array) $request->input('removed_awareness_documents', []);

        if ($existing === [] && ! $request->hasFile('awareness_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('awareness_documents')) {
            $kept = array_values(array_merge($kept, $this->storeAwarenessDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_AWARENESS_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeBusinessDocuments(Request $request): array
    {
        return collect($request->file('business_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'business-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveBusinessDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesBusinessFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'business_documents', []);
        $removed = (array) $request->input('removed_business_documents', []);

        if ($existing === [] && ! $request->hasFile('business_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('business_documents')) {
            $kept = array_values(array_merge($kept, $this->storeBusinessDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_BUSINESS_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeBusinessGallery(Request $request): array
    {
        return collect($request->file('business_gallery', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'business-gallery'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveBusinessGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesBusinessFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'business_gallery', []);
        $removed = (array) $request->input('removed_business_gallery', []);

        if ($existing === [] && ! $request->hasFile('business_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('business_gallery')) {
            $kept = array_values(array_merge($kept, $this->storeBusinessGallery($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_BUSINESS_GALLERY));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeWomensWorldGallery(Request $request): array
    {
        return collect($request->file('womens_world_gallery', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'womens-world-gallery'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveWomensWorldGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesWomensWorldFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'womens_world_gallery', []);
        $removed = (array) $request->input('removed_womens_world_gallery', []);

        if ($existing === [] && ! $request->hasFile('womens_world_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('womens_world_gallery')) {
            $kept = array_values(array_merge($kept, $this->storeWomensWorldGallery($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_WOMENS_WORLD_GALLERY));
    }

    /**
     * @return list<array{year: string, title: string, description: string, photo: array{path: string, url: string, name: string, type: string}|null}>|null
     */
    private function resolveLifeTimeline(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAutobiographyFlow($request->input('content_type'))) {
            return null;
        }

        $entries = collect($request->input('life_timeline', []))
            ->filter(fn (mixed $entry): bool => is_array($entry)
                && filled($entry['year'] ?? null)
                && filled($entry['title'] ?? null)
                && filled($entry['description'] ?? null))
            ->values();

        if ($entries->isEmpty()) {
            $this->deleteLifeTimelinePhotos((array) data_get($post?->meta, 'life_timeline', []));

            return null;
        }

        $existingByPath = collect((array) data_get($post?->meta, 'life_timeline', []))
            ->mapWithKeys(function (mixed $entry): array {
                $path = (string) data_get($entry, 'photo.path', data_get($entry, 'photo_path', ''));

                return filled($path) ? [$path => $entry] : [];
            });

        $resolved = $entries
            ->take(self::MAX_LIFE_TIMELINE)
            ->map(function (array $entry, int $index) use ($request, $existingByPath): array {
                $photo = null;
                $uploadedFile = $request->file("life_timeline.$index.photo");

                if ($uploadedFile) {
                    $existingPath = (string) ($entry['existing_photo_path'] ?? '');
                    if (filled($existingPath)) {
                        CommunityPostFileUploader::deleteIfExists($existingPath);
                    }

                    $photo = CommunityPostFileUploader::storeAttachment($uploadedFile, 'autobiography-timeline');
                } elseif (filled($entry['existing_photo_path'] ?? null)) {
                    $existingPath = (string) $entry['existing_photo_path'];
                    $existingEntry = $existingByPath->get($existingPath);
                    $existingPhoto = data_get($existingEntry, 'photo');

                    if (is_array($existingPhoto)) {
                        $photo = $existingPhoto;
                    }
                }

                return [
                    'year' => trim((string) ($entry['year'] ?? '')),
                    'title' => trim((string) ($entry['title'] ?? '')),
                    'description' => trim((string) ($entry['description'] ?? '')),
                    'photo' => $photo,
                ];
            })
            ->values()
            ->all();

        $keptPaths = collect($resolved)
            ->map(fn (array $entry): string => (string) data_get($entry, 'photo.path', ''))
            ->filter()
            ->all();

        collect((array) data_get($post?->meta, 'life_timeline', []))
            ->each(function (mixed $entry) use ($keptPaths): void {
                $path = (string) data_get($entry, 'photo.path', data_get($entry, 'photo_path', ''));
                if (filled($path) && ! in_array($path, $keptPaths, true)) {
                    CommunityPostFileUploader::deleteIfExists($path);
                }
            });

        return $resolved;
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     */
    private function deleteLifeTimelinePhotos(array $entries): void
    {
        foreach ($entries as $entry) {
            CommunityPostFileUploader::deleteIfExists((string) data_get($entry, 'photo.path', data_get($entry, 'photo_path', '')));
        }
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}|null
     */
    private function resolveStoryAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if ($request->input('content_type') !== 'stories') {
            return null;
        }

        if ($request->boolean('remove_story_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'story_audio'));

            return null;
        }

        $sourceType = $request->input('story_audio_source_type', 'none');

        if ($sourceType === 'upload' && $request->hasFile('story_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'story_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('story_audio_file'), 'upload');
        }

        if ($sourceType === 'recording' && $request->hasFile('story_audio_recording')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'story_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('story_audio_recording'), 'recording');
        }

        if ($request->boolean('keep_existing_story_audio') && data_get($post?->meta, 'story_audio')) {
            return data_get($post->meta, 'story_audio');
        }

        if ($sourceType === 'none') {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'story_audio'));

            return null;
        }

        return data_get($post?->meta, 'story_audio');
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}|null
     */
    private function resolveWomensWorldAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesWomensWorldFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_womens_world_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'womens_world_audio'));

            return null;
        }

        $sourceType = $request->input('womens_world_audio_source_type', 'none');

        if ($sourceType === 'upload' && $request->hasFile('womens_world_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'womens_world_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('womens_world_audio_file'), 'upload');
        }

        if ($sourceType === 'recording' && $request->hasFile('womens_world_audio_recording')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'womens_world_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('womens_world_audio_recording'), 'recording');
        }

        if ($request->boolean('keep_existing_womens_world_audio') && data_get($post?->meta, 'womens_world_audio')) {
            return data_get($post->meta, 'womens_world_audio');
        }

        if ($sourceType === 'none') {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'womens_world_audio'));

            return null;
        }

        return data_get($post?->meta, 'womens_world_audio');
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}|null
     */
    private function resolvePoetryAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if ($request->input('content_type') !== 'poetry') {
            return null;
        }

        if ($request->boolean('remove_poetry_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'poetry_audio'));

            return null;
        }

        $sourceType = $request->input('poetry_audio_source_type', 'none');

        if ($sourceType === 'upload' && $request->hasFile('poetry_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'poetry_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('poetry_audio_file'), 'upload');
        }

        if ($sourceType === 'recording' && $request->hasFile('poetry_audio_recording')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'poetry_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('poetry_audio_recording'), 'recording');
        }

        if ($request->boolean('keep_existing_poetry_audio') && data_get($post?->meta, 'poetry_audio')) {
            return data_get($post->meta, 'poetry_audio');
        }

        if ($sourceType === 'none') {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'poetry_audio'));

            return null;
        }

        return data_get($post?->meta, 'poetry_audio');
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}|null
     */
    private function resolveAutobiographyAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAutobiographyFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_autobiography_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'autobiography_audio'));

            return null;
        }

        $sourceType = $request->input('autobiography_audio_source_type', 'none');

        if ($sourceType === 'upload' && $request->hasFile('autobiography_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'autobiography_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('autobiography_audio_file'), 'upload');
        }

        if ($sourceType === 'recording' && $request->hasFile('autobiography_audio_recording')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'autobiography_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('autobiography_audio_recording'), 'recording');
        }

        if ($request->boolean('keep_existing_autobiography_audio') && data_get($post?->meta, 'autobiography_audio')) {
            return data_get($post->meta, 'autobiography_audio');
        }

        if ($sourceType === 'none') {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'autobiography_audio'));

            return null;
        }

        return data_get($post?->meta, 'autobiography_audio');
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveAutobiographyDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAutobiographyFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'autobiography_documents', []);
        $removed = (array) $request->input('removed_autobiography_documents', []);

        if ($existing === [] && ! $request->hasFile('autobiography_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('autobiography_documents')) {
            $kept = array_values(array_merge(
                $kept,
                collect($request->file('autobiography_documents', []))
                    ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'autobiography-documents'))
                    ->values()
                    ->all()
            ));
        }

        return array_values(array_slice($kept, 0, self::MAX_AUTOBIOGRAPHY_DOCUMENTS));
    }

    /**
     * @return list<array{award_name: string, year: string, description: string, image: array{path: string, url: string, name: string, type: string}|null}>|null
     */
    private function resolveAutobiographyAchievements(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAutobiographyFlow($request->input('content_type'))) {
            return null;
        }

        $entries = collect($request->input('autobiography_achievements', []))
            ->filter(function (mixed $entry): bool {
                if (! is_array($entry)) {
                    return false;
                }

                return filled($entry['award_name'] ?? null)
                    || filled($entry['year'] ?? null)
                    || filled($entry['description'] ?? null)
                    || filled($entry['existing_image_path'] ?? null);
            })
            ->values();

        if ($entries->isEmpty()) {
            $this->deleteAutobiographyAchievementImages((array) data_get($post?->meta, 'autobiography_achievements', []));

            return null;
        }

        $existingByPath = collect((array) data_get($post?->meta, 'autobiography_achievements', []))
            ->mapWithKeys(function (mixed $entry): array {
                $path = (string) data_get($entry, 'image.path', '');

                return filled($path) ? [$path => $entry] : [];
            });

        $resolved = $entries
            ->take(self::MAX_AUTOBIOGRAPHY_ACHIEVEMENTS)
            ->map(function (array $entry, int $index) use ($request, $existingByPath): array {
                $image = null;
                $uploadedFile = $request->file("autobiography_achievements.$index.image");

                if ($uploadedFile) {
                    $existingPath = (string) ($entry['existing_image_path'] ?? '');
                    if (filled($existingPath)) {
                        CommunityPostFileUploader::deleteIfExists($existingPath);
                    }

                    $image = CommunityPostFileUploader::storeAttachment($uploadedFile, 'autobiography-achievements');
                } elseif (filled($entry['existing_image_path'] ?? null)) {
                    $existingPath = (string) $entry['existing_image_path'];
                    $existingEntry = $existingByPath->get($existingPath);
                    $existingImage = data_get($existingEntry, 'image');

                    if (is_array($existingImage)) {
                        $image = $existingImage;
                    }
                }

                return [
                    'award_name' => trim((string) ($entry['award_name'] ?? '')),
                    'year' => trim((string) ($entry['year'] ?? '')),
                    'description' => trim((string) ($entry['description'] ?? '')),
                    'image' => $image,
                ];
            })
            ->filter(fn (array $entry): bool => filled($entry['award_name']) || filled($entry['description']))
            ->values()
            ->all();

        if ($resolved === []) {
            $this->deleteAutobiographyAchievementImages((array) data_get($post?->meta, 'autobiography_achievements', []));

            return null;
        }

        $keptPaths = collect($resolved)
            ->map(fn (array $entry): string => (string) data_get($entry, 'image.path', ''))
            ->filter()
            ->all();

        collect((array) data_get($post?->meta, 'autobiography_achievements', []))
            ->each(function (mixed $entry) use ($keptPaths): void {
                $path = (string) data_get($entry, 'image.path', '');
                if (filled($path) && ! in_array($path, $keptPaths, true)) {
                    CommunityPostFileUploader::deleteIfExists($path);
                }
            });

        return $resolved;
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     */
    private function deleteAutobiographyAchievementImages(array $entries): void
    {
        foreach ($entries as $entry) {
            CommunityPostFileUploader::deleteIfExists((string) data_get($entry, 'image.path', ''));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyPoetryRegionalLocation(array $data): array
    {
        if (($data['content_type'] ?? null) !== 'poetry') {
            return $data;
        }

        $structuredFields = array_intersect_key(
            $data['meta'] ?? [],
            array_flip(CommunityPost::structuredLocationMetaKeys())
        );

        if (collect($structuredFields)->filter(fn (mixed $value): bool => filled($value))->isEmpty()) {
            return $data;
        }

        $data['location_type'] = CommunityPost::inferLocationTypeFromStructured($structuredFields);
        $data['location'] = CommunityPost::composeStructuredLocation($structuredFields);

        return $data;
    }

    /**
     * @param  array{type?: string, path?: string}|null  $audio
     */
    private function deleteStoryAudioFile(?array $audio): void
    {
        if (blank($audio['path'] ?? null)) {
            return;
        }

        CommunityPostFileUploader::deleteIfExists($audio['path']);
    }

    /**
     * @return array{type: string, url?: string, video_id?: string, path?: string, name?: string}|null
     */
    private function resolveVideo(Request $request, ?CommunityPost $post = null): ?array
    {
        if ($request->boolean('remove_video')) {
            $this->deleteVideoFile($post?->videoData());

            return null;
        }

        $sourceType = $request->input('video_source_type', 'none');

        if ($sourceType === 'youtube') {
            $url = trim((string) $request->input('video_youtube_url'));
            $videoId = CommunityPost::parseYoutubeVideoId($url);

            if ($post && ($post->videoData()['type'] ?? null) === 'upload') {
                $this->deleteVideoFile($post->videoData());
            }

            return [
                'type' => 'youtube',
                'url' => $url,
                'video_id' => $videoId,
            ];
        }

        if ($sourceType === 'upload' && $request->hasFile('video_file')) {
            $this->deleteVideoFile($post?->videoData());

            return $this->storeVideoFile($request->file('video_file'));
        }

        if ($sourceType === 'upload' && $request->boolean('keep_existing_video') && $post?->videoData()) {
            return $post->videoData();
        }

        if ($sourceType === 'none') {
            if ($post?->videoData()) {
                $this->deleteVideoFile($post->videoData());
            }

            return null;
        }

        return $post?->videoData();
    }

    /**
     * @return array{type: string, path: string, name: string}
     */
    private function storeVideoFile(\Illuminate\Http\UploadedFile $file): array
    {
        return CommunityPostFileUploader::storeVideo($file);
    }

    /**
     * @param  array{type: string, path?: string}|null  $video
     */
    private function deleteVideoFile(?array $video): void
    {
        if (($video['type'] ?? null) !== 'upload' || blank($video['path'] ?? null)) {
            return;
        }

        CommunityPostFileUploader::deleteIfExists($video['path']);
    }

    private function ensureCommunityAudienceAccess(CommunityPost $post, Request $request): void
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isVisibleInCommunityTo($request->user()), 403, 'This post is not available to your account.');
    }

    private function deleteFeaturedImage(?string $path): void
    {
        CommunityPostFileUploader::deleteIfExists($path);
    }

    private function paginateCommunityPosts(Request $request, ?User $author = null)
    {
        $query = CommunityPost::query()
            ->with('user')
            ->withCount(['reactions', 'comments', 'starRatings'])
            ->withAvg('starRatings', 'rating')
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->when($author, fn ($query) => $query
                ->where('user_id', $author->id)
                ->visibleOnAuthorProfile())
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->orderByDesc('is_featured')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_sponsored');

        CommunityEngagementController::applySubscriptionPriority($query, auth()->id());

        return $query
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
    }

    private function communityPostsAjaxResponse($posts): JsonResponse
    {
        return response()->json([
            'html' => view('community.partials.post-cards', [
                'posts' => $posts,
                'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
            ])->render(),
            'next_page_url' => $posts->nextPageUrl(),
            'loaded_to' => $posts->lastItem() ?? 0,
            'total' => $posts->total(),
        ]);
    }

    private function resolveAuthor(string $uniqueName): User
    {
        $author = User::query()->where('author_slug', $uniqueName)->first();

        if ($author) {
            return $author;
        }

        if (preg_match('/-(\d+)$/', $uniqueName, $matches)) {
            $author = User::query()->find((int) $matches[1]);
            if ($author && $author->authorUniqueName() === $uniqueName) {
                return $author;
            }
        }

        abort(404);
    }

    private function answeredQuestionsForPost(CommunityPost $post)
    {
        return CommunityAuthorQuestion::query()
            ->forAuthor($post->user_id)
            ->where('community_post_id', $post->id)
            ->answered()
            ->with(['asker:id,name,full_name'])
            ->latest('answered_at')
            ->get();
    }

    private function answeredQuestionsForAuthor(User $author)
    {
        return CommunityAuthorQuestion::query()
            ->forAuthor($author->id)
            ->answered()
            ->with(['asker:id,name,full_name', 'post:id,title,slug'])
            ->latest('answered_at')
            ->get();
    }

    private function recordPostView(Request $request, CommunityPost $post): void
    {
        $sessionKey = 'community_post_viewed_'.$post->id;

        if ($request->session()->has($sessionKey)) {
            return;
        }

        $post->increment('views_count');
        $request->session()->put($sessionKey, true);
    }

    private function authorizeOwner(Request $request, CommunityPost $post): void
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
    }

    /**
     * @return array{status: string, published_at: ?\Illuminate\Support\Carbon, submitted_at: ?\Illuminate\Support\Carbon, reviewed_at: null, reviewed_by: null, review_note: null}
     */
    private function resolvePublicationState(Request $request, ?CommunityPost $post = null): array
    {
        $requestedStatus = $request->input('status', CommunityPost::STATUS_PUBLISHED);
        $user = $request->user();

        if ($requestedStatus === CommunityPost::STATUS_DRAFT) {
            return [
                'status' => CommunityPost::STATUS_DRAFT,
                'published_at' => null,
                'submitted_at' => null,
                'reviewed_at' => null,
                'reviewed_by' => null,
                'review_note' => null,
            ];
        }

        if ($user->isAdmin()) {
            return [
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => $post?->published_at ?? now(),
                'submitted_at' => null,
                'reviewed_at' => $post?->reviewed_at,
                'reviewed_by' => $post?->reviewed_by,
                'review_note' => $post?->review_note,
            ];
        }

        return [
            'status' => CommunityPost::STATUS_PENDING,
            'published_at' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_note' => null,
        ];
    }

    private function notifyAdminsOfPendingPost(CommunityPost $post): void
    {
        PortalNotificationService::notifyAdminsOfApprovalRequest(
            'Community post',
            $post->title,
            route('admin.community-posts.show', $post)
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function mergeBookPagesIntoMeta(Request $request, array &$data): void
    {
        $contentType = $data['content_type'] ?? $request->input('content_type');

        if (! CommunityPost::isBookContentType(is_string($contentType) ? $contentType : null)) {
            return;
        }

        $pages = $this->normalizeBookPages(
            $request->input('book_pages', []),
            is_string($contentType) ? $contentType : null
        );

        if ($pages !== []) {
            $data['meta']['book_pages'] = $pages;
            $data['body'] = CommunityPost::bodyFromBookPages($pages);
        }
    }

    /**
     * @param  list<array{content?: string, language?: string, title?: string, summary?: string}>|list<string>  $pages
     * @return list<array{content: string, language: string, title?: string, summary?: string}>
     */
    private function normalizeBookPages(array $pages, ?string $contentType = null): array
    {
        $usesChapters = CommunityPost::usesChapterLayout($contentType);

        return collect($pages)
            ->map(function (mixed $page) use ($usesChapters): array {
                $normalized = [
                    'content' => is_array($page) ? (string) ($page['content'] ?? '') : (string) $page,
                    'language' => in_array(is_array($page) ? ($page['language'] ?? 'en') : 'en', ['en', 'hi'], true)
                        ? (is_array($page) ? ($page['language'] ?? 'en') : 'en')
                        : 'en',
                ];

                if ($usesChapters) {
                    $normalized['title'] = trim(is_array($page) ? (string) ($page['title'] ?? '') : '');
                    $normalized['summary'] = trim(is_array($page) ? (string) ($page['summary'] ?? '') : '');
                }

                return $normalized;
            })
            ->filter(function (array $page) use ($usesChapters): bool {
                if (filled(strip_tags($page['content']))) {
                    return true;
                }

                return $usesChapters && filled($page['title'] ?? null);
            })
            ->values()
            ->all();
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}|null
     */
    private function resolveChildrensCornerArt(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            return null;
        }

        $mode = CommunityContentTaxonomy::childrensCornerContentMode($request->input('child_share_type'));

        if ($mode !== 'image') {
            $existingPath = (string) data_get($post?->meta, 'childrens_corner_art.path');
            if (filled($existingPath)) {
                CommunityPostFileUploader::deleteIfExists($existingPath);
            }

            return null;
        }

        if ($request->hasFile('childrens_corner_art_file')) {
            $existingPath = (string) data_get($post?->meta, 'childrens_corner_art.path');
            if (filled($existingPath)) {
                CommunityPostFileUploader::deleteIfExists($existingPath);
            }

            return CommunityPostFileUploader::storeAttachment($request->file('childrens_corner_art_file'), 'childrens-corner-art');
        }

        if ($request->boolean('keep_existing_childrens_corner_art') && $post) {
            $existing = data_get($post->meta, 'childrens_corner_art');

            return is_array($existing) ? $existing : null;
        }

        $existingPath = (string) data_get($post?->meta, 'childrens_corner_art.path');
        if (filled($existingPath)) {
            CommunityPostFileUploader::deleteIfExists($existingPath);
        }

        return null;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveChildrensCornerProjectFiles(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            return null;
        }

        $mode = CommunityContentTaxonomy::childrensCornerContentMode($request->input('child_share_type'));

        if ($mode !== 'project') {
            foreach ((array) data_get($post?->meta, 'childrens_corner_project_files', []) as $file) {
                CommunityPostFileUploader::deleteIfExists(data_get($file, 'path'));
            }

            return null;
        }

        $existing = (array) data_get($post?->meta, 'childrens_corner_project_files', []);
        $keptPaths = (array) $request->input('keep_childrens_corner_project_files', []);

        $kept = collect($existing)
            ->filter(fn (mixed $file): bool => is_array($file) && in_array((string) data_get($file, 'path'), $keptPaths, true))
            ->values()
            ->all();

        foreach ($existing as $file) {
            $path = (string) data_get($file, 'path');
            if (filled($path) && ! in_array($path, $keptPaths, true)) {
                CommunityPostFileUploader::deleteIfExists($path);
            }
        }

        if ($request->hasFile('childrens_corner_project_files')) {
            foreach ($request->file('childrens_corner_project_files', []) as $file) {
                $kept[] = CommunityPostFileUploader::storeAttachment($file, 'childrens-corner-projects');
            }
        }

        return array_values(array_slice($kept, 0, self::MAX_CHILDRENS_CORNER_PROJECT_FILES));
    }

    /**
     * @return list<array{question: string, options: list<string>, correct_answer: string}>|null
     */
    private function resolveChildrensCornerQuiz(Request $request): ?array
    {
        if (! CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            return null;
        }

        if (CommunityContentTaxonomy::childrensCornerContentMode($request->input('child_share_type')) !== 'quiz') {
            return null;
        }

        $resolved = collect($request->input('childrens_corner_quiz', []))
            ->filter(fn (mixed $question): bool => is_array($question) && filled($question['question'] ?? null))
            ->map(function (array $question): array {
                $options = collect((array) ($question['options'] ?? []))
                    ->map(fn (mixed $option): string => trim((string) $option))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'question' => trim((string) ($question['question'] ?? '')),
                    'options' => $options,
                    'correct_answer' => trim((string) ($question['correct_answer'] ?? '')),
                ];
            })
            ->filter(fn (array $question): bool => $question['options'] !== [] && filled($question['correct_answer']))
            ->take(self::MAX_CHILDRENS_CORNER_QUIZ_QUESTIONS)
            ->values()
            ->all();

        return $resolved === [] ? null : $resolved;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeChildrensCornerGallery(Request $request): array
    {
        return collect($request->file('childrens_corner_gallery', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'childrens-corner-gallery'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveChildrensCornerGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'childrens_corner_gallery', []);
        $removed = (array) $request->input('removed_childrens_corner_gallery', []);

        if ($existing === [] && ! $request->hasFile('childrens_corner_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('childrens_corner_gallery')) {
            $kept = array_values(array_merge($kept, $this->storeChildrensCornerGallery($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_CHILDRENS_CORNER_GALLERY));
    }

    /**
     * @return array{type: string, url?: string, video_id?: string, path?: string, name?: string}|null
     */
    private function resolveChildrensCornerVideo(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_childrens_corner_video')) {
            $this->deleteVideoFile(data_get($post?->meta, 'childrens_corner_video'));

            return null;
        }

        $sourceType = $request->input('childrens_corner_video_source_type', 'none');

        if ($sourceType === 'youtube') {
            $url = trim((string) $request->input('childrens_corner_video_youtube_url'));
            $videoId = CommunityPost::parseYoutubeVideoId($url);

            if (data_get($post?->meta, 'childrens_corner_video.type') === 'upload') {
                $this->deleteVideoFile(data_get($post?->meta, 'childrens_corner_video'));
            }

            return [
                'type' => 'youtube',
                'url' => $url,
                'video_id' => $videoId,
            ];
        }

        if ($sourceType === 'upload' && $request->hasFile('childrens_corner_video_file')) {
            $this->deleteVideoFile(data_get($post?->meta, 'childrens_corner_video'));

            return $this->storeVideoFile($request->file('childrens_corner_video_file'));
        }

        if ($sourceType === 'upload' && $request->boolean('keep_existing_childrens_corner_video')) {
            $existing = data_get($post?->meta, 'childrens_corner_video');

            return is_array($existing) ? $existing : null;
        }

        if ($sourceType === 'none') {
            $this->deleteVideoFile(data_get($post?->meta, 'childrens_corner_video'));

            return null;
        }

        $existing = data_get($post?->meta, 'childrens_corner_video');

        return is_array($existing) ? $existing : null;
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}|null
     */
    private function resolveChildrensCornerAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_childrens_corner_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'childrens_corner_audio'));

            return null;
        }

        $sourceType = $request->input('childrens_corner_audio_source_type', 'none');

        if ($sourceType === 'upload' && $request->hasFile('childrens_corner_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'childrens_corner_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('childrens_corner_audio_file'), 'upload');
        }

        if ($sourceType === 'recording' && $request->hasFile('childrens_corner_audio_recording')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'childrens_corner_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('childrens_corner_audio_recording'), 'recording');
        }

        if ($request->boolean('keep_existing_childrens_corner_audio') && $post) {
            $existing = data_get($post->meta, 'childrens_corner_audio');

            return is_array($existing) ? $existing : null;
        }

        if ($sourceType === 'none') {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'childrens_corner_audio'));

            return null;
        }

        $existing = data_get($post?->meta, 'childrens_corner_audio');

        return is_array($existing) ? $existing : null;
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}|null
     */
    private function resolveChildrensCornerCertificate(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesChildrensCornerFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_childrens_corner_certificate')) {
            CommunityPostFileUploader::deleteIfExists(data_get($post?->meta, 'childrens_corner_certificate.path'));

            return null;
        }

        if ($request->hasFile('childrens_corner_certificate_file')) {
            $existingPath = (string) data_get($post?->meta, 'childrens_corner_certificate.path');
            if (filled($existingPath)) {
                CommunityPostFileUploader::deleteIfExists($existingPath);
            }

            return CommunityPostFileUploader::storeAttachment($request->file('childrens_corner_certificate_file'), 'childrens-corner-certificates');
        }

        if ($request->boolean('keep_existing_childrens_corner_certificate') && $post) {
            $existing = data_get($post->meta, 'childrens_corner_certificate');

            return is_array($existing) ? $existing : null;
        }

        $existingPath = (string) data_get($post?->meta, 'childrens_corner_certificate.path');
        if (filled($existingPath)) {
            CommunityPostFileUploader::deleteIfExists($existingPath);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyChildrensCornerBroadLocation(array $validated, Request $request): array
    {
        $city = trim((string) $request->input('childrens_corner_city'));
        $district = trim((string) $request->input('childrens_corner_district'));
        $state = trim((string) $request->input('childrens_corner_state'));
        $parts = array_values(array_filter([$city, $district, $state]));

        if ($parts !== []) {
            $validated['location'] = implode(', ', $parts);
            $validated['location_type'] = filled($city)
                ? CommunityPost::LOCATION_TYPE_CITY
                : (filled($district) ? CommunityPost::LOCATION_TYPE_DISTRICT : CommunityPost::LOCATION_TYPE_STATE);
        } else {
            $validated = array_merge($validated, CommunityPost::defaultLocationForType(CommunityPost::LOCATION_TYPE_GLOBAL));
        }

        $validated['location_lat'] = null;
        $validated['location_lng'] = null;

        return $validated;
    }

    private function syncReportTrustScore(CommunityPost $post): CommunityPost
    {
        if (! $post->isReportContent()) {
            return $post;
        }

        return CommunityReportTrustScoreService::syncToMeta($post);
    }
}
