<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Community\CommunityEngagementController;
use App\Models\CommunityAuthorQuestion;
use App\Models\CommunityCategorySubscription;
use App\Models\CommunityPost;
use App\Models\CommunityPostParticipation;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostPollVote;
use App\Models\CommunityPostStarRating;
use App\Models\CommunityPostReaction;
use App\Models\User;
use App\Services\CommunityCommunityIssuesEngagementNotificationService;
use App\Services\CommunityAgricultureEngagementNotificationService;
use App\Services\CommunityAstroConsultancyEngagementNotificationService;
use App\Services\CommunityCompetitionsEngagementNotificationService;
use App\Services\CommunityCreativeCornerEngagementNotificationService;
use App\Services\CommunityReligionSpiritualityEngagementNotificationService;
use App\Services\CommunityEnvironmentEngagementNotificationService;
use App\Services\CommunityPostParticipationNotificationService;
use App\Services\CommunityReportEngagementNotificationService;
use App\Services\CommunityStoryAchievementService;
use App\Services\CommunityStoryEngagementNotificationService;
use App\Services\CommunityEngagementNotificationService;
use App\Services\CommunitySeniorCitizensForumEngagementNotificationService;
use App\Services\CommunityStudentCornerEngagementNotificationService;
use App\Services\CommunityYouthCornerEngagementNotificationService;
use App\Services\CommunityWomensWorldEngagementNotificationService;
use App\Services\FoulWordFilter;
use App\Services\CommunityArticleScoreService;
use App\Services\CommunityReportTrustScoreService;
use App\Services\PortalNotificationService;
use App\Support\CommunityContentTaxonomy;
use App\Support\CommunityPostAuditLogger;
use App\Support\CommunityPostFileUploader;
use App\Support\CommunityPostFormFields;
use App\Support\KrutiDevToUnicode;
use App\Support\UserFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

    private const MAX_SENIOR_CITIZENS_FORUM_ACHIEVEMENTS = 15;

    private const MAX_AUTOBIOGRAPHY_DOCUMENTS = 10;

    private const MAX_AWARENESS_INFOGRAPHICS = 10;

    private const MAX_AWARENESS_DOCUMENTS = 6;

    private const MAX_BUSINESS_DOCUMENTS = 6;

    private const MAX_STUDENT_CORNER_DOCUMENTS = 6;

    private const MAX_STUDENT_CORNER_GALLERY = 10;

    private const MAX_STUDENT_CORNER_ACHIEVEMENTS = 10;

    private const MAX_YOUTH_CORNER_DOCUMENTS = 6;

    private const MAX_YOUTH_CORNER_GALLERY = 10;

    private const MAX_YOUTH_CORNER_ACHIEVEMENTS = 10;

    private const MAX_LOCAL_VOICE_PHOTO_EVIDENCE = 10;

    private const MAX_LOCAL_VOICE_DOCUMENTS = 6;

    private const MAX_LOCAL_VOICE_HERO_IMAGES = 6;

    private const MAX_MY_AREA_PHOTO_EVIDENCE = 10;

    private const MAX_MY_AREA_DOCUMENTS = 6;

    private const MAX_MY_AREA_HERO_IMAGES = 6;

    private const MAX_COMMUNITY_ISSUE_PHOTO_EVIDENCE = 10;

    private const MAX_COMMUNITY_ISSUE_DOCUMENTS = 6;

    private const MAX_AGRICULTURE_PROBLEM_PHOTOS = 10;

    private const MAX_AGRICULTURE_GALLERY_PER_CATEGORY = 6;

    private const MAX_AGRICULTURE_DOCUMENTS = 8;

    private const MAX_ENVIRONMENT_GALLERY_PER_CATEGORY = 6;

    private const MAX_ENVIRONMENT_DOCUMENTS = 8;

    private const MAX_SCIENCE_TECHNOLOGY_GALLERY_PER_CATEGORY = 6;

    private const MAX_SCIENCE_TECHNOLOGY_DOCUMENTS = 8;

    private const MAX_ASTRO_CONSULTANCY_DOCUMENTS = 8;

    private const MAX_RELIGION_SPIRITUALITY_DOCUMENTS = 8;

    private const MAX_RELIGION_SPIRITUALITY_GALLERY = 10;

    private const MAX_CREATIVE_CORNER_DOCUMENTS = 8;

    private const MAX_CREATIVE_CORNER_GALLERY = 12;

    private const MAX_COMPETITIONS_JURY = 10;

    private const MAX_COMPETITIONS_SPONSORS = 10;

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
        $listingHighlights = $this->communityListingHighlights($request);
        $posts = $this->paginateCommunityPosts($request, null, array_keys($listingHighlights));

        if ($request->ajax()) {
            return $this->communityPostsAjaxResponse($posts, $request, $listingHighlights);
        }

        $isAllPostsView = CommunityContentTaxonomy::isAllPostsListing($request);
        $viewData = [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::formTypes(),
            'hubSections' => CommunityContentTaxonomy::hubSections(),
            'activeType' => $request->string('type')->toString(),
            'activeHub' => CommunityContentTaxonomy::resolveCommunityListingHub(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null
            ),
            'activeCategory' => $request->string('category')->toString(),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
            'isAllPostsView' => $isAllPostsView,
            'listingHighlights' => $listingHighlights,
            ...$this->hubLandingExtras(),
        ];

        if (CommunityContentTaxonomy::shouldUsePortalListing(
            $viewData['activeType'],
            $viewData['activeHub'],
            isset($activeAuthor),
            $isAllPostsView
        )) {
            $portalScope = CommunityContentTaxonomy::resolvePortalScope($viewData['activeType'], $viewData['activeHub']);
            $viewData['contentPortal'] = $this->buildContentPortalData($request, $posts, $portalScope['content_types']);
            $viewData['portalKey'] = $portalScope['portal_key'];
        }

        return view('community.index', $viewData);
    }

    public function author(Request $request, string $uniqueName): View|JsonResponse
    {
        $author = $this->resolveAuthor($uniqueName);
        $listingHighlights = $this->communityListingHighlights($request, $author);
        $posts = $this->paginateCommunityPosts($request, $author, array_keys($listingHighlights));

        if ($request->ajax()) {
            return $this->communityPostsAjaxResponse($posts, $request, $listingHighlights);
        }

        $viewData = [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::formTypes(),
            'hubSections' => CommunityContentTaxonomy::hubSections(),
            'activeType' => $request->string('type')->toString(),
            'activeHub' => CommunityContentTaxonomy::resolveCommunityListingHub(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null,
                true
            ),
            'activeCategory' => $request->string('category')->toString(),
            'activeAuthor' => $author,
            'answeredAuthorQuestions' => $this->answeredQuestionsForAuthor($author),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
            'isAllPostsView' => false,
            'listingHighlights' => $listingHighlights,
            ...$this->hubLandingExtras(),
        ];

        if (CommunityContentTaxonomy::shouldUsePortalListing(
            $viewData['activeType'],
            $viewData['activeHub'],
            true
        )) {
            $portalScope = CommunityContentTaxonomy::resolvePortalScope($viewData['activeType'], $viewData['activeHub']);
            $viewData['contentPortal'] = $this->buildContentPortalData($request, $posts, $portalScope['content_types']);
            $viewData['portalKey'] = $portalScope['portal_key'];
        }

        return view('community.index', $viewData);
    }

    public function show(Request $request, CommunityPost $post): View
    {
        $viewer = auth()->user();
        $canManagePreview = $viewer !== null && ($viewer->id === $post->user_id || $viewer->isAdmin());
        $privateLinkAccess = $post->allowsWomensWorldPrivateLinkAccess($request->query('access'))
            || $post->allowsSeniorCitizensForumPrivateLinkAccess($request->query('access'))
            || $post->allowsStudentCornerPrivateLinkAccess($request->query('access'))
            || $post->allowsYouthCornerPrivateLinkAccess($request->query('access'))
            || $post->allowsLocalVoicePrivateLinkAccess($request->query('access'))
            || $post->allowsMyAreaPrivateLinkAccess($request->query('access'))
            || $post->allowsCommunityIssuePrivateLinkAccess($request->query('access'));

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
        ])->loadCount(['starRatings', 'awarenessSupports', 'awarenessPledges', 'awarenessVolunteers', 'businessQueries', 'localVoiceSupports', 'localVoiceFollows', 'environmentSupports', 'environmentFollows', 'environmentVolunteers', 'astroConsultancyPrivateQueries']);

        if ($post->isPubliclyVisible()) {
            $this->recordPostView($request, $post);
        }

        $viewData = [
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'answeredAuthorQuestions' => $post->user_id
                ? $this->answeredQuestionsForPost($post)
                : collect(),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ];

        if (CommunityContentTaxonomy::usesContentPortal($post->content_type)) {
            $viewData = array_merge($viewData, $this->hubLandingExtras());
            $viewData['relatedPortalPosts'] = $this->relatedPortalPosts($post);
            $viewData['trendingPortalPosts'] = $this->trendingPortalPosts($post);
            $viewData['featuredPortalPosts'] = $this->featuredPortalPosts($post);
            $viewData['activeHub'] = CommunityContentTaxonomy::hubSectionForType($post->content_type);
            $viewData['activeCategory'] = (string) ($post->category ?? '');
            $viewData['authorPostCount'] = $post->user_id
                ? CommunityPost::query()
                    ->where('user_id', $post->user_id)
                    ->publiclyListed()
                    ->count()
                : 0;
        }

        return view('community.show', array_merge($viewData, $this->participationViewData($post)));
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
            'environmentSupports',
            'environmentFollows',
            'environmentVolunteers',
            'astroConsultancyPrivateQueries',
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
            'reportEngagement' => $post->supportsCivicEngagement()
                ? CommunityReportEngagementNotificationService::stateForPost($post, auth()->id())
                : null,
            'reportEngagementActivity' => $post->supportsCivicEngagement()
                ? [
                    'supports' => $post->reportSupports()->with('user:id,name,full_name')->latest()->limit(10)->get(),
                    'agreements' => $post->reportAgreements()->with('user:id,name,full_name')->latest()->limit(10)->get(),
                    'follows' => $post->reportFollows()->with('user:id,name,full_name')->latest()->limit(10)->get(),
                ]
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
            'environmentEngagement' => $post->isEnvironmentPost()
                ? \App\Services\CommunityEnvironmentEngagementService::stateForPost($post, auth()->id())
                : null,
            'environmentEngagementActivity' => $post->isEnvironmentPost()
                ? \App\Services\CommunityEnvironmentEngagementService::activityForPost($post)
                : null,
            'astroConsultancyEngagement' => $post->isAstroConsultancyPost()
                ? \App\Services\CommunityAstroConsultancyEngagementService::stateForPost($post, auth()->id())
                : null,
            'astroConsultancyEngagementActivity' => $post->isAstroConsultancyPost()
                ? \App\Services\CommunityAstroConsultancyEngagementService::activityForPost($post)
                : null,
            'businessEngagement' => $post->isBusinessPost()
                ? \App\Services\CommunityBusinessEngagementService::stateForPost($post, auth()->id())
                : null,
            'businessEngagementActivity' => $post->isBusinessPost()
                ? \App\Services\CommunityBusinessEngagementService::activityForPost($post)
                : null,
            'localVoiceEngagement' => $post->isLocalVoicesPost()
                ? \App\Services\CommunityLocalVoiceEngagementService::stateForPost($post, auth()->id())
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

        if ($post->isSeniorCitizensForumPost() && $active) {
            CommunitySeniorCitizensForumEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($post->isStudentCornerPost() && $active) {
            CommunityStudentCornerEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($post->isYouthCornerPost() && $active) {
            CommunityYouthCornerEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($post->isAgriculturePost() && $active) {
            CommunityAgricultureEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($post->isEnvironmentPost() && $active) {
            CommunityEnvironmentEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($post->isAstroConsultancyPost() && $active) {
            CommunityAstroConsultancyEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($post->isReligionSpiritualityPost() && $active) {
            CommunityReligionSpiritualityEngagementNotificationService::notifyAuthorOfReaction(
                $post,
                $request->user(),
                $data['reaction']
            );
        }

        if ($post->isCreativeCornerPost() && $active) {
            CommunityCreativeCornerEngagementNotificationService::notifyAuthorOfReaction(
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

        if ($post->isAgriculturePost()) {
            CommunityAgricultureEngagementNotificationService::notifyAuthorOfCommunityResponse(
                $post,
                $request->user(),
                $data['body'],
                filled($data['parent_id'] ?? null)
            );
        }

        if ($post->isEnvironmentPost()) {
            CommunityEnvironmentEngagementNotificationService::notifyAuthorOfCommunityResponse(
                $post,
                $request->user(),
                $data['body'],
                filled($data['parent_id'] ?? null)
            );
        }

        if ($post->isAstroConsultancyPost()) {
            CommunityAstroConsultancyEngagementNotificationService::notifyAuthorOfCommunityResponse(
                $post,
                $request->user(),
                $data['body'],
                filled($data['parent_id'] ?? null)
            );
        }

        if ($post->isReligionSpiritualityPost()) {
            CommunityReligionSpiritualityEngagementNotificationService::notifyAuthorOfCommunityResponse(
                $post,
                $request->user(),
                $data['body'],
                filled($data['parent_id'] ?? null)
            );
        }

        if ($post->isCreativeCornerPost()) {
            CommunityCreativeCornerEngagementNotificationService::notifyAuthorOfCommunityResponse(
                $post,
                $request->user(),
                $data['body'],
                filled($data['parent_id'] ?? null)
            );
        }

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
                'meta',
                'title',
                'status',
                'published_at',
                'submitted_at',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('type_label', fn (CommunityPost $post): string => e($post->typeLabel()))
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

        foreach ((array) data_get($post->meta, 'student_corner_documents', []) as $document) {
            CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
        }

        foreach ((array) data_get($post->meta, 'student_corner_gallery', []) as $image) {
            CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
        }

        $this->deleteStudentCornerAchievementFiles((array) data_get($post->meta, 'student_corner_achievements', []));

        foreach ((array) data_get($post->meta, 'youth_corner_documents', []) as $document) {
            CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
        }

        foreach ((array) data_get($post->meta, 'youth_corner_gallery', []) as $image) {
            CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
        }

        $this->deleteYouthCornerAchievementFiles((array) data_get($post->meta, 'youth_corner_achievements', []));

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
        $this->deleteStoryAudioFile(data_get($post->meta, 'senior_citizens_forum_audio'));
        $this->deleteSeniorCitizensForumAchievementFiles((array) data_get($post->meta, 'senior_citizens_forum_achievements', []));

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

    public function create(Request $request): View
    {
        $post = new CommunityPost([
            'status' => CommunityPost::STATUS_PUBLISHED,
            'allow_comments' => true,
            'allow_questions' => true,
            'allow_sharing' => true,
            'allow_poll' => false,
        ]);

        $lockedContentType = null;
        $requestedType = $request->string('type')->toString();
        if ($requestedType !== '' && array_key_exists($requestedType, CommunityContentTaxonomy::formTypes())) {
            $post->content_type = $requestedType;
            $lockedContentType = $requestedType;
        }

        return view('backend.community-posts.form', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'mode' => 'create',
            'lockedContentType' => $lockedContentType,
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
        $data['meta'] = $this->applySeniorCitizensForumPrivacyMeta($data['meta'], $request);
        $data['meta'] = $this->applyStudentCornerPrivacyMeta($data['meta'], $request);
        $data['meta'] = $this->applyYouthCornerPrivacyMeta($data['meta'], $request);
        $data['meta'] = $this->applyLocalVoicePrivacyMeta($data['meta'], $request);
        $data['meta'] = $this->applyMyAreaPrivacyMeta($data['meta'], $request);
        $data['meta'] = $this->applyCommunityIssuePrivacyMeta($data['meta'], $request);
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
        $studentCornerDocuments = $this->resolveStudentCornerDocuments($request);
        if ($studentCornerDocuments !== null) {
            $data['meta']['student_corner_documents'] = $studentCornerDocuments;
        }
        $studentCornerGallery = $this->resolveStudentCornerGallery($request);
        if ($studentCornerGallery !== null) {
            $data['meta']['student_corner_gallery'] = $studentCornerGallery;
        }
        $studentCornerAchievements = $this->resolveStudentCornerAchievements($request);
        if ($studentCornerAchievements !== null) {
            $data['meta']['student_corner_achievements'] = $studentCornerAchievements;
        }
        $youthCornerDocuments = $this->resolveYouthCornerDocuments($request);
        if ($youthCornerDocuments !== null) {
            $data['meta']['youth_corner_documents'] = $youthCornerDocuments;
        }
        $youthCornerGallery = $this->resolveYouthCornerGallery($request);
        if ($youthCornerGallery !== null) {
            $data['meta']['youth_corner_gallery'] = $youthCornerGallery;
        }
        $youthCornerAchievements = $this->resolveYouthCornerAchievements($request);
        if ($youthCornerAchievements !== null) {
            $data['meta']['youth_corner_achievements'] = $youthCornerAchievements;
        }
        $localVoicePhotoEvidence = $this->resolveLocalVoicePhotoEvidence($request);
        if ($localVoicePhotoEvidence !== null) {
            $data['meta']['local_voice_photo_evidence'] = $localVoicePhotoEvidence;
        }
        $localVoiceDocuments = $this->resolveLocalVoiceDocuments($request);
        if ($localVoiceDocuments !== null) {
            $data['meta']['local_voice_documents'] = $localVoiceDocuments;
        }
        $localVoiceHeroImages = $this->resolveLocalVoiceHeroImages($request);
        if ($localVoiceHeroImages !== null) {
            $data['meta']['local_voice_hero_images'] = $localVoiceHeroImages;
        }
        $myAreaPhotoEvidence = $this->resolveMyAreaPhotoEvidence($request);
        if ($myAreaPhotoEvidence !== null) {
            $data['meta']['my_area_photo_evidence'] = $myAreaPhotoEvidence;
        }
        $myAreaDocuments = $this->resolveMyAreaDocuments($request);
        if ($myAreaDocuments !== null) {
            $data['meta']['my_area_documents'] = $myAreaDocuments;
        }
        $myAreaHeroImages = $this->resolveMyAreaHeroImages($request);
        if ($myAreaHeroImages !== null) {
            $data['meta']['my_area_hero_images'] = $myAreaHeroImages;
        }
        $communityIssuePhotoEvidence = $this->resolveCommunityIssuePhotoEvidence($request);
        if ($communityIssuePhotoEvidence !== null) {
            $data['meta']['community_issue_photo_evidence'] = $communityIssuePhotoEvidence;
        }
        $communityIssueDocuments = $this->resolveCommunityIssueDocuments($request);
        if ($communityIssueDocuments !== null) {
            $data['meta']['community_issue_documents'] = $communityIssueDocuments;
        }
        $agricultureProblemPhotos = $this->resolveAgricultureProblemPhotos($request);
        if ($agricultureProblemPhotos !== null) {
            $data['meta']['agriculture_problem_photos'] = $agricultureProblemPhotos;
        }
        $agricultureGallery = $this->resolveAgricultureGallery($request);
        if ($agricultureGallery !== null) {
            $data['meta']['agriculture_gallery'] = $agricultureGallery;
        }
        $agricultureDocuments = $this->resolveAgricultureDocuments($request);
        if ($agricultureDocuments !== null) {
            $data['meta']['agriculture_documents'] = $agricultureDocuments;
        }
        $environmentGallery = $this->resolveEnvironmentGallery($request);
        if ($environmentGallery !== null) {
            $data['meta']['environment_gallery'] = $environmentGallery;
        }
        $environmentDocuments = $this->resolveEnvironmentDocuments($request);
        if ($environmentDocuments !== null) {
            $data['meta']['environment_documents'] = $environmentDocuments;
        }
        $scienceTechnologyGallery = $this->resolveScienceTechnologyGallery($request);
        if ($scienceTechnologyGallery !== null) {
            $data['meta']['science_technology_gallery'] = $scienceTechnologyGallery;
        }
        $scienceTechnologyDocuments = $this->resolveScienceTechnologyDocuments($request);
        if ($scienceTechnologyDocuments !== null) {
            $data['meta']['science_technology_documents'] = $scienceTechnologyDocuments;
        }
        $astroConsultancyDocuments = $this->resolveAstroConsultancyDocuments($request);
        if ($astroConsultancyDocuments !== null) {
            $data['meta']['astro_consultancy_documents'] = $astroConsultancyDocuments;
        }
        $religionSpiritualityGallery = $this->resolveReligionSpiritualityGallery($request);
        if ($religionSpiritualityGallery !== null) {
            $data['meta']['religion_spirituality_gallery'] = $religionSpiritualityGallery;
        }
        $religionSpiritualityDocuments = $this->resolveReligionSpiritualityDocuments($request);
        if ($religionSpiritualityDocuments !== null) {
            $data['meta']['religion_spirituality_documents'] = $religionSpiritualityDocuments;
        }
        $religionSpiritualityAudio = $this->resolveReligionSpiritualityAudio($request);
        if ($religionSpiritualityAudio !== null) {
            $data['meta']['religion_spirituality_audio'] = $religionSpiritualityAudio;
        } elseif (CommunityPost::usesReligionSpiritualityFlow($request->input('content_type')) && $request->boolean('remove_religion_spirituality_audio')) {
            unset($data['meta']['religion_spirituality_audio']);
        }
        $creativeCornerGallery = $this->resolveCreativeCornerGallery($request);
        if ($creativeCornerGallery !== null) {
            $data['meta']['creative_corner_gallery'] = $creativeCornerGallery;
        }
        $creativeCornerDocuments = $this->resolveCreativeCornerDocuments($request);
        if ($creativeCornerDocuments !== null) {
            $data['meta']['creative_corner_documents'] = $creativeCornerDocuments;
        }
        $creativeCornerAudio = $this->resolveCreativeCornerAudio($request);
        if ($creativeCornerAudio !== null) {
            $data['meta']['creative_corner_audio'] = $creativeCornerAudio;
        } elseif (CommunityPost::usesCreativeCornerFlow($request->input('content_type')) && $request->boolean('remove_creative_corner_audio')) {
            unset($data['meta']['creative_corner_audio']);
        }
        $competitionsOrganizerLogo = $this->resolveCompetitionsOrganizerLogo($request);
        if ($competitionsOrganizerLogo !== null) {
            $data['meta']['competitions_organizer_logo'] = $competitionsOrganizerLogo;
        } elseif (CommunityPost::usesCompetitionsFlow($request->input('content_type')) && $request->boolean('removed_competitions_organizer_logo')) {
            unset($data['meta']['competitions_organizer_logo']);
        }
        $competitionsJury = $this->resolveCompetitionsJury($request);
        if ($competitionsJury !== null) {
            $data['meta']['competitions_jury'] = $competitionsJury;
        }
        $competitionsSponsors = $this->resolveCompetitionsSponsors($request);
        if ($competitionsSponsors !== null) {
            $data['meta']['competitions_sponsors'] = $competitionsSponsors;
        }
        $scienceTechnologySourceCode = $this->resolveScienceTechnologySingleAttachment(
            $request,
            null,
            'science_technology_source_code',
            'science_technology_source_code',
            'science-technology-source-code',
            'removed_science_technology_source_code'
        );
        if ($scienceTechnologySourceCode !== null) {
            $data['meta']['science_technology_source_code'] = $scienceTechnologySourceCode;
        }
        $scienceTechnologyCircuitDiagram = $this->resolveScienceTechnologySingleAttachment(
            $request,
            null,
            'science_technology_circuit_diagram',
            'science_technology_circuit_diagram',
            'science-technology-circuit-diagram',
            'removed_science_technology_circuit_diagram'
        );
        if ($scienceTechnologyCircuitDiagram !== null) {
            $data['meta']['science_technology_circuit_diagram'] = $scienceTechnologyCircuitDiagram;
        }
        $scienceTechnologyPcbDesign = $this->resolveScienceTechnologySingleAttachment(
            $request,
            null,
            'science_technology_pcb_design',
            'science_technology_pcb_design',
            'science-technology-pcb-design',
            'removed_science_technology_pcb_design'
        );
        if ($scienceTechnologyPcbDesign !== null) {
            $data['meta']['science_technology_pcb_design'] = $scienceTechnologyPcbDesign;
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
        $seniorCitizensForumAudio = $this->resolveSeniorCitizensForumAudio($request);
        if ($seniorCitizensForumAudio !== null || ($request->input('content_type') === 'senior-citizens-forum' && $request->input('senior_citizens_forum_audio_source_type') === 'none')) {
            if ($seniorCitizensForumAudio !== null) {
                $data['meta']['senior_citizens_forum_audio'] = $seniorCitizensForumAudio;
            } else {
                unset($data['meta']['senior_citizens_forum_audio']);
            }
        }
        $seniorCitizensForumAchievements = $this->resolveSeniorCitizensForumAchievements($request);
        if ($seniorCitizensForumAchievements !== null) {
            $data['meta']['senior_citizens_forum_achievements'] = $seniorCitizensForumAchievements;
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
        } elseif ($post->isSeniorCitizensForumPost() && $post->isPubliclyVisible()) {
            CommunitySeniorCitizensForumEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
        } elseif ($post->isStudentCornerPost() && $post->isPubliclyVisible()) {
            CommunityStudentCornerEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
        } elseif ($post->isYouthCornerPost() && $post->isPubliclyVisible()) {
            CommunityYouthCornerEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
        } elseif (in_array($post->content_type, ['poetry', 'biography', 'autobiography'], true) && $post->isPubliclyVisible()) {
            CommunityStoryEngagementNotificationService::notifyAuthorOfPublishedWithoutAudio($post->fresh());
        } elseif ($post->isAgriculturePost() && $post->isPubliclyVisible()) {
            CommunityAgricultureEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        } elseif ($post->isEnvironmentPost() && $post->isPubliclyVisible()) {
            CommunityEnvironmentEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        } elseif ($post->isAstroConsultancyPost() && $post->isPubliclyVisible()) {
            CommunityAstroConsultancyEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        } elseif ($post->isReligionSpiritualityPost() && $post->isPubliclyVisible()) {
            CommunityReligionSpiritualityEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        } elseif ($post->isCreativeCornerPost() && $post->isPubliclyVisible()) {
            CommunityCreativeCornerEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        } elseif ($post->isCompetitionsPost() && $post->isPubliclyVisible()) {
            CommunityCompetitionsEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        }

        $message = $post->status === CommunityPost::STATUS_DRAFT
            ? 'Post saved successfully. You can publish it later from My Posts.'
            : ($post->isPendingApproval()
                ? 'Community post submitted for admin approval.'
                : 'Community post created successfully.');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => $post->status === CommunityPost::STATUS_DRAFT
                    ? route('community.posts.index')
                    : route('community.posts.show', $post),
            ]);
        }

        return redirect()
            ->to($post->status === CommunityPost::STATUS_DRAFT ? route('community.posts.index') : route('community.posts.show', $post))
            ->with('success', $message);
    }

    public function edit(Request $request, CommunityPost $post): View
    {
        $this->authorizeOwner($request, $post);

        return view('backend.community-posts.form', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::editableTypes($post),
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
        $data['meta'] = $this->applySeniorCitizensForumPrivacyMeta($data['meta'], $request, $post);
        $data['meta'] = $this->applyStudentCornerPrivacyMeta($data['meta'], $request, $post);
        $data['meta'] = $this->applyYouthCornerPrivacyMeta($data['meta'], $request, $post);
        $data['meta'] = $this->applyLocalVoicePrivacyMeta($data['meta'], $request, $post);
        $data['meta'] = $this->applyMyAreaPrivacyMeta($data['meta'], $request, $post);
        $data['meta'] = $this->applyCommunityIssuePrivacyMeta($data['meta'], $request, $post);
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

        $studentCornerDocuments = $this->resolveStudentCornerDocuments($request, $post);
        if ($studentCornerDocuments !== null) {
            $data['meta']['student_corner_documents'] = $studentCornerDocuments;
        } elseif (data_get($post->meta, 'student_corner_documents')) {
            foreach ((array) data_get($post->meta, 'student_corner_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['student_corner_documents']);
        }

        if (CommunityPost::usesStudentCornerFlow($request->input('content_type'))
            && $request->input('student_corner_content_type') !== CommunityContentTaxonomy::studentCornerProjectContentType()) {
            foreach ([
                'student_corner_project_title',
                'student_corner_project_category',
                'student_corner_project_description',
                'student_corner_project_outcome',
            ] as $projectMetaKey) {
                unset($data['meta'][$projectMetaKey]);
            }
        }

        $studentCornerGallery = $this->resolveStudentCornerGallery($request, $post);
        if ($studentCornerGallery !== null) {
            $data['meta']['student_corner_gallery'] = $studentCornerGallery;
        } elseif (data_get($post->meta, 'student_corner_gallery')) {
            foreach ((array) data_get($post->meta, 'student_corner_gallery', []) as $image) {
                CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
            }
            unset($data['meta']['student_corner_gallery']);
        }

        $studentCornerAchievements = $this->resolveStudentCornerAchievements($request, $post);
        if ($studentCornerAchievements !== null) {
            $data['meta']['student_corner_achievements'] = $studentCornerAchievements;
        } elseif (data_get($post->meta, 'student_corner_achievements')) {
            $this->deleteStudentCornerAchievementFiles((array) data_get($post->meta, 'student_corner_achievements', []));
            unset($data['meta']['student_corner_achievements']);
        }

        $youthCornerDocuments = $this->resolveYouthCornerDocuments($request, $post);
        if ($youthCornerDocuments !== null) {
            $data['meta']['youth_corner_documents'] = $youthCornerDocuments;
        } elseif (data_get($post->meta, 'youth_corner_documents')) {
            foreach ((array) data_get($post->meta, 'youth_corner_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['youth_corner_documents']);
        }

        if (CommunityPost::usesYouthCornerFlow($request->input('content_type'))
            && $request->input('youth_corner_content_type') !== CommunityContentTaxonomy::youthCornerProjectContentType()) {
            foreach ([
                'youth_corner_project_title',
                'youth_corner_project_category',
                'youth_corner_project_description',
                'youth_corner_project_outcome',
            ] as $projectMetaKey) {
                unset($data['meta'][$projectMetaKey]);
            }
        }

        $youthCornerGallery = $this->resolveYouthCornerGallery($request, $post);
        if ($youthCornerGallery !== null) {
            $data['meta']['youth_corner_gallery'] = $youthCornerGallery;
        } elseif (data_get($post->meta, 'youth_corner_gallery')) {
            foreach ((array) data_get($post->meta, 'youth_corner_gallery', []) as $image) {
                CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
            }
            unset($data['meta']['youth_corner_gallery']);
        }

        $youthCornerAchievements = $this->resolveYouthCornerAchievements($request, $post);
        if ($youthCornerAchievements !== null) {
            $data['meta']['youth_corner_achievements'] = $youthCornerAchievements;
        } elseif (data_get($post->meta, 'youth_corner_achievements')) {
            $this->deleteYouthCornerAchievementFiles((array) data_get($post->meta, 'youth_corner_achievements', []));
            unset($data['meta']['youth_corner_achievements']);
        }

        $localVoicePhotoEvidence = $this->resolveLocalVoicePhotoEvidence($request, $post);
        if ($localVoicePhotoEvidence !== null) {
            $data['meta']['local_voice_photo_evidence'] = $localVoicePhotoEvidence;
        } elseif (data_get($post->meta, 'local_voice_photo_evidence')) {
            foreach ((array) data_get($post->meta, 'local_voice_photo_evidence', []) as $photo) {
                CommunityPostFileUploader::deleteIfExists(data_get($photo, 'path'));
            }
            unset($data['meta']['local_voice_photo_evidence']);
        }

        $localVoiceDocuments = $this->resolveLocalVoiceDocuments($request, $post);
        if ($localVoiceDocuments !== null) {
            $data['meta']['local_voice_documents'] = $localVoiceDocuments;
        } elseif (data_get($post->meta, 'local_voice_documents')) {
            foreach ((array) data_get($post->meta, 'local_voice_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['local_voice_documents']);
        }

        $localVoiceHeroImages = $this->resolveLocalVoiceHeroImages($request, $post);
        if ($localVoiceHeroImages !== null) {
            $data['meta']['local_voice_hero_images'] = $localVoiceHeroImages;
        } elseif (data_get($post->meta, 'local_voice_hero_images')) {
            foreach ((array) data_get($post->meta, 'local_voice_hero_images', []) as $image) {
                CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
            }
            unset($data['meta']['local_voice_hero_images']);
        }

        $myAreaPhotoEvidence = $this->resolveMyAreaPhotoEvidence($request, $post);
        if ($myAreaPhotoEvidence !== null) {
            $data['meta']['my_area_photo_evidence'] = $myAreaPhotoEvidence;
        } elseif (data_get($post->meta, 'my_area_photo_evidence')) {
            foreach ((array) data_get($post->meta, 'my_area_photo_evidence', []) as $photo) {
                CommunityPostFileUploader::deleteIfExists(data_get($photo, 'path'));
            }
            unset($data['meta']['my_area_photo_evidence']);
        }

        $myAreaDocuments = $this->resolveMyAreaDocuments($request, $post);
        if ($myAreaDocuments !== null) {
            $data['meta']['my_area_documents'] = $myAreaDocuments;
        } elseif (data_get($post->meta, 'my_area_documents')) {
            foreach ((array) data_get($post->meta, 'my_area_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['my_area_documents']);
        }

        $myAreaHeroImages = $this->resolveMyAreaHeroImages($request, $post);
        if ($myAreaHeroImages !== null) {
            $data['meta']['my_area_hero_images'] = $myAreaHeroImages;
        } elseif (data_get($post->meta, 'my_area_hero_images')) {
            foreach ((array) data_get($post->meta, 'my_area_hero_images', []) as $image) {
                CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
            }
            unset($data['meta']['my_area_hero_images']);
        }

        $communityIssuePhotoEvidence = $this->resolveCommunityIssuePhotoEvidence($request, $post);
        if ($communityIssuePhotoEvidence !== null) {
            $data['meta']['community_issue_photo_evidence'] = $communityIssuePhotoEvidence;
        } elseif (data_get($post->meta, 'community_issue_photo_evidence')) {
            foreach ((array) data_get($post->meta, 'community_issue_photo_evidence', []) as $photo) {
                CommunityPostFileUploader::deleteIfExists(data_get($photo, 'path'));
            }
            unset($data['meta']['community_issue_photo_evidence']);
        }

        $communityIssueDocuments = $this->resolveCommunityIssueDocuments($request, $post);
        if ($communityIssueDocuments !== null) {
            $data['meta']['community_issue_documents'] = $communityIssueDocuments;
        } elseif (data_get($post->meta, 'community_issue_documents')) {
            foreach ((array) data_get($post->meta, 'community_issue_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['community_issue_documents']);
        }

        $agricultureProblemPhotos = $this->resolveAgricultureProblemPhotos($request, $post);
        if ($agricultureProblemPhotos !== null) {
            $data['meta']['agriculture_problem_photos'] = $agricultureProblemPhotos;
        } elseif (data_get($post->meta, 'agriculture_problem_photos')) {
            foreach ((array) data_get($post->meta, 'agriculture_problem_photos', []) as $photo) {
                CommunityPostFileUploader::deleteIfExists(data_get($photo, 'path'));
            }
            unset($data['meta']['agriculture_problem_photos']);
        }

        $agricultureGallery = $this->resolveAgricultureGallery($request, $post);
        if ($agricultureGallery !== null) {
            $data['meta']['agriculture_gallery'] = $agricultureGallery;
        } elseif (data_get($post->meta, 'agriculture_gallery')) {
            foreach ((array) data_get($post->meta, 'agriculture_gallery', []) as $categoryPhotos) {
                foreach ((array) $categoryPhotos as $photo) {
                    CommunityPostFileUploader::deleteIfExists(data_get($photo, 'path'));
                }
            }
            unset($data['meta']['agriculture_gallery']);
        }

        $agricultureDocuments = $this->resolveAgricultureDocuments($request, $post);
        if ($agricultureDocuments !== null) {
            $data['meta']['agriculture_documents'] = $agricultureDocuments;
        } elseif (data_get($post->meta, 'agriculture_documents')) {
            foreach ((array) data_get($post->meta, 'agriculture_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['agriculture_documents']);
        }

        $environmentGallery = $this->resolveEnvironmentGallery($request, $post);
        if ($environmentGallery !== null) {
            $data['meta']['environment_gallery'] = $environmentGallery;
        } elseif (data_get($post->meta, 'environment_gallery')) {
            foreach ((array) data_get($post->meta, 'environment_gallery', []) as $categoryPhotos) {
                foreach ((array) $categoryPhotos as $photo) {
                    CommunityPostFileUploader::deleteIfExists(data_get($photo, 'path'));
                }
            }
            unset($data['meta']['environment_gallery']);
        }

        $environmentDocuments = $this->resolveEnvironmentDocuments($request, $post);
        if ($environmentDocuments !== null) {
            $data['meta']['environment_documents'] = $environmentDocuments;
        } elseif (data_get($post->meta, 'environment_documents')) {
            foreach ((array) data_get($post->meta, 'environment_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['environment_documents']);
        }

        $scienceTechnologyGallery = $this->resolveScienceTechnologyGallery($request, $post);
        if ($scienceTechnologyGallery !== null) {
            $data['meta']['science_technology_gallery'] = $scienceTechnologyGallery;
        } elseif (data_get($post->meta, 'science_technology_gallery')) {
            foreach ((array) data_get($post->meta, 'science_technology_gallery', []) as $categoryPhotos) {
                foreach ((array) $categoryPhotos as $photo) {
                    CommunityPostFileUploader::deleteIfExists(data_get($photo, 'path'));
                }
            }
            unset($data['meta']['science_technology_gallery']);
        }

        $scienceTechnologyDocuments = $this->resolveScienceTechnologyDocuments($request, $post);
        if ($scienceTechnologyDocuments !== null) {
            $data['meta']['science_technology_documents'] = $scienceTechnologyDocuments;
        } elseif (data_get($post->meta, 'science_technology_documents')) {
            foreach ((array) data_get($post->meta, 'science_technology_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['science_technology_documents']);
        }

        $astroConsultancyDocuments = $this->resolveAstroConsultancyDocuments($request, $post);
        if ($astroConsultancyDocuments !== null) {
            $data['meta']['astro_consultancy_documents'] = $astroConsultancyDocuments;
        } elseif (data_get($post->meta, 'astro_consultancy_documents')) {
            foreach ((array) data_get($post->meta, 'astro_consultancy_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['astro_consultancy_documents']);
        }

        $religionSpiritualityGallery = $this->resolveReligionSpiritualityGallery($request, $post);
        if ($religionSpiritualityGallery !== null) {
            $data['meta']['religion_spirituality_gallery'] = $religionSpiritualityGallery;
        } elseif (data_get($post->meta, 'religion_spirituality_gallery')) {
            foreach ((array) data_get($post->meta, 'religion_spirituality_gallery', []) as $image) {
                CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
            }
            unset($data['meta']['religion_spirituality_gallery']);
        }

        $religionSpiritualityDocuments = $this->resolveReligionSpiritualityDocuments($request, $post);
        if ($religionSpiritualityDocuments !== null) {
            $data['meta']['religion_spirituality_documents'] = $religionSpiritualityDocuments;
        } elseif (data_get($post->meta, 'religion_spirituality_documents')) {
            foreach ((array) data_get($post->meta, 'religion_spirituality_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['religion_spirituality_documents']);
        }

        $religionSpiritualityAudio = $this->resolveReligionSpiritualityAudio($request, $post);
        if ($religionSpiritualityAudio !== null) {
            $data['meta']['religion_spirituality_audio'] = $religionSpiritualityAudio;
        } elseif ($request->boolean('remove_religion_spirituality_audio') && data_get($post->meta, 'religion_spirituality_audio')) {
            $this->deleteStoryAudioFile(data_get($post->meta, 'religion_spirituality_audio'));
            unset($data['meta']['religion_spirituality_audio']);
        }

        $creativeCornerGallery = $this->resolveCreativeCornerGallery($request, $post);
        if ($creativeCornerGallery !== null) {
            $data['meta']['creative_corner_gallery'] = $creativeCornerGallery;
        } elseif (data_get($post->meta, 'creative_corner_gallery')) {
            foreach ((array) data_get($post->meta, 'creative_corner_gallery', []) as $image) {
                CommunityPostFileUploader::deleteIfExists(data_get($image, 'path'));
            }
            unset($data['meta']['creative_corner_gallery']);
        }

        $creativeCornerDocuments = $this->resolveCreativeCornerDocuments($request, $post);
        if ($creativeCornerDocuments !== null) {
            $data['meta']['creative_corner_documents'] = $creativeCornerDocuments;
        } elseif (data_get($post->meta, 'creative_corner_documents')) {
            foreach ((array) data_get($post->meta, 'creative_corner_documents', []) as $document) {
                CommunityPostFileUploader::deleteIfExists(data_get($document, 'path'));
            }
            unset($data['meta']['creative_corner_documents']);
        }

        $creativeCornerAudio = $this->resolveCreativeCornerAudio($request, $post);
        if ($creativeCornerAudio !== null) {
            $data['meta']['creative_corner_audio'] = $creativeCornerAudio;
        } elseif ($request->boolean('remove_creative_corner_audio') && data_get($post->meta, 'creative_corner_audio')) {
            $this->deleteStoryAudioFile(data_get($post->meta, 'creative_corner_audio'));
            unset($data['meta']['creative_corner_audio']);
        }

        $competitionsOrganizerLogo = $this->resolveCompetitionsOrganizerLogo($request, $post);
        if ($competitionsOrganizerLogo !== null) {
            $data['meta']['competitions_organizer_logo'] = $competitionsOrganizerLogo;
        } elseif (CommunityPost::usesCompetitionsFlow($request->input('content_type')) && $request->boolean('removed_competitions_organizer_logo')) {
            CommunityPostFileUploader::deleteIfExists(data_get($post->meta, 'competitions_organizer_logo.path'));
            unset($data['meta']['competitions_organizer_logo']);
        }

        $competitionsJury = $this->resolveCompetitionsJury($request, $post);
        if ($competitionsJury !== null) {
            $data['meta']['competitions_jury'] = $competitionsJury;
        } elseif (CommunityPost::usesCompetitionsFlow($request->input('content_type')) && data_get($post->meta, 'competitions_jury')) {
            foreach ((array) data_get($post->meta, 'competitions_jury', []) as $member) {
                CommunityPostFileUploader::deleteIfExists(data_get($member, 'photo.path'));
            }
            unset($data['meta']['competitions_jury']);
        }

        $competitionsSponsors = $this->resolveCompetitionsSponsors($request, $post);
        if ($competitionsSponsors !== null) {
            $data['meta']['competitions_sponsors'] = $competitionsSponsors;
        } elseif (CommunityPost::usesCompetitionsFlow($request->input('content_type')) && data_get($post->meta, 'competitions_sponsors')) {
            foreach ((array) data_get($post->meta, 'competitions_sponsors', []) as $sponsor) {
                CommunityPostFileUploader::deleteIfExists(data_get($sponsor, 'logo.path'));
            }
            unset($data['meta']['competitions_sponsors']);
        }

        foreach ([
            ['science_technology_source_code', 'science_technology_source_code', 'science-technology-source-code', 'removed_science_technology_source_code'],
            ['science_technology_circuit_diagram', 'science_technology_circuit_diagram', 'science-technology-circuit-diagram', 'removed_science_technology_circuit_diagram'],
            ['science_technology_pcb_design', 'science_technology_pcb_design', 'science-technology-pcb-design', 'removed_science_technology_pcb_design'],
        ] as [$inputName, $metaKey, $storagePrefix, $removedInput]) {
            $attachment = $this->resolveScienceTechnologySingleAttachment($request, $post, $inputName, $metaKey, $storagePrefix, $removedInput);
            if ($attachment !== null) {
                $data['meta'][$metaKey] = $attachment;
            } elseif ($request->boolean($removedInput) && data_get($post->meta, $metaKey.'.path')) {
                CommunityPostFileUploader::deleteIfExists(data_get($post->meta, $metaKey.'.path'));
                unset($data['meta'][$metaKey]);
            }
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

        $seniorCitizensForumAudio = $this->resolveSeniorCitizensForumAudio($request, $post);
        if ($seniorCitizensForumAudio !== null) {
            $data['meta']['senior_citizens_forum_audio'] = $seniorCitizensForumAudio;
        } elseif ($request->input('content_type') === 'senior-citizens-forum' && $request->input('senior_citizens_forum_audio_source_type') === 'none') {
            unset($data['meta']['senior_citizens_forum_audio']);
        } elseif ($request->boolean('remove_senior_citizens_forum_audio')) {
            unset($data['meta']['senior_citizens_forum_audio']);
        }

        $seniorCitizensForumAchievements = $this->resolveSeniorCitizensForumAchievements($request, $post);
        if ($seniorCitizensForumAchievements !== null) {
            $data['meta']['senior_citizens_forum_achievements'] = $seniorCitizensForumAchievements;
        } elseif (CommunityPost::usesSeniorCitizensForumFlow($request->input('content_type')) && data_get($post->meta, 'senior_citizens_forum_achievements')) {
            unset($data['meta']['senior_citizens_forum_achievements']);
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
        $originalMeta = is_array($originalAttributes['meta'] ?? null)
            ? $originalAttributes['meta']
            : (json_decode((string) ($originalAttributes['meta'] ?? ''), true) ?: []);
        $post->update($data);

        $this->syncReportTrustScore($post->fresh());

        CommunityPostAuditLogger::logUpdated($post, $request, $originalAttributes);

        if ($post->supportsCivicEngagement() && $post->isPubliclyVisible()) {
            $updateMessage = 'The author published an update to this '.match (true) {
                $post->isCommunityIssuesPost() => 'community issue',
                $post->isMyAreaPost() => 'My Area post',
                default => 'report',
            }.'.';
            $communityIssueFollowersNotified = false;

            if ($post->isMyAreaPost()) {
                $newStatus = data_get($post->meta, 'my_area_status_tracker');
                $oldStatus = data_get($originalMeta, 'my_area_status_tracker');
                if (filled($newStatus) && $newStatus !== $oldStatus) {
                    $updateMessage = 'Resolution status updated to: '.$newStatus.'.';
                }
            }

            if ($post->isCommunityIssuesPost()) {
                $newStatus = data_get($post->meta, 'community_issue_status_tracker');
                $oldStatus = data_get($originalMeta, 'community_issue_status_tracker');
                $newTimeline = (string) data_get($post->meta, 'community_issue_resolution_timeline', '');
                $oldTimeline = (string) data_get($originalMeta, 'community_issue_resolution_timeline', '');

                if (filled($newStatus) && $newStatus !== $oldStatus) {
                    CommunityCommunityIssuesEngagementNotificationService::notifyFollowersOfStatusChange(
                        $post->fresh(),
                        is_string($oldStatus) ? $oldStatus : null,
                        $newStatus
                    );
                    $communityIssueFollowersNotified = true;
                } elseif (filled($newTimeline) && $newTimeline !== $oldTimeline) {
                    CommunityCommunityIssuesEngagementNotificationService::notifyFollowersOfTimelineUpdate($post->fresh());
                    $communityIssueFollowersNotified = true;
                }
            }

            if (! $communityIssueFollowersNotified) {
                CommunityReportEngagementNotificationService::notifyFollowersOfReportUpdate(
                    $post->fresh(),
                    $updateMessage
                );
            }
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
            $post->isSeniorCitizensForumPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunitySeniorCitizensForumEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
        } elseif (
            $post->isStudentCornerPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityStudentCornerEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
        } elseif (
            $post->isYouthCornerPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityYouthCornerEngagementNotificationService::notifyAuthorOfPublishedPost($post->fresh());
        } elseif (
            in_array($post->content_type, ['poetry', 'autobiography'], true)
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityStoryEngagementNotificationService::notifyAuthorOfPublishedWithoutAudio($post->fresh());
        } elseif (
            $post->isAgriculturePost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityAgricultureEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        }

        if (
            $post->isAgriculturePost()
            && $post->isPubliclyVisible()
            && $originalAttributes['status'] === \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityAgricultureEngagementNotificationService::maybeNotifyCropDoctorRequestOnUpdate($post->fresh(), $originalMeta);
            CommunityAgricultureEngagementNotificationService::maybeNotifyAskCommunityOnUpdate($post->fresh(), $originalMeta);
        }

        if (
            $post->isEnvironmentPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityEnvironmentEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        }

        if (
            $post->isEnvironmentPost()
            && $post->isPubliclyVisible()
            && $originalAttributes['status'] === \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityEnvironmentEngagementNotificationService::maybeNotifyAskCommunityOnUpdate($post->fresh(), $originalMeta);
        }

        if (
            $post->isAstroConsultancyPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityAstroConsultancyEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        }

        if (
            $post->isAstroConsultancyPost()
            && $post->isPubliclyVisible()
            && $originalAttributes['status'] === \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityAstroConsultancyEngagementNotificationService::maybeNotifyAskCommunityOnUpdate($post->fresh(), $originalMeta);
        }

        if (
            $post->isReligionSpiritualityPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityReligionSpiritualityEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        }

        if (
            $post->isReligionSpiritualityPost()
            && $post->isPubliclyVisible()
            && $originalAttributes['status'] === \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityReligionSpiritualityEngagementNotificationService::maybeNotifyAskCommunityOnUpdate($post->fresh(), $originalMeta);
        }

        if (
            $post->isCreativeCornerPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityCreativeCornerEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        }

        if (
            $post->isCreativeCornerPost()
            && $post->isPubliclyVisible()
            && $originalAttributes['status'] === \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityCreativeCornerEngagementNotificationService::maybeNotifyAskCommunityOnUpdate($post->fresh(), $originalMeta);
        }

        if (
            $post->isCompetitionsPost()
            && $post->isPubliclyVisible()
            && ! $wasPending
            && $originalAttributes['status'] !== \App\Models\CommunityPost::STATUS_PUBLISHED
        ) {
            CommunityCompetitionsEngagementNotificationService::notifyOnPublishedPost($post->fresh());
        }

        $message = $post->status === CommunityPost::STATUS_DRAFT
            ? 'Post saved successfully. You can publish it later from My Posts.'
            : ($post->isPendingApproval()
                ? 'Community post submitted for admin approval.'
                : 'Community post updated successfully.');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => $post->status === CommunityPost::STATUS_DRAFT
                    ? route('community.posts.index')
                    : route('community.posts.show', $post),
            ]);
        }

        return redirect()
            ->to($post->status === CommunityPost::STATUS_DRAFT ? route('community.posts.index') : route('community.posts.show', $post))
            ->with('success', $message);
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

    public function uploadInlineAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,mp4,webm,mov,avi,mkv',
            ],
        ]);

        $file = $request->file('upload');
        $mimeType = (string) $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');
        $attachment = CommunityPostFileUploader::storeAttachment(
            $file,
            $isVideo ? 'inline-videos' : 'inline-documents'
        );

        return response()->json([
            'url' => $attachment['url'],
            'name' => $attachment['name'],
            'type' => $isVideo ? 'video' : 'document',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?CommunityPost $post = null): array
    {
        $this->pruneUnreadableUploads($request);

        $typeKeys = CommunityContentTaxonomy::allowedContentTypeKeys($post);
        $contentType = $request->input('content_type');
        $isReport = $contentType === 'reports';
        $usesStructuredLocation = CommunityPost::usesStructuredLocation(is_string($contentType) ? $contentType : null);
        $mountsStructuredLocation = CommunityPost::mountsStructuredLocationFields(is_string($contentType) ? $contentType : null);
        $isChildrensCorner = CommunityPost::usesChildrensCornerFlow(is_string($contentType) ? $contentType : null);
        $isAwareness = CommunityPost::usesAwarenessFlow(is_string($contentType) ? $contentType : null);
        $isBusiness = CommunityPost::usesBusinessFlow(is_string($contentType) ? $contentType : null);
        $isWomensWorld = CommunityPost::usesWomensWorldFlow(is_string($contentType) ? $contentType : null);
        $isSeniorCitizensForum = CommunityPost::usesSeniorCitizensForumFlow(is_string($contentType) ? $contentType : null);
        $isStudentCorner = CommunityPost::usesStudentCornerFlow(is_string($contentType) ? $contentType : null);
        $isYouthCorner = CommunityPost::usesYouthCornerFlow(is_string($contentType) ? $contentType : null);
        $isLocalVoices = CommunityPost::usesLocalVoicesFlow(is_string($contentType) ? $contentType : null);
        $isMyArea = CommunityPost::usesMyAreaFlow(is_string($contentType) ? $contentType : null);
        $isCommunityIssues = CommunityPost::usesCommunityIssuesFlow(is_string($contentType) ? $contentType : null);
        $isAgriculture = CommunityPost::usesAgricultureFlow(is_string($contentType) ? $contentType : null);
        $isEnvironment = CommunityPost::usesEnvironmentFlow(is_string($contentType) ? $contentType : null);
        $isScienceTechnology = CommunityPost::usesScienceTechnologyFlow(is_string($contentType) ? $contentType : null);
        $isAstroConsultancy = CommunityPost::usesAstroConsultancyFlow(is_string($contentType) ? $contentType : null);
        $isReligionSpirituality = CommunityPost::usesReligionSpiritualityFlow(is_string($contentType) ? $contentType : null);
        $isCreativeCorner = CommunityPost::usesCreativeCornerFlow(is_string($contentType) ? $contentType : null);
        $isCompetitions = CommunityPost::usesCompetitionsFlow(is_string($contentType) ? $contentType : null);
        $isStudentCornerProject = $isStudentCorner
            && $request->input('student_corner_content_type') === CommunityContentTaxonomy::studentCornerProjectContentType();
        $isYouthCornerProject = $isYouthCorner
            && $request->input('youth_corner_content_type') === CommunityContentTaxonomy::youthCornerProjectContentType();
        $childShareType = $request->input('child_share_type');
        $childContentMode = CommunityContentTaxonomy::childrensCornerContentMode(is_string($childShareType) ? $childShareType : null);
        $isChildrensCornerQuiz = $isChildrensCorner && $childContentMode === 'quiz';

        if (is_array($request->input('book_pages'))) {
            $request->merge([
                'book_pages' => collect($request->input('book_pages'))
                    ->map(function (mixed $page): mixed {
                        if (! is_array($page)) {
                            return $page;
                        }

                        $page['language'] = CommunityContentTaxonomy::bookPageLanguageCode($page['language'] ?? 'en');

                        return $page;
                    })
                    ->all(),
            ]);
        }

        if ($isChildrensCornerQuiz) {
            $request->merge([
                'childrens_corner_quiz' => $this->sanitizeChildrensCornerQuizPayload($request->input('childrens_corner_quiz')),
            ]);
        }

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

        if ($isSeniorCitizensForum && $request->filled('senior_citizens_forum_category')) {
            $request->merge(['category' => $request->input('senior_citizens_forum_category')]);
        }

        if ($isStudentCorner && $request->filled('student_corner_category')) {
            $request->merge(['category' => $request->input('student_corner_category')]);
        }

        if ($isYouthCorner && $request->filled('youth_corner_category')) {
            $request->merge(['category' => $request->input('youth_corner_category')]);
        }

        if ($isLocalVoices && $request->filled('local_voice_category')) {
            $request->merge(['category' => $request->input('local_voice_category')]);
        }

        if ($isMyArea && $request->filled('my_area_topic_category')) {
            $request->merge(['category' => $request->input('my_area_topic_category')]);
        }

        if ($isCommunityIssues && $request->filled('community_issue_category')) {
            $request->merge(['category' => $request->input('community_issue_category')]);
        }

        if ($isAgriculture && $request->filled('agriculture_category')) {
            $request->merge(['category' => $request->input('agriculture_category')]);
        }

        if ($isEnvironment && $request->filled('environment_category')) {
            $request->merge(['category' => $request->input('environment_category')]);
        }

        if ($isScienceTechnology && $request->filled('science_technology_category')) {
            $request->merge(['category' => $request->input('science_technology_category')]);
        }

        if ($isAstroConsultancy && $request->filled('astro_consultancy_category')) {
            $request->merge(['category' => $request->input('astro_consultancy_category')]);
        }

        if ($isReligionSpirituality && $request->filled('religion_spirituality_category')) {
            $request->merge(['category' => $request->input('religion_spirituality_category')]);
        }

        if ($isCreativeCorner && $request->filled('creative_corner_category')) {
            $request->merge(['category' => $request->input('creative_corner_category')]);
        }

        if ($isCompetitions && $request->filled('competitions_category')) {
            $request->merge(['category' => $request->input('competitions_category')]);
        }

        if ($isMyArea && $request->filled('my_area_activity_type')) {
            $request->merge(['writing_purpose' => $request->input('my_area_activity_type')]);
        }

        if ($request->input('status') === CommunityPost::STATUS_DRAFT) {
            return $this->validatedDraft($request, $post, $typeKeys);
        }

        $rules = [
            'content_type' => ['required', Rule::in($typeKeys)],
            'writing_purpose' => ['nullable', 'string', 'max:120'],
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
                Rule::requiredIf(fn () => in_array((string) $contentType, ['awareness', 'local-voices'], true)),
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
            'senior_citizens_forum_category' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumMainCategories()),
            ],
            'senior_citizens_forum_content_type' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumContentTypes()),
            ],
            'student_corner_category' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerMainCategories()),
            ],
            'student_corner_content_type' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerContentTypes()),
            ],
            'student_corner_profile_name' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'string',
                'max:120',
            ],
            'student_corner_class_course' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerClassCourses()),
            ],
            'student_corner_stream' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerStreams()),
            ],
            'student_corner_institution_name' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'string',
                'max:200',
            ],
            'student_corner_target_audience' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'array',
            ],
            'student_corner_target_audience.*' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerTargetAudiences()),
            ],
            'student_corner_project_title' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'required',
                'string',
                'max:200',
            ],
            'student_corner_project_category' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerProjectCategories()),
            ],
            'student_corner_project_description' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'required',
                'string',
                'max:5000',
            ],
            'student_corner_project_outcome' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'nullable',
                'string',
                'max:3000',
            ],
            'student_corner_documents' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'nullable',
                'array',
                'max:'.self::MAX_STUDENT_CORNER_DOCUMENTS,
            ],
            'student_corner_documents.*' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'file',
                'max:20480',
                'mimes:'.implode(',', CommunityContentTaxonomy::studentCornerDocumentExtensions()),
            ],
            'removed_student_corner_documents' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'nullable',
                'array',
            ],
            'removed_student_corner_documents.*' => [
                Rule::excludeIf(fn () => ! $isStudentCornerProject),
                'string',
                'max:255',
            ],
            'student_corner_video_type' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerVideoTypes()),
            ],
            'student_corner_gallery' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'array',
                'max:'.self::MAX_STUDENT_CORNER_GALLERY,
            ],
            'student_corner_gallery.*' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'image',
                'max:4096',
            ],
            'removed_student_corner_gallery' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'array',
            ],
            'removed_student_corner_gallery.*' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'string',
                'max:255',
            ],
            'student_corner_study_material_types' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'array',
            ],
            'student_corner_study_material_types.*' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerStudyMaterialTypes()),
            ],
            'student_corner_career_guidance_topics' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'nullable',
                'array',
            ],
            'student_corner_career_guidance_topics.*' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'string',
                Rule::in(CommunityContentTaxonomy::studentCornerCareerGuidanceTopics()),
            ],
            'student_corner_scholarship_name' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:200'],
            'student_corner_eligibility' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:2000'],
            'student_corner_application_deadline' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:120'],
            'student_corner_official_website' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'url', 'max:255'],
            'student_corner_exam_name' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:160'],
            'student_corner_preparation_strategy' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:3000'],
            'student_corner_resources_used' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:3000'],
            'student_corner_marks_rank' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:120'],
            'student_corner_lessons_learned' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:3000'],
            'student_corner_skills' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'array'],
            'student_corner_skills.*' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'string', Rule::in(CommunityContentTaxonomy::studentCornerSkills())],
            'student_corner_social_impact_categories' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'array'],
            'student_corner_social_impact_categories.*' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'string', Rule::in(CommunityContentTaxonomy::studentCornerSocialImpactCategories())],
            'student_corner_ask_community' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:500'],
            'student_corner_poll_question' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:255'],
            'student_corner_poll_options' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:2000'],
            'student_corner_mentorship_requests' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'array'],
            'student_corner_mentorship_requests.*' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'string', Rule::in(CommunityContentTaxonomy::studentCornerMentorshipRequests())],
            'student_corner_submit_to_competition' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'boolean'],
            'student_corner_competition_categories' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'array'],
            'student_corner_competition_categories.*' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'string', Rule::in(CommunityContentTaxonomy::studentCornerCompetitionCategories())],
            'student_corner_visibility' => [
                Rule::excludeIf(fn () => ! $isStudentCorner),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::studentCornerVisibilitySettings())),
            ],
            'student_corner_achievements' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'array', 'max:'.self::MAX_STUDENT_CORNER_ACHIEVEMENTS],
            'student_corner_achievements.*.achievement_title' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:160'],
            'student_corner_achievements.*.year' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:10'],
            'student_corner_achievements.*.certificate' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'file', 'max:4096', 'mimes:pdf,jpg,jpeg,png,webp'],
            'student_corner_achievements.*.existing_certificate_path' => [Rule::excludeIf(fn () => ! $isStudentCorner), 'nullable', 'string', 'max:255'],
            'youth_corner_category' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::youthCornerMainCategories()),
            ],
            'local_voice_type' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceTypes()),
            ],
            'local_voice_category' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceMainCategories()),
            ],
            'community_issue_category' => [
                Rule::excludeIf(fn () => ! $isCommunityIssues),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::communityIssueMainCategories()),
            ],
            'community_issue_type' => [
                Rule::excludeIf(fn () => ! $isCommunityIssues),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::communityIssueTypes()),
            ],
            'agriculture_share_type' => [
                Rule::excludeIf(fn () => ! $isAgriculture),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::agricultureShareTypes()),
            ],
            'agriculture_category' => [
                Rule::excludeIf(fn () => ! $isAgriculture),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::agricultureMainCategories()),
            ],
            'agriculture_crop_name' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:120'],
            'agriculture_crop_variety' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:120'],
            'agriculture_sowing_date' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'date'],
            'agriculture_harvest_date' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'date'],
            'agriculture_growing_season' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureGrowingSeasons())],
            'agriculture_climate_zone' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:120'],
            'agriculture_soil_type' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureSoilTypes())],
            'agriculture_farm_size' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureFarmSizes())],
            'agriculture_farming_type' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureFarmingTypes())],
            'agriculture_irrigation_method' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureIrrigationMethods())],
            'agriculture_water_source' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureWaterSources())],
            'agriculture_water_conservation_practices' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'array'],
            'agriculture_water_conservation_practices.*' => [Rule::excludeIf(fn () => ! $isAgriculture), 'string', Rule::in(CommunityContentTaxonomy::agricultureWaterConservationPractices())],
            'agriculture_soil_test_conducted' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', Rule::in(['yes', 'no'])],
            'agriculture_soil_ph' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:40'],
            'agriculture_soil_organic_carbon' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:40'],
            'agriculture_soil_nitrogen' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:40'],
            'agriculture_soil_phosphorus' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:40'],
            'agriculture_soil_potassium' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:40'],
            'agriculture_soil_recommendations' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:3000'],
            'agriculture_problem_type' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureProblemTypes())],
            'agriculture_expert_assistance' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', Rule::in(['yes', 'no'])],
            'agriculture_problem_photos' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'array', 'max:'.self::MAX_AGRICULTURE_PROBLEM_PHOTOS],
            'agriculture_problem_photos.*' => [Rule::excludeIf(fn () => ! $isAgriculture), 'image', 'max:4096'],
            'removed_agriculture_problem_photos' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'array'],
            'removed_agriculture_problem_photos.*' => [Rule::excludeIf(fn () => ! $isAgriculture), 'string', 'max:255'],
            'agriculture_equipment_name' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:160'],
            'agriculture_equipment_manufacturer' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:160'],
            'agriculture_equipment_experience' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:3000'],
            'agriculture_equipment_cost' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:120'],
            'agriculture_equipment_benefits' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:2000'],
            'agriculture_scheme_name' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:160'],
            'agriculture_scheme_department' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:160'],
            'agriculture_scheme_eligibility' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:2000'],
            'agriculture_scheme_subsidy' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:120'],
            'agriculture_scheme_application_link' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'url', 'max:255'],
            'agriculture_scheme_last_date' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'date'],
            'agriculture_market_commodity' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:120'],
            'agriculture_market_name' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:160'],
            'agriculture_market_price' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:80'],
            'agriculture_market_date' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'date'],
            'agriculture_market_price_trend' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agriculturePriceTrends())],
            'agriculture_livestock_types' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'array'],
            'agriculture_livestock_types.*' => [Rule::excludeIf(fn () => ! $isAgriculture), 'string', Rule::in(CommunityContentTaxonomy::agricultureLivestockTypes())],
            'agriculture_innovation_name' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:160'],
            'agriculture_innovation_description' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:3000'],
            'agriculture_innovation_benefits' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:2000'],
            'agriculture_innovation_results' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:2000'],
            'agriculture_agri_business_type' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureAgriBusinessTypes())],
            'agriculture_weather_impact' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureWeatherImpacts())],
            'agriculture_video_type' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::agricultureVideoExamples())],
            'agriculture_ask_community' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:500'],
            'agriculture_enable_knowledge_exchange' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'boolean'],
            'agriculture_enable_crop_doctor' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'boolean'],
            'agriculture_target_audiences' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'array'],
            'agriculture_target_audiences.*' => [Rule::excludeIf(fn () => ! $isAgriculture), 'string', Rule::in(CommunityContentTaxonomy::agricultureTargetAudiences())],
            'agriculture_poll_question' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:255'],
            'agriculture_poll_options' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'string', 'max:2000'],
            'agriculture_documents' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'array', 'max:'.self::MAX_AGRICULTURE_DOCUMENTS],
            'agriculture_documents.*' => [Rule::excludeIf(fn () => ! $isAgriculture), 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max:20480'],
            'removed_agriculture_documents' => [Rule::excludeIf(fn () => ! $isAgriculture), 'nullable', 'array'],
            'removed_agriculture_documents.*' => [Rule::excludeIf(fn () => ! $isAgriculture), 'string', 'max:255'],
            'environment_post_type' => [
                Rule::excludeIf(fn () => ! $isEnvironment),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::environmentPostTypes()),
            ],
            'environment_category' => [
                Rule::excludeIf(fn () => ! $isEnvironment),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::environmentMainCategories()),
            ],
            'environment_natural_feature_name' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:160'],
            'environment_map_pin_type' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::environmentMapPinTypes())],
            'environment_issue_type' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::environmentIssueTypes())],
            'environment_initiative_type' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::environmentInitiativeTypes())],
            'environment_water_source' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::environmentWaterSources())],
            'environment_conservation_method' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::environmentConservationMethods())],
            'environment_water_saved' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:120'],
            'environment_soil_conservation_methods' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'array'],
            'environment_soil_conservation_methods.*' => [Rule::excludeIf(fn () => ! $isEnvironment), 'string', Rule::in(CommunityContentTaxonomy::environmentSoilConservationMethods())],
            'environment_tree_count' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'integer', 'min:0', 'max:1000000'],
            'environment_tree_species' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:255'],
            'environment_tree_plantation_date' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'date'],
            'environment_tree_organization' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:160'],
            'environment_tree_survival_status' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::environmentTreeSurvivalStatuses())],
            'environment_tree_maintenance_plan' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:2000'],
            'environment_waste_types' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'array'],
            'environment_waste_types.*' => [Rule::excludeIf(fn () => ! $isEnvironment), 'string', Rule::in(CommunityContentTaxonomy::environmentWasteTypes())],
            'environment_biodiversity_types' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'array'],
            'environment_biodiversity_types.*' => [Rule::excludeIf(fn () => ! $isEnvironment), 'string', Rule::in(CommunityContentTaxonomy::environmentBiodiversityTypes())],
            'environment_climate_impacts' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'array'],
            'environment_climate_impacts.*' => [Rule::excludeIf(fn () => ! $isEnvironment), 'string', Rule::in(CommunityContentTaxonomy::environmentClimateImpacts())],
            'environment_video_type' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::environmentVideoExamples())],
            'environment_enable_impact_calculator' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_data_trees_planted' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:80'],
            'environment_data_area_covered' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:80'],
            'environment_data_water_saved' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:80'],
            'environment_data_waste_collected' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:80'],
            'environment_data_people_participated' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:80'],
            'environment_data_carbon_reduction' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:80'],
            'environment_data_species_recorded' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:80'],
            'environment_participation_requests' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'array'],
            'environment_participation_requests.*' => [Rule::excludeIf(fn () => ! $isEnvironment), 'string', Rule::in(CommunityContentTaxonomy::environmentParticipationRequests())],
            'environment_event_campaign_name' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:160'],
            'environment_event_organizer' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:160'],
            'environment_event_venue' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:160'],
            'environment_event_date' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'date'],
            'environment_event_time' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:40'],
            'environment_event_registration_link' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'url', 'max:255'],
            'environment_scheme_name' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:160'],
            'environment_scheme_department' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:160'],
            'environment_scheme_eligibility' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:2000'],
            'environment_scheme_benefits' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:2000'],
            'environment_scheme_official_link' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'url', 'max:255'],
            'environment_ask_community' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:500'],
            'environment_poll_question' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:255'],
            'environment_poll_options' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'string', 'max:2000'],
            'environment_show_on_green_map' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_enable_green_leader' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_allow_join_campaign' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_allow_volunteer' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_allow_donate' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_allow_support_initiative' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_allow_follow_campaign' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_allow_volunteer_registration' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'boolean'],
            'environment_documents' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'array', 'max:'.self::MAX_ENVIRONMENT_DOCUMENTS],
            'environment_documents.*' => [Rule::excludeIf(fn () => ! $isEnvironment), 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max:20480'],
            'removed_environment_documents' => [Rule::excludeIf(fn () => ! $isEnvironment), 'nullable', 'array'],
            'removed_environment_documents.*' => [Rule::excludeIf(fn () => ! $isEnvironment), 'string', 'max:255'],
            'science_technology_post_type' => [
                Rule::excludeIf(fn () => ! $isScienceTechnology),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::scienceTechnologyPostTypes()),
            ],
            'science_technology_category' => [
                Rule::excludeIf(fn () => ! $isScienceTechnology),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::scienceTechnologyMainCategories()),
            ],
            'science_technology_target_audience' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_target_audience.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyTargetAudiences())],
            'science_technology_level' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyLevels())],
            'science_technology_scientific_fields' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_scientific_fields.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyScientificFields())],
            'science_technology_project_name' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:160'],
            'science_technology_project_category' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyProjectCategories())],
            'science_technology_project_objective' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_project_components' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_project_working_principle' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_project_results' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_project_future_improvements' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_research_area' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:160'],
            'science_technology_research_institution' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:160'],
            'science_technology_research_duration' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:120'],
            'science_technology_research_abstract' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:5000'],
            'science_technology_research_keywords' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:500'],
            'science_technology_research_methodology' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_research_results' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_research_conclusion' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_research_references' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:5000'],
            'science_technology_experiment_objective' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_experiment_materials' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_experiment_procedure' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:4000'],
            'science_technology_experiment_observations' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_experiment_results' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_experiment_safety' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_innovation_name' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:160'],
            'science_technology_patent_filed' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyPatentStatuses())],
            'science_technology_problem_solved' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_novel_features' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_innovation_technology' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_innovation_benefits' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_commercial_potential' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_technologies_used' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_technologies_used.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyTechnologiesUsed())],
            'science_technology_programming_languages' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_programming_languages.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyProgrammingLanguages())],
            'science_technology_github_repo' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'url', 'max:255'],
            'science_technology_source_code' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'file', 'mimes:zip', 'max:20480'],
            'removed_science_technology_source_code' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_hardware_components' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:3000'],
            'science_technology_circuit_diagram' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
            'removed_science_technology_circuit_diagram' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_pcb_design' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,zip', 'max:20480'],
            'removed_science_technology_pcb_design' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_bom' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:4000'],
            'science_technology_hardware_cost' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:120'],
            'science_technology_water_soil_topics' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_water_soil_topics.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyWaterSoilTopics())],
            'science_technology_renewable_energy' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_renewable_energy.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyRenewableEnergyTypes())],
            'science_technology_patent_number' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:120'],
            'science_technology_application_number' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:120'],
            'science_technology_patent_status' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyPatentIprStatuses())],
            'science_technology_funding_types' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_funding_types.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyFundingTypes())],
            'science_technology_application_areas' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_application_areas.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyApplicationAreas())],
            'science_technology_reference_types' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_reference_types.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyReferenceTypes())],
            'science_technology_references' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:8000'],
            'science_technology_license' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyLicenseOptions())],
            'science_technology_video_type' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyVideoExamples())],
            'science_technology_enable_innovation_showcase' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_enable_expert_review' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_open_innovation' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_open_innovation.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyOpenInnovationOptions())],
            'science_technology_challenge_themes' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_challenge_themes.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyInnovationChallengeThemes())],
            'science_technology_collaboration_requests' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_collaboration_requests.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyCollaborationRequests())],
            'science_technology_ask_community' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:500'],
            'science_technology_allow_poll' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_poll_question' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:255'],
            'science_technology_poll_options' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'string', 'max:2000'],
            'science_technology_comment_settings' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'science_technology_comment_settings.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', Rule::in(CommunityContentTaxonomy::scienceTechnologyCommentSettings())],
            'science_technology_allow_support' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_allow_follow' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_allow_collaborate' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'boolean'],
            'science_technology_documents' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array', 'max:'.self::MAX_SCIENCE_TECHNOLOGY_DOCUMENTS],
            'science_technology_documents.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', 'max:20480'],
            'removed_science_technology_documents' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'nullable', 'array'],
            'removed_science_technology_documents.*' => [Rule::excludeIf(fn () => ! $isScienceTechnology), 'string', 'max:255'],
            'astro_consultancy_post_type' => [
                Rule::excludeIf(fn () => ! $isAstroConsultancy),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::astroConsultancyPostTypes()),
            ],
            'astro_consultancy_category' => [
                Rule::excludeIf(fn () => ! $isAstroConsultancy),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::astroConsultancyMainCategories()),
            ],
            'astro_consultancy_target_audience' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_target_audience.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyTargetAudiences())],
            'astro_consultancy_consultation_topics' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_consultation_topics.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyConsultationTopics())],
            'astro_consultancy_content_language' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyContentLanguages())],
            'astro_consultancy_zodiac_sign' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyZodiacSigns())],
            'astro_consultancy_horoscope_period' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyHoroscopePeriods())],
            'astro_consultancy_vastu_property_types' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_vastu_property_types.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyVastuPropertyTypes())],
            'astro_consultancy_vastu_areas' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_vastu_areas.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyVastuAreas())],
            'astro_consultancy_life_path_number' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:40'],
            'astro_consultancy_destiny_number' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:40'],
            'astro_consultancy_name_number' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:40'],
            'astro_consultancy_lucky_number' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:40'],
            'astro_consultancy_compatibility' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:160'],
            'astro_consultancy_gemstone' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:120'],
            'astro_consultancy_gemstone_planet' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:80'],
            'astro_consultancy_gemstone_benefits' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:2000'],
            'astro_consultancy_gemstone_precautions' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:2000'],
            'astro_consultancy_festival_name' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:160'],
            'astro_consultancy_muhurat_type' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:120'],
            'astro_consultancy_muhurat_date' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'date'],
            'astro_consultancy_muhurat_time' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:120'],
            'astro_consultancy_festival_significance' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:3000'],
            'astro_consultancy_document_types' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_document_types.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyDocumentTypes())],
            'astro_consultancy_video_type' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyVideoExamples())],
            'astro_consultancy_consultant_profile_url' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'url', 'max:255'],
            'astro_consultancy_related_service_actions' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_related_service_actions.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyRelatedServiceActions())],
            'astro_consultancy_enable_consultant_linking' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'boolean'],
            'astro_consultancy_knowledge_library_topics' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_knowledge_library_topics.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyKnowledgeLibraryTopics())],
            'astro_consultancy_enable_live_qa' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'boolean'],
            'astro_consultancy_private_query_options' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_private_query_options.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyPrivateQueryOptions())],
            'astro_consultancy_ask_community' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:500'],
            'astro_consultancy_allow_poll' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'boolean'],
            'astro_consultancy_poll_question' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:255'],
            'astro_consultancy_poll_options' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'string', 'max:2000'],
            'astro_consultancy_comment_settings' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'astro_consultancy_comment_settings.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', Rule::in(CommunityContentTaxonomy::astroConsultancyCommentSettings())],
            'astro_consultancy_documents' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array', 'max:'.self::MAX_ASTRO_CONSULTANCY_DOCUMENTS],
            'astro_consultancy_documents.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'removed_astro_consultancy_documents' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'nullable', 'array'],
            'removed_astro_consultancy_documents.*' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'string', 'max:255'],
            'astro_consultancy_declaration_beliefs' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'accepted'],
            'astro_consultancy_declaration_no_false_claims' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'accepted'],
            'astro_consultancy_declaration_no_fear' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'accepted'],
            'astro_consultancy_declaration_guidelines' => [Rule::excludeIf(fn () => ! $isAstroConsultancy), 'accepted'],
            'religion_spirituality_post_type' => [
                Rule::excludeIf(fn () => ! $isReligionSpirituality),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::religionSpiritualityPostTypes()),
            ],
            'religion_spirituality_category' => [
                Rule::excludeIf(fn () => ! $isReligionSpirituality),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::religionSpiritualityMainCategories()),
            ],
            'religion_spirituality_tradition' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityTraditions())],
            'religion_spirituality_target_audience' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_target_audience.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityTargetAudiences())],
            'religion_spirituality_scripture_name' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:160'],
            'religion_spirituality_scripture_chapter' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:40'],
            'religion_spirituality_scripture_verse' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:40'],
            'religion_spirituality_scripture_reference' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:255'],
            'religion_spirituality_moral_messages' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_moral_messages.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityMoralValues())],
            'religion_spirituality_festival_name' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:160'],
            'religion_spirituality_festival_date' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:120'],
            'religion_spirituality_festival_historical_significance' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:3000'],
            'religion_spirituality_festival_traditional_practices' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_festival_celebration_methods' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_festival_regional_variations' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_pilgrimage_name' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:160'],
            'religion_spirituality_pilgrimage_location' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:160'],
            'religion_spirituality_pilgrimage_best_time' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:120'],
            'religion_spirituality_pilgrimage_history' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:3000'],
            'religion_spirituality_pilgrimage_facilities' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_pilgrimage_travel_tips' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_pilgrimage_accommodation' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_place_of_worship_type' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityPlaceOfWorshipTypes())],
            'religion_spirituality_location_country' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:120'],
            'religion_spirituality_location_state' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:120'],
            'religion_spirituality_location_district' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:120'],
            'religion_spirituality_location_city' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:120'],
            'religion_spirituality_location_gps' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:255'],
            'religion_spirituality_meditation_topics' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_meditation_topics.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityMeditationTopics())],
            'religion_spirituality_community_service_activities' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_community_service_activities.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityCommunityServiceActivities())],
            'religion_spirituality_video_type' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityVideoExamples())],
            'religion_spirituality_audio_type' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityAudioExamples())],
            'religion_spirituality_audio_file' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'file', 'mimes:mp3,wav,ogg,webm,mpeg', 'max:'.self::MAX_STORY_AUDIO_KB],
            'remove_religion_spirituality_audio' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'boolean'],
            'religion_spirituality_document_types' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_document_types.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityDocumentTypes())],
            'religion_spirituality_documents' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array', 'max:'.self::MAX_RELIGION_SPIRITUALITY_DOCUMENTS],
            'religion_spirituality_documents.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'removed_religion_spirituality_documents' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'removed_religion_spirituality_documents.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', 'max:255'],
            'religion_spirituality_gallery' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array', 'max:'.self::MAX_RELIGION_SPIRITUALITY_GALLERY],
            'religion_spirituality_gallery.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'image', 'max:4096'],
            'removed_religion_spirituality_gallery' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'removed_religion_spirituality_gallery.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', 'max:255'],
            'religion_spirituality_related_service_actions' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_related_service_actions.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityRelatedServiceActions())],
            'religion_spirituality_enable_digital_pilgrimage_guide' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'boolean'],
            'religion_spirituality_digital_pilgrimage_site_types' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_digital_pilgrimage_site_types.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityDigitalPilgrimageSiteTypes())],
            'religion_spirituality_digital_pilgrimage_site_name' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:160'],
            'religion_spirituality_digital_pilgrimage_verified_info' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:3000'],
            'religion_spirituality_digital_pilgrimage_nearby_facilities' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_digital_pilgrimage_accommodation' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_digital_pilgrimage_local_businesses' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_digital_pilgrimage_map_url' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:255'],
            'religion_spirituality_enable_festival_calendar' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'boolean'],
            'religion_spirituality_festival_calendar_event_types' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_festival_calendar_event_types.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityFestivalCalendarEventTypes())],
            'religion_spirituality_festival_calendar_event_name' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:160'],
            'religion_spirituality_festival_calendar_event_date' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'date'],
            'religion_spirituality_festival_calendar_description' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_festival_calendar_linked_article_url' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'url', 'max:255'],
            'religion_spirituality_enable_community_service_directory' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'boolean'],
            'religion_spirituality_service_directory_opportunities' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_service_directory_opportunities.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityServiceDirectoryOpportunities())],
            'religion_spirituality_service_directory_organization' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:160'],
            'religion_spirituality_service_directory_when_where' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:255'],
            'religion_spirituality_service_directory_volunteer_notes' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_enable_wisdom_library' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'boolean'],
            'religion_spirituality_wisdom_themes' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_wisdom_themes.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityWisdomLibraryThemes())],
            'religion_spirituality_wisdom_traditions' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_wisdom_traditions.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityTraditions())],
            'religion_spirituality_wisdom_collection_summary' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:3000'],
            'religion_spirituality_ask_community' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:500'],
            'religion_spirituality_allow_poll' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'boolean'],
            'religion_spirituality_poll_question' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:255'],
            'religion_spirituality_poll_options' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'string', 'max:2000'],
            'religion_spirituality_comment_settings' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'nullable', 'array'],
            'religion_spirituality_comment_settings.*' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'string', Rule::in(CommunityContentTaxonomy::religionSpiritualityCommentSettings())],
            'religion_spirituality_declaration_respectful' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'accepted'],
            'religion_spirituality_declaration_accurate' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'accepted'],
            'religion_spirituality_declaration_no_hatred' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'accepted'],
            'religion_spirituality_declaration_educational' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'accepted'],
            'religion_spirituality_declaration_guidelines' => [Rule::excludeIf(fn () => ! $isReligionSpirituality), 'accepted'],
            'creative_corner_post_type' => [
                Rule::excludeIf(fn () => ! $isCreativeCorner),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::creativeCornerPostTypes()),
            ],
            'creative_corner_category' => [
                Rule::excludeIf(fn () => ! $isCreativeCorner),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::creativeCornerMainCategories()),
            ],
            'creative_corner_target_audience' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_target_audience.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerTargetAudiences())],
            'creative_corner_creation_type' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::creativeCornerCreationTypes())],
            'creative_corner_mediums' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_mediums.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerMediums())],
            'creative_corner_software_tools' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_software_tools.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerSoftwareTools())],
            'creative_corner_materials' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_materials.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerMaterials())],
            'creative_corner_creation_date' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'date'],
            'creative_corner_time_taken' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:120'],
            'creative_corner_difficulty_level' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::creativeCornerDifficultyLevels())],
            'creative_corner_themes' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_themes.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerThemes())],
            'creative_corner_location_country' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:120'],
            'creative_corner_location_state' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:120'],
            'creative_corner_location_district' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:120'],
            'creative_corner_location_city' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:120'],
            'creative_corner_material_cost' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:60'],
            'creative_corner_equipment_cost' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:60'],
            'creative_corner_total_cost' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:60'],
            'creative_corner_submit_to_competition' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'boolean'],
            'creative_corner_competition_categories' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_competition_categories.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerCompetitionCategories())],
            'creative_corner_available_for_sale' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'boolean'],
            'creative_corner_sale_price' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:60'],
            'creative_corner_custom_orders_accepted' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'boolean'],
            'creative_corner_limited_edition' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'boolean'],
            'creative_corner_shipping_available' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'boolean'],
            'creative_corner_commission_options' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_commission_options.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerCommissionOptions())],
            'creative_corner_copyright' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::creativeCornerCopyrightOptions())],
            'creative_corner_social_portfolio' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:255'],
            'creative_corner_social_instagram' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:255'],
            'creative_corner_social_youtube' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:255'],
            'creative_corner_social_website' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'url', 'max:255'],
            'creative_corner_social_vendor_profile' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:255'],
            'creative_corner_video_type' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::creativeCornerVideoExamples())],
            'creative_corner_audio_type' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::creativeCornerAudioExamples())],
            'creative_corner_audio_file' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'file', 'mimes:mp3,wav,ogg,webm,mpeg', 'max:'.self::MAX_STORY_AUDIO_KB],
            'remove_creative_corner_audio' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'boolean'],
            'creative_corner_document_types' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_document_types.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerDocumentTypes())],
            'creative_corner_documents' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array', 'max:'.self::MAX_CREATIVE_CORNER_DOCUMENTS],
            'creative_corner_documents.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'removed_creative_corner_documents' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'removed_creative_corner_documents.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', 'max:255'],
            'creative_corner_gallery' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array', 'max:'.self::MAX_CREATIVE_CORNER_GALLERY],
            'creative_corner_gallery.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'image', 'max:4096'],
            'removed_creative_corner_gallery' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'removed_creative_corner_gallery.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', 'max:255'],
            'creative_corner_ask_community' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:500'],
            'creative_corner_allow_poll' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'boolean'],
            'creative_corner_poll_question' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:255'],
            'creative_corner_poll_options' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:2000'],
            'creative_corner_comment_settings' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_comment_settings.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerCommentSettings())],
            'creative_corner_creative_licenses' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_creative_licenses.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerCreativeLicenses())],
            'creative_corner_collaboration_roles' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'array'],
            'creative_corner_collaboration_roles.*' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'string', Rule::in(CommunityContentTaxonomy::creativeCornerCollaborationRoles())],
            'creative_corner_ai_used' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::creativeCornerAiUsageOptions())],
            'creative_corner_ai_tool' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:160'],
            'creative_corner_ai_description' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'nullable', 'string', 'max:2000'],
            'creative_corner_declaration_original' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'accepted'],
            'creative_corner_declaration_no_infringement' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'accepted'],
            'creative_corner_declaration_ai_disclosed' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'accepted'],
            'creative_corner_declaration_guidelines' => [Rule::excludeIf(fn () => ! $isCreativeCorner), 'accepted'],
            'competitions_competition_type' => [
                Rule::excludeIf(fn () => ! $isCompetitions),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::competitionsCompetitionTypes()),
            ],
            'competitions_category' => [
                Rule::excludeIf(fn () => ! $isCompetitions),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::competitionsMainCategories()),
            ],
            'competitions_organizer_name' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_organizer_organization' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_organizer_contact_person' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_organizer_email' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'email', 'max:160'],
            'competitions_organizer_phone' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:40'],
            'competitions_organizer_website' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'url', 'max:255'],
            'competitions_organizer_logo' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'image', 'max:4096'],
            'removed_competitions_organizer_logo' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_eligibility' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_eligibility.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsEligibilityGroups())],
            'competitions_themes' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_themes.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsThemes())],
            'competitions_submission_types' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_submission_types.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsSubmissionTypes())],
            'competitions_max_files' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'integer', 'min:1', 'max:50'],
            'competitions_max_file_size' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:40'],
            'competitions_allowed_formats' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:255'],
            'competitions_level' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::competitionsLevels())],
            'competitions_date_announcement' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'date'],
            'competitions_date_registration_opens' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'date'],
            'competitions_date_registration_closes' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'date'],
            'competitions_date_submission_deadline' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'date'],
            'competitions_date_evaluation_period' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:120'],
            'competitions_date_result' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'date'],
            'competitions_date_award_ceremony' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'date'],
            'competitions_registration_required' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_registration_fee' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:60'],
            'competitions_max_participants' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'integer', 'min:1'],
            'competitions_team_allowed' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_individual_only' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_team_min_members' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'integer', 'min:1'],
            'competitions_team_max_members' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'integer', 'min:1'],
            'competitions_team_details' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_team_details.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsTeamDetailOptions())],
            'competitions_entry_fields' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_entry_fields.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsEntryFields())],
            'competitions_supporting_documents' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_supporting_documents.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsSupportingDocumentTypes())],
            'competitions_judging_criteria' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_judging_criteria.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsJudgingCriteriaOptions())],
            'competitions_judging_weightage' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:2000'],
            'competitions_jury' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array', 'max:'.self::MAX_COMPETITIONS_JURY],
            'competitions_jury.*.name' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_jury.*.designation' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_jury.*.organization' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_jury.*.profile' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:2000'],
            'competitions_jury_photos' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_jury_photos.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'image', 'max:4096'],
            'competitions_jury_remove_photo' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_jury_remove_photo.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'integer', 'min:0'],
            'competitions_prize_first' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:255'],
            'competitions_prize_second' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:255'],
            'competitions_prize_third' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:255'],
            'competitions_prize_consolation' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:255'],
            'competitions_prize_certificates' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_prize_trophies' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_prize_cash' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_prize_gift_voucher' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_prize_internship' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_prize_scholarship' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_prize_featured_homepage' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_certificate_participation' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_certificate_winner' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_certificate_merit' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_certificate_digital' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_sponsors' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array', 'max:'.self::MAX_COMPETITIONS_SPONSORS],
            'competitions_sponsors.*.name' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_sponsors.*.website' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'url', 'max:255'],
            'competitions_sponsors.*.contribution' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:255'],
            'competitions_sponsor_logos' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_sponsor_logos.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'image', 'max:4096'],
            'competitions_sponsor_remove_logo' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_sponsor_remove_logo.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'integer', 'min:0'],
            'competitions_voting_system' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::competitionsVotingSystems())],
            'competitions_public_voting_methods' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_public_voting_methods.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsPublicVotingMethods())],
            'competitions_comment_settings' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_comment_settings.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsCommentSettings())],
            'competitions_copyright_options' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_copyright_options.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsCopyrightOptions())],
            'competitions_ai_used' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::competitionsAiUsageOptions())],
            'competitions_ai_tool' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:160'],
            'competitions_ai_extent' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:2000'],
            'competitions_declaration_original' => [Rule::excludeIf(fn () => ! $isCompetitions), 'accepted'],
            'competitions_declaration_permission' => [Rule::excludeIf(fn () => ! $isCompetitions), 'accepted'],
            'competitions_declaration_ai_disclosed' => [Rule::excludeIf(fn () => ! $isCompetitions), 'accepted'],
            'competitions_declaration_rules' => [Rule::excludeIf(fn () => ! $isCompetitions), 'accepted'],
            'competitions_declaration_display' => [Rule::excludeIf(fn () => ! $isCompetitions), 'accepted'],
            'competitions_enable_multi_section' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_origin_sections' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_origin_sections.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsOriginSections())],
            'competitions_primary_origin_section' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::competitionsOriginSections())],
            'competitions_enable_auto_portfolio' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_enable_entry_qr_codes' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_enable_achievement_badges' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_award_badges' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_award_badges.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsAwardBadges())],
            'competitions_enable_leaderboards' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_leaderboard_types' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_leaderboard_types.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsLeaderboardTypes())],
            'competitions_enable_institution_dashboard' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_institution_dashboard_notes' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:2000'],
            'competitions_enable_sponsored_branding' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_sponsored_branding_notes' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'string', 'max:2000'],
            'competitions_enable_ecommerce' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_ecommerce_options' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_ecommerce_options.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsEcommerceOptions())],
            'competitions_enable_voting_fraud_protection' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_voting_fraud_protections' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_voting_fraud_protections.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsVotingFraudProtections())],
            'competitions_enable_digital_certificates' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'competitions_digital_certificate_types' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'array'],
            'competitions_digital_certificate_types.*' => [Rule::excludeIf(fn () => ! $isCompetitions), 'string', Rule::in(CommunityContentTaxonomy::competitionsDigitalCertificateTypes())],
            'competitions_enable_verifiable_certificate_ids' => [Rule::excludeIf(fn () => ! $isCompetitions), 'nullable', 'boolean'],
            'community_issue_severity' => [
                Rule::excludeIf(fn () => ! $isCommunityIssues),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::communityIssueSeverityLevels()),
            ],
            'community_issue_affected_population' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::communityIssueAffectedPopulationRanges())],
            'community_issue_affected_groups' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'array'],
            'community_issue_affected_groups.*' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'string', Rule::in(CommunityContentTaxonomy::communityIssueAffectedGroups())],
            'location_landmark' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', 'max:160'],
            'community_issue_first_noticed_on' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'date'],
            'community_issue_is_recurring' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', Rule::in(['yes', 'no'])],
            'community_issue_frequency' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::communityIssueRecurringFrequencies())],
            'community_issue_authority' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::communityIssueAuthorities())],
            'community_issue_already_reported' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', Rule::in(['yes', 'no'])],
            'community_issue_complaint_number' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', 'max:120'],
            'community_issue_complaint_date' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'date'],
            'community_issue_department_contacted' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', 'max:160'],
            'community_issue_suggested_solution' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', 'max:3000'],
            'community_issue_support_requests' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'array'],
            'community_issue_support_requests.*' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'string', Rule::in(CommunityContentTaxonomy::communityIssueSupportRequests())],
            'community_issue_status_tracker' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::communityIssueStatusSteps())],
            'community_issue_resolution_timeline' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', 'max:4000'],
            'community_issue_allow_campaign' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'boolean'],
            'community_issue_allow_support' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'boolean'],
            'community_issue_allow_follow' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'boolean'],
            'community_issue_allow_verification' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'boolean'],
            'community_issue_escalation_threshold' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'integer', 'min:10', 'max:10000'],
            'community_issue_poll_question' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', 'max:255'],
            'community_issue_poll_options' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'string', 'max:2000'],
            'community_issue_visibility' => [
                Rule::excludeIf(fn () => ! $isCommunityIssues),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::communityIssueVisibilitySettings())),
            ],
            'community_issue_photo_evidence' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'array', 'max:'.self::MAX_COMMUNITY_ISSUE_PHOTO_EVIDENCE],
            'community_issue_photo_evidence.*' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'image', 'max:4096'],
            'removed_community_issue_photo_evidence' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'array'],
            'removed_community_issue_photo_evidence.*' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'string', 'max:255'],
            'community_issue_documents' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'array', 'max:'.self::MAX_COMMUNITY_ISSUE_DOCUMENTS],
            'community_issue_documents.*' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'removed_community_issue_documents' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'nullable', 'array'],
            'removed_community_issue_documents.*' => [Rule::excludeIf(fn () => ! $isCommunityIssues), 'string', 'max:255'],
            'local_voice_issue_type' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceIssueTypes()),
            ],
            'local_voice_affected_communities' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'array'],
            'local_voice_affected_communities.*' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceAffectedCommunities()),
            ],
            'local_voice_impact_level' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceImpactLevels()),
            ],
            'local_voice_photo_evidence' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'nullable',
                'array',
                'max:'.self::MAX_LOCAL_VOICE_PHOTO_EVIDENCE,
            ],
            'local_voice_photo_evidence.*' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'image',
                'max:4096',
            ],
            'removed_local_voice_photo_evidence' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'array'],
            'removed_local_voice_photo_evidence.*' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'string', 'max:255'],
            'local_voice_video_type' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceVideoTypes()),
            ],
            'local_voice_documents' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'nullable',
                'array',
                'max:'.self::MAX_LOCAL_VOICE_DOCUMENTS,
            ],
            'local_voice_documents.*' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'file',
                'max:20480',
                'mimes:pdf,doc,docx',
            ],
            'removed_local_voice_documents' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'array'],
            'removed_local_voice_documents.*' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'string', 'max:255'],
            'local_voice_suggested_solution' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:3000'],
            'local_voice_estimated_benefit' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:500'],
            'local_voice_authorities' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'array'],
            'local_voice_authorities.*' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceAuthorities()),
            ],
            'local_voice_call_for_action' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'array'],
            'local_voice_call_for_action.*' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceCallForActionExamples()),
            ],
            'local_voice_status_tracker' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceStatusTrackerSteps()),
            ],
            'local_voice_poll_question' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:255'],
            'local_voice_poll_options' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:2000'],
            'local_voice_allow_support' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'boolean'],
            'local_voice_allow_follow' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'boolean'],
            'local_voice_hero_name' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:160'],
            'local_voice_hero_location' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:160'],
            'local_voice_hero_contribution' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:2000'],
            'local_voice_hero_achievements' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:2000'],
            'local_voice_hero_images' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'nullable',
                'array',
                'max:'.self::MAX_LOCAL_VOICE_HERO_IMAGES,
            ],
            'local_voice_hero_images.*' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'image', 'max:4096'],
            'removed_local_voice_hero_images' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'array'],
            'removed_local_voice_hero_images.*' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'string', 'max:255'],
            'local_voice_initiatives' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'array'],
            'local_voice_initiatives.*' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'string',
                Rule::in(CommunityContentTaxonomy::localVoiceInitiativeExamples()),
            ],
            'local_voice_event_date' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'date'],
            'local_voice_event_time' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:40'],
            'local_voice_event_venue' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:160'],
            'local_voice_event_organizer' => [Rule::excludeIf(fn () => ! $isLocalVoices), 'nullable', 'string', 'max:160'],
            'local_voice_visibility' => [
                Rule::excludeIf(fn () => ! $isLocalVoices),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::localVoiceVisibilitySettings())),
            ],
            'my_area_activity_type' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::myAreaActivityTypes()),
            ],
            'my_area_topic_category' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::myAreaTopicCategories()),
            ],
            'my_area_impact_level' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::myAreaImpactLevels()),
            ],
            'my_area_affected_communities' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'array'],
            'my_area_affected_communities.*' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'string',
                Rule::in(CommunityContentTaxonomy::myAreaAffectedCommunities()),
            ],
            'my_area_status_tracker' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::myAreaStatusTrackerSteps()),
            ],
            'my_area_authorities' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'array'],
            'my_area_authorities.*' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'string',
                Rule::in(CommunityContentTaxonomy::myAreaAuthorities()),
            ],
            'my_area_suggested_solution' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:3000'],
            'my_area_hero_name' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:160'],
            'my_area_hero_location' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:160'],
            'my_area_hero_contribution' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:2000'],
            'my_area_achievement_title' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:200'],
            'my_area_achievement_description' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:3000'],
            'my_area_poll_question' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:255'],
            'my_area_poll_options' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'string', 'max:2000'],
            'my_area_photo_evidence' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'nullable',
                'array',
                'max:'.self::MAX_MY_AREA_PHOTO_EVIDENCE,
            ],
            'my_area_photo_evidence.*' => [Rule::excludeIf(fn () => ! $isMyArea), 'image', 'max:4096'],
            'removed_my_area_photo_evidence' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'array'],
            'my_area_documents' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'nullable',
                'array',
                'max:'.self::MAX_MY_AREA_DOCUMENTS,
            ],
            'my_area_documents.*' => [Rule::excludeIf(fn () => ! $isMyArea), 'file', 'max:20480', 'mimes:pdf,doc,docx'],
            'removed_my_area_documents' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'array'],
            'my_area_hero_images' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'nullable',
                'array',
                'max:'.self::MAX_MY_AREA_HERO_IMAGES,
            ],
            'my_area_hero_images.*' => [Rule::excludeIf(fn () => ! $isMyArea), 'image', 'max:4096'],
            'removed_my_area_hero_images' => [Rule::excludeIf(fn () => ! $isMyArea), 'nullable', 'array'],
            'my_area_visibility' => [
                Rule::excludeIf(fn () => ! $isMyArea),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::myAreaVisibilitySettings())),
            ],
            'youth_corner_content_type' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'required',
                'string',
                Rule::in(CommunityContentTaxonomy::youthCornerContentTypes()),
            ],
            'youth_corner_age_group' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::youthCornerAgeGroups()),
            ],
            'youth_corner_occupation' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::youthCornerOccupations()),
            ],
            'youth_corner_education_level' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::youthCornerEducationLevels()),
            ],
            'youth_corner_target_audience' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'nullable',
                'array',
            ],
            'youth_corner_target_audience.*' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'string',
                Rule::in(CommunityContentTaxonomy::youthCornerTargetAudiences()),
            ],
            'youth_corner_opportunity_types' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'youth_corner_opportunity_types.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', Rule::in(CommunityContentTaxonomy::youthCornerOpportunityTypes())],
            'youth_corner_skills' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'youth_corner_skills.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', Rule::in(CommunityContentTaxonomy::youthCornerSkills())],
            'youth_corner_career_area' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::youthCornerCareerAreas())],
            'youth_corner_startup_name' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:200'],
            'youth_corner_startup_industry' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:160'],
            'youth_corner_business_idea' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:3000'],
            'youth_corner_funding_stage' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::youthCornerFundingStages())],
            'youth_corner_startup_challenges' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:3000'],
            'youth_corner_startup_lessons' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:3000'],
            'youth_corner_project_title' => [Rule::excludeIf(fn () => ! $isYouthCornerProject), 'required', 'string', 'max:200'],
            'youth_corner_project_category' => [Rule::excludeIf(fn () => ! $isYouthCornerProject), 'required', 'string', Rule::in(CommunityContentTaxonomy::youthCornerProjectCategories())],
            'youth_corner_project_description' => [Rule::excludeIf(fn () => ! $isYouthCornerProject), 'required', 'string', 'max:5000'],
            'youth_corner_project_outcome' => [Rule::excludeIf(fn () => ! $isYouthCornerProject), 'nullable', 'string', 'max:3000'],
            'youth_corner_documents' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array', 'max:'.self::MAX_YOUTH_CORNER_DOCUMENTS],
            'youth_corner_documents.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip'],
            'removed_youth_corner_documents' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'removed_youth_corner_documents.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', 'max:255'],
            'youth_corner_video_type' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', Rule::in(CommunityContentTaxonomy::youthCornerVideoTypes())],
            'youth_corner_gallery' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array', 'max:'.self::MAX_YOUTH_CORNER_GALLERY],
            'youth_corner_gallery.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'image', 'max:4096'],
            'removed_youth_corner_gallery' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'removed_youth_corner_gallery.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', 'max:255'],
            'youth_corner_themes' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'youth_corner_themes.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', Rule::in(CommunityContentTaxonomy::youthCornerThemes())],
            'youth_corner_community_service' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'youth_corner_community_service.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', Rule::in(CommunityContentTaxonomy::youthCornerCommunityServiceActivities())],
            'youth_corner_networking_options' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'youth_corner_networking_options.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', Rule::in(CommunityContentTaxonomy::youthCornerNetworkingOptions())],
            'youth_corner_ask_community' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:500'],
            'youth_corner_poll_question' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:255'],
            'youth_corner_poll_options' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:2000'],
            'youth_corner_mentorship_requests' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array'],
            'youth_corner_mentorship_requests.*' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'string', Rule::in(CommunityContentTaxonomy::youthCornerMentorshipRequests())],
            'youth_corner_visibility' => [
                Rule::excludeIf(fn () => ! $isYouthCorner),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::youthCornerVisibilitySettings())),
            ],
            'youth_corner_achievements' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'array', 'max:'.self::MAX_YOUTH_CORNER_ACHIEVEMENTS],
            'youth_corner_achievements.*.achievement_title' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:160'],
            'youth_corner_achievements.*.year' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:10'],
            'youth_corner_achievements.*.certificate' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'file', 'max:4096', 'mimes:pdf,jpg,jpeg,png,webp'],
            'youth_corner_achievements.*.existing_certificate_path' => [Rule::excludeIf(fn () => ! $isYouthCorner), 'nullable', 'string', 'max:255'],
            'senior_citizens_forum_age_group' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumAgeGroups()),
            ],
            'senior_citizens_forum_life_journey_categories' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'array',
            ],
            'senior_citizens_forum_life_journey_categories.*' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumLifeJourneyCategories()),
            ],
            'senior_citizens_forum_key_lessons' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'array',
                'max:15',
            ],
            'senior_citizens_forum_key_lessons.*' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:300',
            ],
            'senior_citizens_forum_audio_source_type' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                Rule::in(['none', 'upload', 'recording']),
            ],
            'senior_citizens_forum_audio_file' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum
                    || $request->input('senior_citizens_forum_audio_source_type') !== 'upload'
                    || $request->boolean('keep_existing_senior_citizens_forum_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'senior_citizens_forum_audio_recording' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum
                    || $request->input('senior_citizens_forum_audio_source_type') !== 'recording'
                    || $request->boolean('keep_existing_senior_citizens_forum_audio')),
                'nullable',
                'file',
                'max:'.self::MAX_STORY_AUDIO_KB,
                'mimetypes:audio/mpeg,audio/mp3,audio/x-m4a,audio/wav,audio/webm,audio/ogg,audio/x-wav',
            ],
            'keep_existing_senior_citizens_forum_audio' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'boolean',
            ],
            'remove_senior_citizens_forum_audio' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'boolean',
            ],
            'senior_citizens_forum_video_type' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumVideoTypes()),
            ],
            'senior_citizens_forum_family_background' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:3000',
            ],
            'senior_citizens_forum_traditions' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:3000',
            ],
            'senior_citizens_forum_cultural_practices' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:3000',
            ],
            'senior_citizens_forum_family_values' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:3000',
            ],
            'senior_citizens_forum_themes' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'array',
            ],
            'senior_citizens_forum_themes.*' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumThemes()),
            ],
            'senior_citizens_forum_advice_to_youth' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:5000',
            ],
            'senior_citizens_forum_community_contributions' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'array',
            ],
            'senior_citizens_forum_community_contributions.*' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumCommunityContributions()),
            ],
            'senior_citizens_forum_achievements' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'array',
                'max:'.self::MAX_SENIOR_CITIZENS_FORUM_ACHIEVEMENTS,
            ],
            'senior_citizens_forum_achievements.*.award_name' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:160',
            ],
            'senior_citizens_forum_achievements.*.year' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:10',
            ],
            'senior_citizens_forum_achievements.*.description' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:1000',
            ],
            'senior_citizens_forum_achievements.*.existing_photo_path' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:255',
            ],
            'senior_citizens_forum_achievements.*.existing_certificate_path' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:255',
            ],
            'senior_citizens_forum_achievements.*.photo' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'image',
                'max:4096',
                'mimes:jpg,jpeg,png,webp,gif',
            ],
            'senior_citizens_forum_achievements.*.certificate' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,webp',
            ],
            'senior_citizens_forum_ask_community' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'string',
                'max:500',
            ],
            'senior_citizens_forum_visibility' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'required',
                'string',
                Rule::in(array_keys(CommunityContentTaxonomy::seniorCitizensForumVisibilitySettings())),
            ],
            'senior_citizens_forum_intergenerational_connections' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'array',
            ],
            'senior_citizens_forum_intergenerational_connections.*' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'string',
                Rule::in(CommunityContentTaxonomy::seniorCitizensForumIntergenerationalConnections()),
            ],
            'senior_citizens_forum_preserve_digital_legacy' => [
                Rule::excludeIf(fn () => ! $isSeniorCitizensForum),
                'nullable',
                'boolean',
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
                Rule::excludeIf(fn () => ! $isChildrensCornerQuiz),
                Rule::requiredIf(fn () => $isChildrensCornerQuiz),
                'nullable',
                'array',
                'min:1',
                'max:'.self::MAX_CHILDRENS_CORNER_QUIZ_QUESTIONS,
            ],
            'childrens_corner_quiz.*.question' => [
                Rule::excludeIf(fn () => ! $isChildrensCornerQuiz),
                'required',
                'string',
                'max:500',
            ],
            'childrens_corner_quiz.*.options' => [
                Rule::excludeIf(fn () => ! $isChildrensCornerQuiz),
                'required',
                'array',
                'min:2',
                'max:6',
            ],
            'childrens_corner_quiz.*.options.*' => [
                Rule::excludeIf(fn () => ! $isChildrensCornerQuiz),
                'nullable',
                'string',
                'max:255',
            ],
            'childrens_corner_quiz.*.correct_answer' => [
                Rule::excludeIf(fn () => ! $isChildrensCornerQuiz),
                'required',
                'string',
                'max:255',
            ],
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
        $this->assertEnvironmentFeaturedImage($request, $post);

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

        if ($isCommunityIssues) {
            $validated['category'] = (string) ($validated['community_issue_category'] ?? $request->input('community_issue_category'));
        }

        if ($isAgriculture) {
            $validated['category'] = (string) ($validated['agriculture_category'] ?? $request->input('agriculture_category'));
        }

        if ($isEnvironment) {
            $validated['category'] = (string) ($validated['environment_category'] ?? $request->input('environment_category'));
        }

        if ($isScienceTechnology) {
            $validated['category'] = (string) ($validated['science_technology_category'] ?? $request->input('science_technology_category'));
        }

        if ($isAstroConsultancy) {
            $validated['category'] = (string) ($validated['astro_consultancy_category'] ?? $request->input('astro_consultancy_category'));
        }

        if ($isReligionSpirituality) {
            $validated['category'] = (string) ($validated['religion_spirituality_category'] ?? $request->input('religion_spirituality_category'));
        }

        if ($isCreativeCorner) {
            $validated['category'] = (string) ($validated['creative_corner_category'] ?? $request->input('creative_corner_category'));
        }

        if ($isBusiness) {
            $validated['category'] = (string) ($validated['business_category'] ?? $request->input('business_category'));
        }

        if ($isWomensWorld) {
            $validated['category'] = (string) ($validated['womens_world_category'] ?? $request->input('womens_world_category'));
        }

        if ($isSeniorCitizensForum) {
            $validated['category'] = (string) ($validated['senior_citizens_forum_category'] ?? $request->input('senior_citizens_forum_category'));
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

        $validated = $this->convertLegacyHindiFonts($validated);

        app(FoulWordFilter::class)->assertCleanPayload($validated);

        unset($validated['book_pages']);

        if ($isChildrensCorner) {
            $validated = $this->applyChildrensCornerBroadLocation($validated, $request);
        } elseif ($isWomensWorld || $isSeniorCitizensForum) {
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
        } else {
            $validated['publish_as'] = $validated['publish_as'] ?? CommunityPost::PUBLISH_AS_PUBLIC_PROFILE;
            if ($validated['publish_as'] !== CommunityPost::PUBLISH_AS_PEN_NAME) {
                $validated['pen_name'] = null;
            }
        }

        if (($validated['content_type'] ?? null) === 'reports' || ! ($validated['allow_poll'] ?? false)) {
            $validated['allow_poll'] = false;
            $validated['poll_subject'] = null;
        }

        if (! ($validated['allow_poll'] ?? false)) {
            $validated['poll_subject'] = null;
        }

        app(FoulWordFilter::class)->assertCleanPayload($validated);

        return $validated;
    }

    /**
     * @param  list<string>  $typeKeys
     * @return array<string, mixed>
     */
    private function validatedDraft(Request $request, ?CommunityPost $post, array $typeKeys): array
    {
        $this->pruneUnreadableUploads($request);

        $contentType = $request->input('content_type');

        $validated = $request->validate([
            'content_type' => ['required', Rule::in($typeKeys)],
            'writing_purpose' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'status' => ['required', Rule::in([CommunityPost::STATUS_DRAFT])],
            'tags' => ['nullable', 'string', 'max:500'],
            'location_type' => ['nullable', Rule::in(array_keys(CommunityPost::locationTypeOptions(is_string($contentType) ? $contentType : null)))],
            'location' => ['nullable', 'string', 'max:160'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'accept_content_responsibility' => ['nullable'],
            'accept_original_work_indemnity' => ['nullable'],
        ]);

        $validated['title'] = filled($validated['title'] ?? null)
            ? trim((string) $validated['title'])
            : 'Untitled draft';

        $category = trim((string) ($validated['category'] ?? ''));
        if ($category === '' && is_string($contentType)) {
            $category = CommunityContentTaxonomy::firstCategoryFor($contentType);
        }
        $validated['category'] = $category;

        if (CommunityPost::isBookContentType($contentType)) {
            $bookPages = $this->normalizeBookPages(
                $request->input('book_pages', []),
                is_string($contentType) ? $contentType : null
            );

            if ($bookPages !== []) {
                $validated['body'] = CommunityPost::bodyFromBookPages($bookPages);
            }
        }

        $validated['location_type'] = filled($validated['location_type'] ?? null)
            ? $validated['location_type']
            : CommunityPost::LOCATION_TYPE_GLOBAL;

        if (! filled($validated['location'] ?? null)) {
            $validated = array_merge(
                $validated,
                CommunityPost::defaultLocationForType((string) $validated['location_type'])
            );
        }

        $validated['publish_as'] = null;
        $validated['pen_name'] = null;
        $validated['allow_poll'] = false;
        $validated['poll_subject'] = null;

        $validated = $this->convertLegacyHindiFonts($validated);

        app(FoulWordFilter::class)->assertCleanPayload($validated);

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
        $isEnvironment = CommunityPost::usesEnvironmentFlow(is_string($contentType) ? $contentType : null);
        $isScienceTechnology = CommunityPost::usesScienceTechnologyFlow(is_string($contentType) ? $contentType : null);
        $isAstroConsultancy = CommunityPost::usesAstroConsultancyFlow(is_string($contentType) ? $contentType : null);
        $isReligionSpirituality = CommunityPost::usesReligionSpiritualityFlow(is_string($contentType) ? $contentType : null);
        $maxImages = ($isAwareness || $isEnvironment || $isScienceTechnology || $isAstroConsultancy || $isReligionSpirituality) ? 1 : self::MAX_FEATURED_IMAGES;
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
                    : ($isEnvironment
                        ? 'Environment posts can include one featured image.'
                        : ($isScienceTechnology
                            ? 'Science & Technology posts can include one featured image.'
                            : ($isAstroConsultancy
                                ? 'Astro Consultancy posts can include one featured image.'
                                : 'You can upload up to '.self::MAX_FEATURED_IMAGES.' featured images.'))),
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

    private function assertEnvironmentFeaturedImage(Request $request, ?CommunityPost $post = null): void
    {
        $contentType = $request->input('content_type');

        if (! CommunityPost::usesEnvironmentFlow(is_string($contentType) ? $contentType : null)) {
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
                'featured_images' => 'Please upload a featured image for this environment post.',
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

    private function applySeniorCitizensForumPrivacyMeta(array $meta, Request $request, ?CommunityPost $post = null): array
    {
        if ($request->input('content_type') !== 'senior-citizens-forum') {
            return $meta;
        }

        $visibility = array_key_exists(
            (string) $request->input('senior_citizens_forum_visibility'),
            CommunityContentTaxonomy::seniorCitizensForumVisibilitySettings()
        )
            ? (string) $request->input('senior_citizens_forum_visibility')
            : CommunityContentTaxonomy::seniorCitizensForumDefaultVisibilitySetting();

        if ($visibility === 'private_link') {
            $existing = data_get($post?->meta, 'senior_citizens_forum_private_link_token');
            $meta['senior_citizens_forum_private_link_token'] = filled($existing)
                ? $existing
                : \Illuminate\Support\Str::random(48);
        } else {
            unset($meta['senior_citizens_forum_private_link_token']);
        }

        return $meta;
    }

    private function applyStudentCornerPrivacyMeta(array $meta, Request $request, ?CommunityPost $post = null): array
    {
        if ($request->input('content_type') !== 'student-corner') {
            return $meta;
        }

        $visibility = array_key_exists(
            (string) $request->input('student_corner_visibility'),
            CommunityContentTaxonomy::studentCornerVisibilitySettings()
        )
            ? (string) $request->input('student_corner_visibility')
            : CommunityContentTaxonomy::studentCornerDefaultVisibilitySetting();

        if ($visibility === 'private_link') {
            $existing = data_get($post?->meta, 'student_corner_private_link_token');
            $meta['student_corner_private_link_token'] = filled($existing)
                ? $existing
                : \Illuminate\Support\Str::random(48);
        } else {
            unset($meta['student_corner_private_link_token']);
        }

        return $meta;
    }

    private function applyYouthCornerPrivacyMeta(array $meta, Request $request, ?CommunityPost $post = null): array
    {
        if ($request->input('content_type') !== 'youth-corner') {
            return $meta;
        }

        $visibility = array_key_exists(
            (string) $request->input('youth_corner_visibility'),
            CommunityContentTaxonomy::youthCornerVisibilitySettings()
        )
            ? (string) $request->input('youth_corner_visibility')
            : CommunityContentTaxonomy::youthCornerDefaultVisibilitySetting();

        if ($visibility === 'private_link') {
            $existing = data_get($post?->meta, 'youth_corner_private_link_token');
            $meta['youth_corner_private_link_token'] = filled($existing)
                ? $existing
                : \Illuminate\Support\Str::random(48);
        } else {
            unset($meta['youth_corner_private_link_token']);
        }

        return $meta;
    }

    private function applyLocalVoicePrivacyMeta(array $meta, Request $request, ?CommunityPost $post = null): array
    {
        if ($request->input('content_type') !== 'local-voices') {
            return $meta;
        }

        $visibility = array_key_exists(
            (string) $request->input('local_voice_visibility'),
            CommunityContentTaxonomy::localVoiceVisibilitySettings()
        )
            ? (string) $request->input('local_voice_visibility')
            : CommunityContentTaxonomy::localVoiceDefaultVisibilitySetting();

        if ($visibility === 'private_link') {
            $existing = data_get($post?->meta, 'local_voice_private_link_token');
            $meta['local_voice_private_link_token'] = filled($existing)
                ? $existing
                : \Illuminate\Support\Str::random(48);
        } else {
            unset($meta['local_voice_private_link_token']);
        }

        return $meta;
    }

    private function applyMyAreaPrivacyMeta(array $meta, Request $request, ?CommunityPost $post = null): array
    {
        if ($request->input('content_type') !== 'my-area') {
            return $meta;
        }

        $visibility = array_key_exists(
            (string) $request->input('my_area_visibility'),
            CommunityContentTaxonomy::myAreaVisibilitySettings()
        )
            ? (string) $request->input('my_area_visibility')
            : CommunityContentTaxonomy::myAreaDefaultVisibilitySetting();

        if ($visibility === 'private_link') {
            $existing = data_get($post?->meta, 'my_area_private_link_token');
            $meta['my_area_private_link_token'] = filled($existing)
                ? $existing
                : \Illuminate\Support\Str::random(48);
        } else {
            unset($meta['my_area_private_link_token']);
        }

        return $meta;
    }

    private function applyCommunityIssuePrivacyMeta(array $meta, Request $request, ?CommunityPost $post = null): array
    {
        if ($request->input('content_type') !== 'community-issues') {
            return $meta;
        }

        $visibility = array_key_exists(
            (string) $request->input('community_issue_visibility'),
            CommunityContentTaxonomy::communityIssueVisibilitySettings()
        )
            ? (string) $request->input('community_issue_visibility')
            : CommunityContentTaxonomy::communityIssueDefaultVisibilitySetting();

        if ($visibility === 'private_link') {
            $existing = data_get($post?->meta, 'community_issue_private_link_token');
            $meta['community_issue_private_link_token'] = filled($existing)
                ? $existing
                : \Illuminate\Support\Str::random(48);
        } else {
            unset($meta['community_issue_private_link_token']);
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

        if ($type === 'science-technology') {
            return $request->boolean('science_technology_allow_poll');
        }

        if ($type === 'astro-consultancy') {
            return $request->boolean('astro_consultancy_allow_poll');
        }

        if ($type === 'religion-spirituality') {
            return $request->boolean('religion_spirituality_allow_poll');
        }

        if ($type === 'creative-corner') {
            return $request->boolean('creative_corner_allow_poll');
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
    private function storeStudentCornerDocuments(Request $request): array
    {
        return collect($request->file('student_corner_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'student-corner-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveStudentCornerDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesStudentCornerFlow($request->input('content_type'))
            || $request->input('student_corner_content_type') !== CommunityContentTaxonomy::studentCornerProjectContentType()) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'student_corner_documents', []);
        $removed = (array) $request->input('removed_student_corner_documents', []);

        if ($existing === [] && ! $request->hasFile('student_corner_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('student_corner_documents')) {
            $kept = array_values(array_merge($kept, $this->storeStudentCornerDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_STUDENT_CORNER_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeStudentCornerGallery(Request $request): array
    {
        return collect($request->file('student_corner_gallery', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'student-corner-gallery'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveStudentCornerGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesStudentCornerFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'student_corner_gallery', []);
        $removed = (array) $request->input('removed_student_corner_gallery', []);

        if ($existing === [] && ! $request->hasFile('student_corner_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('student_corner_gallery')) {
            $kept = array_values(array_merge($kept, $this->storeStudentCornerGallery($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_STUDENT_CORNER_GALLERY));
    }

    /**
     * @param  list<array<string, mixed>>  $achievements
     */
    private function deleteStudentCornerAchievementFiles(array $achievements): void
    {
        foreach ($achievements as $achievement) {
            CommunityPostFileUploader::deleteIfExists(data_get($achievement, 'certificate.path'));
        }
    }

    /**
     * @return list<array{achievement_title: string, year: string, certificate: array{path: string, url: string, name: string, type: string}|null}>|null
     */
    private function resolveStudentCornerAchievements(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesStudentCornerFlow($request->input('content_type'))) {
            return null;
        }

        $entries = collect($request->input('student_corner_achievements', []))
            ->filter(function (mixed $entry): bool {
                if (! is_array($entry)) {
                    return false;
                }

                return filled($entry['achievement_title'] ?? null)
                    || filled($entry['year'] ?? null)
                    || filled($entry['existing_certificate_path'] ?? null);
            })
            ->values();

        if ($entries->isEmpty()) {
            $this->deleteStudentCornerAchievementFiles((array) data_get($post?->meta, 'student_corner_achievements', []));

            return null;
        }

        $existingByCertificatePath = collect((array) data_get($post?->meta, 'student_corner_achievements', []))
            ->mapWithKeys(function (mixed $entry): array {
                $path = (string) data_get($entry, 'certificate.path', '');

                return filled($path) ? [$path => $entry] : [];
            });

        $resolved = $entries
            ->take(self::MAX_STUDENT_CORNER_ACHIEVEMENTS)
            ->map(function (array $entry, int $index) use ($request, $existingByCertificatePath): array {
                $certificate = null;
                $uploadedCertificate = $request->file("student_corner_achievements.$index.certificate");

                if ($uploadedCertificate) {
                    $existingPath = (string) ($entry['existing_certificate_path'] ?? '');
                    if (filled($existingPath)) {
                        CommunityPostFileUploader::deleteIfExists($existingPath);
                    }

                    $certificate = CommunityPostFileUploader::storeAttachment($uploadedCertificate, 'student-corner-certificates');
                } elseif (filled($entry['existing_certificate_path'] ?? null)) {
                    $existingPath = (string) $entry['existing_certificate_path'];
                    $existingCertificate = data_get($existingByCertificatePath->get($existingPath), 'certificate');

                    if (is_array($existingCertificate)) {
                        $certificate = $existingCertificate;
                    }
                }

                return [
                    'achievement_title' => trim((string) ($entry['achievement_title'] ?? '')),
                    'year' => trim((string) ($entry['year'] ?? '')),
                    'certificate' => $certificate,
                ];
            })
            ->values()
            ->all();

        $keptPaths = collect($resolved)
            ->map(fn (array $entry): string => (string) data_get($entry, 'certificate.path', ''))
            ->filter()
            ->all();

        foreach ((array) data_get($post?->meta, 'student_corner_achievements', []) as $existingEntry) {
            $path = (string) data_get($existingEntry, 'certificate.path', '');
            if (filled($path) && ! in_array($path, $keptPaths, true)) {
                CommunityPostFileUploader::deleteIfExists($path);
            }
        }

        return $resolved;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeYouthCornerDocuments(Request $request): array
    {
        return collect($request->file('youth_corner_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'youth-corner-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveYouthCornerDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesYouthCornerFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'youth_corner_documents', []);
        $removed = (array) $request->input('removed_youth_corner_documents', []);

        if ($existing === [] && ! $request->hasFile('youth_corner_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('youth_corner_documents')) {
            $kept = array_values(array_merge($kept, $this->storeYouthCornerDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_YOUTH_CORNER_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeYouthCornerGallery(Request $request): array
    {
        return collect($request->file('youth_corner_gallery', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'youth-corner-gallery'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveYouthCornerGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesYouthCornerFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'youth_corner_gallery', []);
        $removed = (array) $request->input('removed_youth_corner_gallery', []);

        if ($existing === [] && ! $request->hasFile('youth_corner_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('youth_corner_gallery')) {
            $kept = array_values(array_merge($kept, $this->storeYouthCornerGallery($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_YOUTH_CORNER_GALLERY));
    }

    /**
     * @param  list<array<string, mixed>>  $achievements
     */
    private function deleteYouthCornerAchievementFiles(array $achievements): void
    {
        foreach ($achievements as $achievement) {
            CommunityPostFileUploader::deleteIfExists(data_get($achievement, 'certificate.path'));
        }
    }

    /**
     * @return list<array{achievement_title: string, year: string, certificate: array{path: string, url: string, name: string, type: string}|null}>|null
     */
    private function resolveYouthCornerAchievements(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesYouthCornerFlow($request->input('content_type'))) {
            return null;
        }

        $entries = collect($request->input('youth_corner_achievements', []))
            ->filter(function (mixed $entry): bool {
                if (! is_array($entry)) {
                    return false;
                }

                return filled($entry['achievement_title'] ?? null)
                    || filled($entry['year'] ?? null)
                    || filled($entry['existing_certificate_path'] ?? null);
            })
            ->values();

        if ($entries->isEmpty()) {
            $this->deleteYouthCornerAchievementFiles((array) data_get($post?->meta, 'youth_corner_achievements', []));

            return null;
        }

        $existingByCertificatePath = collect((array) data_get($post?->meta, 'youth_corner_achievements', []))
            ->mapWithKeys(function (mixed $entry): array {
                $path = (string) data_get($entry, 'certificate.path', '');

                return filled($path) ? [$path => $entry] : [];
            });

        $resolved = $entries
            ->take(self::MAX_YOUTH_CORNER_ACHIEVEMENTS)
            ->map(function (array $entry, int $index) use ($request, $existingByCertificatePath): array {
                $certificate = null;
                $uploadedCertificate = $request->file("youth_corner_achievements.$index.certificate");

                if ($uploadedCertificate) {
                    $existingPath = (string) ($entry['existing_certificate_path'] ?? '');
                    if (filled($existingPath)) {
                        CommunityPostFileUploader::deleteIfExists($existingPath);
                    }

                    $certificate = CommunityPostFileUploader::storeAttachment($uploadedCertificate, 'youth-corner-certificates');
                } elseif (filled($entry['existing_certificate_path'] ?? null)) {
                    $existingPath = (string) $entry['existing_certificate_path'];
                    $existingCertificate = data_get($existingByCertificatePath->get($existingPath), 'certificate');

                    if (is_array($existingCertificate)) {
                        $certificate = $existingCertificate;
                    }
                }

                return [
                    'achievement_title' => trim((string) ($entry['achievement_title'] ?? '')),
                    'year' => trim((string) ($entry['year'] ?? '')),
                    'certificate' => $certificate,
                ];
            })
            ->values()
            ->all();

        $keptPaths = collect($resolved)
            ->map(fn (array $entry): string => (string) data_get($entry, 'certificate.path', ''))
            ->filter()
            ->all();

        foreach ((array) data_get($post?->meta, 'youth_corner_achievements', []) as $existingEntry) {
            $path = (string) data_get($existingEntry, 'certificate.path', '');
            if (filled($path) && ! in_array($path, $keptPaths, true)) {
                CommunityPostFileUploader::deleteIfExists($path);
            }
        }

        return $resolved;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeLocalVoicePhotoEvidence(Request $request): array
    {
        return collect($request->file('local_voice_photo_evidence', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'local-voice-photo-evidence'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveLocalVoicePhotoEvidence(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesLocalVoicesFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'local_voice_photo_evidence', []);
        $removed = (array) $request->input('removed_local_voice_photo_evidence', []);

        if ($existing === [] && ! $request->hasFile('local_voice_photo_evidence')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $photo): bool => in_array((string) data_get($photo, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('local_voice_photo_evidence')) {
            $kept = array_values(array_merge($kept, $this->storeLocalVoicePhotoEvidence($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_LOCAL_VOICE_PHOTO_EVIDENCE));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeLocalVoiceDocuments(Request $request): array
    {
        return collect($request->file('local_voice_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'local-voice-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveLocalVoiceDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesLocalVoicesFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'local_voice_documents', []);
        $removed = (array) $request->input('removed_local_voice_documents', []);

        if ($existing === [] && ! $request->hasFile('local_voice_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('local_voice_documents')) {
            $kept = array_values(array_merge($kept, $this->storeLocalVoiceDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_LOCAL_VOICE_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeLocalVoiceHeroImages(Request $request): array
    {
        return collect($request->file('local_voice_hero_images', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'local-voice-hero-images'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveLocalVoiceHeroImages(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesLocalVoicesFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'local_voice_hero_images', []);
        $removed = (array) $request->input('removed_local_voice_hero_images', []);

        if ($existing === [] && ! $request->hasFile('local_voice_hero_images')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('local_voice_hero_images')) {
            $kept = array_values(array_merge($kept, $this->storeLocalVoiceHeroImages($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_LOCAL_VOICE_HERO_IMAGES));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeMyAreaPhotoEvidence(Request $request): array
    {
        return collect($request->file('my_area_photo_evidence', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'my-area-photo-evidence'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveMyAreaPhotoEvidence(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesMyAreaFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'my_area_photo_evidence', []);
        $removed = (array) $request->input('removed_my_area_photo_evidence', []);

        if ($existing === [] && ! $request->hasFile('my_area_photo_evidence')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $photo): bool => in_array((string) data_get($photo, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('my_area_photo_evidence')) {
            $kept = array_values(array_merge($kept, $this->storeMyAreaPhotoEvidence($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_MY_AREA_PHOTO_EVIDENCE));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeMyAreaDocuments(Request $request): array
    {
        return collect($request->file('my_area_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'my-area-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveMyAreaDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesMyAreaFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'my_area_documents', []);
        $removed = (array) $request->input('removed_my_area_documents', []);

        if ($existing === [] && ! $request->hasFile('my_area_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('my_area_documents')) {
            $kept = array_values(array_merge($kept, $this->storeMyAreaDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_MY_AREA_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeMyAreaHeroImages(Request $request): array
    {
        return collect($request->file('my_area_hero_images', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'my-area-hero-images'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveMyAreaHeroImages(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesMyAreaFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'my_area_hero_images', []);
        $removed = (array) $request->input('removed_my_area_hero_images', []);

        if ($existing === [] && ! $request->hasFile('my_area_hero_images')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('my_area_hero_images')) {
            $kept = array_values(array_merge($kept, $this->storeMyAreaHeroImages($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_MY_AREA_HERO_IMAGES));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeCommunityIssuePhotoEvidence(Request $request): array
    {
        return collect($request->file('community_issue_photo_evidence', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'community-issue-photo-evidence'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveCommunityIssuePhotoEvidence(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCommunityIssuesFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'community_issue_photo_evidence', []);
        $removed = (array) $request->input('removed_community_issue_photo_evidence', []);

        if ($existing === [] && ! $request->hasFile('community_issue_photo_evidence')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $photo): bool => in_array((string) data_get($photo, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('community_issue_photo_evidence')) {
            $kept = array_values(array_merge($kept, $this->storeCommunityIssuePhotoEvidence($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_COMMUNITY_ISSUE_PHOTO_EVIDENCE));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeCommunityIssueDocuments(Request $request): array
    {
        return collect($request->file('community_issue_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'community-issue-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveCommunityIssueDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCommunityIssuesFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'community_issue_documents', []);
        $removed = (array) $request->input('removed_community_issue_documents', []);

        if ($existing === [] && ! $request->hasFile('community_issue_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('community_issue_documents')) {
            $kept = array_values(array_merge($kept, $this->storeCommunityIssueDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_COMMUNITY_ISSUE_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeAgricultureProblemPhotos(Request $request): array
    {
        return collect($request->file('agriculture_problem_photos', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'agriculture-problem-photos'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveAgricultureProblemPhotos(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAgricultureFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'agriculture_problem_photos', []);
        $removed = (array) $request->input('removed_agriculture_problem_photos', []);

        if ($existing === [] && ! $request->hasFile('agriculture_problem_photos')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $photo): bool => in_array((string) data_get($photo, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('agriculture_problem_photos')) {
            $kept = array_values(array_merge($kept, $this->storeAgricultureProblemPhotos($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_AGRICULTURE_PROBLEM_PHOTOS));
    }

    /**
     * @return array<string, list<array{path: string, url: string, name: string, type: string}>>|null
     */
    private function resolveAgricultureGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAgricultureFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'agriculture_gallery', []);
        $gallery = [];
        $hasUploads = false;

        foreach (array_keys(CommunityContentTaxonomy::agricultureGalleryCategories()) as $categoryKey) {
            $inputName = 'agriculture_gallery_'.$categoryKey;
            $removedInput = 'removed_agriculture_gallery_'.$categoryKey;
            $categoryExisting = (array) data_get($existing, $categoryKey, []);
            $removed = (array) $request->input($removedInput, []);

            if ($categoryExisting === [] && ! $request->hasFile($inputName)) {
                continue;
            }

            $kept = collect($categoryExisting)
                ->reject(fn (array $photo): bool => in_array((string) data_get($photo, 'path'), $removed, true))
                ->values()
                ->all();

            foreach ($removed as $path) {
                CommunityPostFileUploader::deleteIfExists($path);
            }

            if ($request->hasFile($inputName)) {
                $hasUploads = true;
                $uploaded = collect($request->file($inputName, []))
                    ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'agriculture-gallery-'.$categoryKey))
                    ->values()
                    ->all();
                $kept = array_values(array_merge($kept, $uploaded));
            }

            $kept = array_values(array_slice($kept, 0, self::MAX_AGRICULTURE_GALLERY_PER_CATEGORY));
            if ($kept !== []) {
                $gallery[$categoryKey] = $kept;
            }
        }

        if ($gallery === [] && ! $hasUploads && $existing === []) {
            return null;
        }

        return $gallery === [] ? null : $gallery;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeAgricultureDocuments(Request $request): array
    {
        return collect($request->file('agriculture_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'agriculture-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveAgricultureDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAgricultureFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'agriculture_documents', []);
        $removed = (array) $request->input('removed_agriculture_documents', []);

        if ($existing === [] && ! $request->hasFile('agriculture_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('agriculture_documents')) {
            $kept = array_values(array_merge($kept, $this->storeAgricultureDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_AGRICULTURE_DOCUMENTS));
    }

    /**
     * @return array<string, list<array{path: string, url: string, name: string, type: string}>>|null
     */
    private function resolveEnvironmentGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesEnvironmentFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'environment_gallery', []);
        $gallery = [];
        $hasUploads = false;

        foreach (array_keys(CommunityContentTaxonomy::environmentGalleryCategories()) as $categoryKey) {
            $inputName = 'environment_gallery_'.$categoryKey;
            $removedInput = 'removed_environment_gallery_'.$categoryKey;
            $categoryExisting = (array) data_get($existing, $categoryKey, []);
            $removed = (array) $request->input($removedInput, []);

            if ($categoryExisting === [] && ! $request->hasFile($inputName)) {
                continue;
            }

            $kept = collect($categoryExisting)
                ->reject(fn (array $photo): bool => in_array((string) data_get($photo, 'path'), $removed, true))
                ->values()
                ->all();

            foreach ($removed as $path) {
                CommunityPostFileUploader::deleteIfExists($path);
            }

            if ($request->hasFile($inputName)) {
                $hasUploads = true;
                $uploaded = collect($request->file($inputName, []))
                    ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'environment-gallery-'.$categoryKey))
                    ->values()
                    ->all();
                $kept = array_values(array_merge($kept, $uploaded));
            }

            $kept = array_values(array_slice($kept, 0, self::MAX_ENVIRONMENT_GALLERY_PER_CATEGORY));
            if ($kept !== []) {
                $gallery[$categoryKey] = $kept;
            }
        }

        if ($gallery === [] && ! $hasUploads && $existing === []) {
            return null;
        }

        return $gallery === [] ? null : $gallery;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeEnvironmentDocuments(Request $request): array
    {
        return collect($request->file('environment_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'environment-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveEnvironmentDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesEnvironmentFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'environment_documents', []);
        $removed = (array) $request->input('removed_environment_documents', []);

        if ($existing === [] && ! $request->hasFile('environment_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('environment_documents')) {
            $kept = array_values(array_merge($kept, $this->storeEnvironmentDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_ENVIRONMENT_DOCUMENTS));
    }

    /**
     * @return array<string, list<array{path: string, url: string, name: string, type: string}>>|null
     */
    private function resolveScienceTechnologyGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesScienceTechnologyFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'science_technology_gallery', []);
        $gallery = [];
        $hasUploads = false;

        foreach (array_keys(CommunityContentTaxonomy::scienceTechnologyGalleryCategories()) as $categoryKey) {
            $inputName = 'science_technology_gallery_'.$categoryKey;
            $removedInput = 'removed_science_technology_gallery_'.$categoryKey;
            $categoryExisting = (array) data_get($existing, $categoryKey, []);
            $removed = (array) $request->input($removedInput, []);

            if ($categoryExisting === [] && ! $request->hasFile($inputName)) {
                continue;
            }

            $kept = collect($categoryExisting)
                ->reject(fn (array $photo): bool => in_array((string) data_get($photo, 'path'), $removed, true))
                ->values()
                ->all();

            foreach ($removed as $path) {
                CommunityPostFileUploader::deleteIfExists($path);
            }

            if ($request->hasFile($inputName)) {
                $hasUploads = true;
                $uploaded = collect($request->file($inputName, []))
                    ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'science-technology-gallery-'.$categoryKey))
                    ->values()
                    ->all();
                $kept = array_values(array_merge($kept, $uploaded));
            }

            $kept = array_values(array_slice($kept, 0, self::MAX_SCIENCE_TECHNOLOGY_GALLERY_PER_CATEGORY));
            if ($kept !== []) {
                $gallery[$categoryKey] = $kept;
            }
        }

        if ($gallery === [] && ! $hasUploads && $existing === []) {
            return null;
        }

        return $gallery === [] ? null : $gallery;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeScienceTechnologyDocuments(Request $request): array
    {
        return collect($request->file('science_technology_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'science-technology-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveScienceTechnologyDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesScienceTechnologyFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'science_technology_documents', []);
        $removed = (array) $request->input('removed_science_technology_documents', []);

        if ($existing === [] && ! $request->hasFile('science_technology_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('science_technology_documents')) {
            $kept = array_values(array_merge($kept, $this->storeScienceTechnologyDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_SCIENCE_TECHNOLOGY_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeAstroConsultancyDocuments(Request $request): array
    {
        return collect($request->file('astro_consultancy_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'astro-consultancy-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveAstroConsultancyDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesAstroConsultancyFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'astro_consultancy_documents', []);
        $removed = (array) $request->input('removed_astro_consultancy_documents', []);

        if ($existing === [] && ! $request->hasFile('astro_consultancy_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('astro_consultancy_documents')) {
            $kept = array_values(array_merge($kept, $this->storeAstroConsultancyDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_ASTRO_CONSULTANCY_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeReligionSpiritualityDocuments(Request $request): array
    {
        return collect($request->file('religion_spirituality_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'religion-spirituality-documents'))
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveReligionSpiritualityDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesReligionSpiritualityFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'religion_spirituality_documents', []);
        $removed = (array) $request->input('removed_religion_spirituality_documents', []);

        if ($existing === [] && ! $request->hasFile('religion_spirituality_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('religion_spirituality_documents')) {
            $kept = array_values(array_merge($kept, $this->storeReligionSpiritualityDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_RELIGION_SPIRITUALITY_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveReligionSpiritualityGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesReligionSpiritualityFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'religion_spirituality_gallery', []);
        $removed = (array) $request->input('removed_religion_spirituality_gallery', []);

        if ($existing === [] && ! $request->hasFile('religion_spirituality_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('religion_spirituality_gallery')) {
            $uploaded = collect($request->file('religion_spirituality_gallery', []))
                ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'religion-spirituality-gallery'))
                ->values()
                ->all();
            $kept = array_values(array_merge($kept, $uploaded));
        }

        return array_values(array_slice($kept, 0, self::MAX_RELIGION_SPIRITUALITY_GALLERY));
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}|null
     */
    private function resolveReligionSpiritualityAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesReligionSpiritualityFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_religion_spirituality_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'religion_spirituality_audio'));

            return null;
        }

        if ($request->hasFile('religion_spirituality_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'religion_spirituality_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('religion_spirituality_audio_file'), 'upload');
        }

        if ($request->boolean('keep_existing_religion_spirituality_audio') && data_get($post?->meta, 'religion_spirituality_audio')) {
            return data_get($post->meta, 'religion_spirituality_audio');
        }

        return data_get($post?->meta, 'religion_spirituality_audio');
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveCreativeCornerDocuments(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCreativeCornerFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'creative_corner_documents', []);
        $removed = (array) $request->input('removed_creative_corner_documents', []);

        if ($existing === [] && ! $request->hasFile('creative_corner_documents')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $document): bool => in_array((string) data_get($document, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('creative_corner_documents')) {
            $kept = array_values(array_merge($kept, $this->storeCreativeCornerDocuments($request)));
        }

        return array_values(array_slice($kept, 0, self::MAX_CREATIVE_CORNER_DOCUMENTS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>|null
     */
    private function resolveCreativeCornerGallery(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCreativeCornerFlow($request->input('content_type'))) {
            return null;
        }

        $existing = (array) data_get($post?->meta, 'creative_corner_gallery', []);
        $removed = (array) $request->input('removed_creative_corner_gallery', []);

        if ($existing === [] && ! $request->hasFile('creative_corner_gallery')) {
            return null;
        }

        $kept = collect($existing)
            ->reject(fn (array $image): bool => in_array((string) data_get($image, 'path'), $removed, true))
            ->values()
            ->all();

        foreach ($removed as $path) {
            CommunityPostFileUploader::deleteIfExists($path);
        }

        if ($request->hasFile('creative_corner_gallery')) {
            $uploaded = collect($request->file('creative_corner_gallery', []))
                ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'creative-corner-gallery'))
                ->values()
                ->all();
            $kept = array_values(array_merge($kept, $uploaded));
        }

        return array_values(array_slice($kept, 0, self::MAX_CREATIVE_CORNER_GALLERY));
    }

    /**
     * @return array{type: string, path: string, name: string, url: string}|null
     */
    private function resolveCreativeCornerAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCreativeCornerFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_creative_corner_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'creative_corner_audio'));

            return null;
        }

        if ($request->hasFile('creative_corner_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'creative_corner_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('creative_corner_audio_file'), 'upload');
        }

        if ($request->boolean('keep_existing_creative_corner_audio') && data_get($post?->meta, 'creative_corner_audio')) {
            return data_get($post->meta, 'creative_corner_audio');
        }

        return data_get($post?->meta, 'creative_corner_audio');
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}|null
     */
    private function resolveCompetitionsOrganizerLogo(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCompetitionsFlow($request->input('content_type'))) {
            return null;
        }

        $existing = data_get($post?->meta, 'competitions_organizer_logo');

        if ($request->boolean('removed_competitions_organizer_logo') && is_array($existing)) {
            CommunityPostFileUploader::deleteIfExists(data_get($existing, 'path'));

            return null;
        }

        if ($request->hasFile('competitions_organizer_logo')) {
            if (is_array($existing)) {
                CommunityPostFileUploader::deleteIfExists(data_get($existing, 'path'));
            }

            return CommunityPostFileUploader::storeAttachment($request->file('competitions_organizer_logo'), 'competitions-organizer-logo');
        }

        return is_array($existing) ? $existing : null;
    }

    /**
     * @return list<array{name: string, designation: string, organization: string, profile: string, photo?: array{path: string, url: string, name: string, type: string}}>|null
     */
    private function resolveCompetitionsJury(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCompetitionsFlow($request->input('content_type'))) {
            return null;
        }

        $submitted = collect((array) $request->input('competitions_jury', []))
            ->values();
        $existing = collect((array) data_get($post?->meta, 'competitions_jury', []))->values();
        $removePhotos = array_map('intval', (array) $request->input('competitions_jury_remove_photo', []));

        if ($submitted->isEmpty() && $existing->isEmpty()) {
            return null;
        }

        $resolved = [];

        foreach ($submitted as $index => $member) {
            $name = trim((string) data_get($member, 'name'));
            $designation = trim((string) data_get($member, 'designation'));
            $organization = trim((string) data_get($member, 'organization'));
            $profile = trim((string) data_get($member, 'profile'));

            if ($name === '' && $designation === '' && $organization === '' && $profile === '') {
                continue;
            }

            $entry = [
                'name' => $name,
                'designation' => $designation,
                'organization' => $organization,
                'profile' => $profile,
            ];

            $existingPhoto = data_get($existing->get($index), 'photo');
            if (in_array((int) $index, $removePhotos, true) && is_array($existingPhoto)) {
                CommunityPostFileUploader::deleteIfExists(data_get($existingPhoto, 'path'));
                $existingPhoto = null;
            }

            if ($request->hasFile('competitions_jury_photos.'.$index)) {
                if (is_array($existingPhoto)) {
                    CommunityPostFileUploader::deleteIfExists(data_get($existingPhoto, 'path'));
                }

                $entry['photo'] = CommunityPostFileUploader::storeAttachment(
                    $request->file('competitions_jury_photos.'.$index),
                    'competitions-jury-photo'
                );
            } elseif (is_array($existingPhoto)) {
                $entry['photo'] = $existingPhoto;
            }

            $resolved[] = $entry;
        }

        foreach ($existing->slice($submitted->count()) as $orphaned) {
            CommunityPostFileUploader::deleteIfExists(data_get($orphaned, 'photo.path'));
        }

        return $resolved === [] ? null : array_values(array_slice($resolved, 0, self::MAX_COMPETITIONS_JURY));
    }

    /**
     * @return list<array{name: string, website: string, contribution: string, logo?: array{path: string, url: string, name: string, type: string}}>|null
     */
    private function resolveCompetitionsSponsors(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesCompetitionsFlow($request->input('content_type'))) {
            return null;
        }

        $submitted = collect((array) $request->input('competitions_sponsors', []))
            ->values();
        $existing = collect((array) data_get($post?->meta, 'competitions_sponsors', []))->values();
        $removeLogos = array_map('intval', (array) $request->input('competitions_sponsor_remove_logo', []));

        if ($submitted->isEmpty() && $existing->isEmpty()) {
            return null;
        }

        $resolved = [];

        foreach ($submitted as $index => $sponsor) {
            $name = trim((string) data_get($sponsor, 'name'));
            $website = trim((string) data_get($sponsor, 'website'));
            $contribution = trim((string) data_get($sponsor, 'contribution'));

            if ($name === '' && $website === '' && $contribution === '') {
                continue;
            }

            $entry = [
                'name' => $name,
                'website' => $website,
                'contribution' => $contribution,
            ];

            $existingLogo = data_get($existing->get($index), 'logo');
            if (in_array((int) $index, $removeLogos, true) && is_array($existingLogo)) {
                CommunityPostFileUploader::deleteIfExists(data_get($existingLogo, 'path'));
                $existingLogo = null;
            }

            if ($request->hasFile('competitions_sponsor_logos.'.$index)) {
                if (is_array($existingLogo)) {
                    CommunityPostFileUploader::deleteIfExists(data_get($existingLogo, 'path'));
                }

                $entry['logo'] = CommunityPostFileUploader::storeAttachment(
                    $request->file('competitions_sponsor_logos.'.$index),
                    'competitions-sponsor-logo'
                );
            } elseif (is_array($existingLogo)) {
                $entry['logo'] = $existingLogo;
            }

            $resolved[] = $entry;
        }

        foreach ($existing->slice($submitted->count()) as $orphaned) {
            CommunityPostFileUploader::deleteIfExists(data_get($orphaned, 'logo.path'));
        }

        return $resolved === [] ? null : array_values(array_slice($resolved, 0, self::MAX_COMPETITIONS_SPONSORS));
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function storeCreativeCornerDocuments(Request $request): array
    {
        return collect($request->file('creative_corner_documents', []))
            ->map(fn ($file) => CommunityPostFileUploader::storeAttachment($file, 'creative-corner-documents'))
            ->values()
            ->all();
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}|null
     */
    private function resolveScienceTechnologySingleAttachment(
        Request $request,
        ?CommunityPost $post,
        string $inputName,
        string $metaKey,
        string $storagePrefix,
        string $removedInput
    ): ?array {
        if (! CommunityPost::usesScienceTechnologyFlow($request->input('content_type'))) {
            return null;
        }

        $existing = data_get($post?->meta, $metaKey);

        if ($request->boolean($removedInput) && is_array($existing)) {
            CommunityPostFileUploader::deleteIfExists(data_get($existing, 'path'));

            return null;
        }

        if ($request->hasFile($inputName)) {
            if (is_array($existing)) {
                CommunityPostFileUploader::deleteIfExists(data_get($existing, 'path'));
            }

            return CommunityPostFileUploader::storeAttachment($request->file($inputName), $storagePrefix);
        }

        return is_array($existing) ? $existing : null;
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
    private function resolveSeniorCitizensForumAudio(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesSeniorCitizensForumFlow($request->input('content_type'))) {
            return null;
        }

        if ($request->boolean('remove_senior_citizens_forum_audio')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'senior_citizens_forum_audio'));

            return null;
        }

        $sourceType = $request->input('senior_citizens_forum_audio_source_type', 'none');

        if ($sourceType === 'upload' && $request->hasFile('senior_citizens_forum_audio_file')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'senior_citizens_forum_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('senior_citizens_forum_audio_file'), 'upload');
        }

        if ($sourceType === 'recording' && $request->hasFile('senior_citizens_forum_audio_recording')) {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'senior_citizens_forum_audio'));

            return CommunityPostFileUploader::storeAudio($request->file('senior_citizens_forum_audio_recording'), 'recording');
        }

        if ($request->boolean('keep_existing_senior_citizens_forum_audio') && data_get($post?->meta, 'senior_citizens_forum_audio')) {
            return data_get($post->meta, 'senior_citizens_forum_audio');
        }

        if ($sourceType === 'none') {
            $this->deleteStoryAudioFile(data_get($post?->meta, 'senior_citizens_forum_audio'));

            return null;
        }

        return data_get($post?->meta, 'senior_citizens_forum_audio');
    }

    /**
     * @return list<array{award_name: string, year: string, description: string, photo: array{path: string, url: string, name: string, type: string}|null, certificate: array{path: string, url: string, name: string, type: string}|null}>|null
     */
    private function resolveSeniorCitizensForumAchievements(Request $request, ?CommunityPost $post = null): ?array
    {
        if (! CommunityPost::usesSeniorCitizensForumFlow($request->input('content_type'))) {
            return null;
        }

        $entries = collect($request->input('senior_citizens_forum_achievements', []))
            ->filter(function (mixed $entry): bool {
                if (! is_array($entry)) {
                    return false;
                }

                return filled($entry['award_name'] ?? null)
                    || filled($entry['year'] ?? null)
                    || filled($entry['description'] ?? null)
                    || filled($entry['existing_photo_path'] ?? null)
                    || filled($entry['existing_certificate_path'] ?? null);
            })
            ->values();

        if ($entries->isEmpty()) {
            $this->deleteSeniorCitizensForumAchievementFiles((array) data_get($post?->meta, 'senior_citizens_forum_achievements', []));

            return null;
        }

        $existingByPhotoPath = collect((array) data_get($post?->meta, 'senior_citizens_forum_achievements', []))
            ->mapWithKeys(function (mixed $entry): array {
                $path = (string) data_get($entry, 'photo.path', '');

                return filled($path) ? [$path => $entry] : [];
            });

        $existingByCertificatePath = collect((array) data_get($post?->meta, 'senior_citizens_forum_achievements', []))
            ->mapWithKeys(function (mixed $entry): array {
                $path = (string) data_get($entry, 'certificate.path', '');

                return filled($path) ? [$path => $entry] : [];
            });

        $resolved = $entries
            ->take(self::MAX_SENIOR_CITIZENS_FORUM_ACHIEVEMENTS)
            ->map(function (array $entry, int $index) use ($request, $existingByPhotoPath, $existingByCertificatePath): array {
                $photo = null;
                $uploadedPhoto = $request->file("senior_citizens_forum_achievements.$index.photo");

                if ($uploadedPhoto) {
                    $existingPath = (string) ($entry['existing_photo_path'] ?? '');
                    if (filled($existingPath)) {
                        CommunityPostFileUploader::deleteIfExists($existingPath);
                    }

                    $photo = CommunityPostFileUploader::storeAttachment($uploadedPhoto, 'senior-citizens-forum-achievement-photos');
                } elseif (filled($entry['existing_photo_path'] ?? null)) {
                    $existingPath = (string) $entry['existing_photo_path'];
                    $existingImage = data_get($existingByPhotoPath->get($existingPath), 'photo');

                    if (is_array($existingImage)) {
                        $photo = $existingImage;
                    }
                }

                $certificate = null;
                $uploadedCertificate = $request->file("senior_citizens_forum_achievements.$index.certificate");

                if ($uploadedCertificate) {
                    $existingPath = (string) ($entry['existing_certificate_path'] ?? '');
                    if (filled($existingPath)) {
                        CommunityPostFileUploader::deleteIfExists($existingPath);
                    }

                    $certificate = CommunityPostFileUploader::storeAttachment($uploadedCertificate, 'senior-citizens-forum-certificates');
                } elseif (filled($entry['existing_certificate_path'] ?? null)) {
                    $existingPath = (string) $entry['existing_certificate_path'];
                    $existingCertificate = data_get($existingByCertificatePath->get($existingPath), 'certificate');

                    if (is_array($existingCertificate)) {
                        $certificate = $existingCertificate;
                    }
                }

                return [
                    'award_name' => trim((string) ($entry['award_name'] ?? '')),
                    'year' => trim((string) ($entry['year'] ?? '')),
                    'description' => trim((string) ($entry['description'] ?? '')),
                    'photo' => $photo,
                    'certificate' => $certificate,
                ];
            })
            ->filter(fn (array $entry): bool => filled($entry['award_name']) || filled($entry['description']))
            ->values()
            ->all();

        if ($resolved === []) {
            $this->deleteSeniorCitizensForumAchievementFiles((array) data_get($post?->meta, 'senior_citizens_forum_achievements', []));

            return null;
        }

        $keptPhotoPaths = collect($resolved)
            ->map(fn (array $entry): string => (string) data_get($entry, 'photo.path', ''))
            ->filter()
            ->all();

        $keptCertificatePaths = collect($resolved)
            ->map(fn (array $entry): string => (string) data_get($entry, 'certificate.path', ''))
            ->filter()
            ->all();

        collect((array) data_get($post?->meta, 'senior_citizens_forum_achievements', []))
            ->each(function (mixed $entry) use ($keptPhotoPaths, $keptCertificatePaths): void {
                $photoPath = (string) data_get($entry, 'photo.path', '');
                if (filled($photoPath) && ! in_array($photoPath, $keptPhotoPaths, true)) {
                    CommunityPostFileUploader::deleteIfExists($photoPath);
                }

                $certificatePath = (string) data_get($entry, 'certificate.path', '');
                if (filled($certificatePath) && ! in_array($certificatePath, $keptCertificatePaths, true)) {
                    CommunityPostFileUploader::deleteIfExists($certificatePath);
                }
            });

        return $resolved;
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     */
    private function deleteSeniorCitizensForumAchievementFiles(array $entries): void
    {
        foreach ($entries as $entry) {
            CommunityPostFileUploader::deleteIfExists((string) data_get($entry, 'photo.path', ''));
            CommunityPostFileUploader::deleteIfExists((string) data_get($entry, 'certificate.path', ''));
        }
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

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CommunityPost>
     */
    private function relatedPortalPosts(CommunityPost $post, int $limit = 4)
    {
        return CommunityPost::query()
            ->with('user')
            ->where('content_type', $post->content_type)
            ->where('id', '!=', $post->id)
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->when(filled($post->category), fn ($query) => $query->where('category', $post->category))
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CommunityPost>
     */
    private function trendingPortalPosts(CommunityPost $post, int $limit = 5)
    {
        return CommunityPost::query()
            ->with('user')
            ->where('content_type', $post->content_type)
            ->where('id', '!=', $post->id)
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CommunityPost>
     */
    private function featuredPortalPosts(?CommunityPost $exclude = null, int $limit = 4, string|array|null $contentTypes = null)
    {
        $contentTypes = array_values((array) ($contentTypes ?? $exclude?->content_type ?? 'news'));

        return CommunityPost::query()
            ->with('user')
            ->whereIn('content_type', $contentTypes)
            ->when($exclude, fn ($query) => $query->where('id', '!=', $exclude->id))
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->where(function ($builder): void {
                $builder->where('is_highlighted', true)->orWhere('is_featured', true);
            })
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CommunityPost>
     */
    private function relatedNewsPosts(CommunityPost $post, int $limit = 4)
    {
        return $this->relatedPortalPosts($post, $limit);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CommunityPost>
     */
    private function trendingNewsPosts(CommunityPost $post, int $limit = 5)
    {
        return $this->trendingPortalPosts($post, $limit);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CommunityPost>
     */
    private function breakingNewsPosts(?CommunityPost $exclude = null, int $limit = 4)
    {
        return $this->featuredPortalPosts($exclude, $limit);
    }

    /**
     * @return array{
     *     hubStats: list<array{value: string, label: string, icon: string}>,
     *     popularTopics: \Illuminate\Support\Collection<int, object>,
     *     topContributors: \Illuminate\Support\Collection<int, User>
     * }
     */
    private function hubLandingExtras(): array
    {
        $listedPosts = CommunityPost::query()->publiclyListed();

        $members = User::query()->where('is_active', true)->where('is_blocked', false)->count();
        $contributors = (clone $listedPosts)->distinct()->count('user_id');
        $postsThisMonth = (clone $listedPosts)->where('published_at', '>=', now()->startOfMonth())->count();
        $topics = (clone $listedPosts)->whereNotNull('category')->where('category', '!=', '')->distinct()->count('category');

        $popularTopics = CommunityPost::query()
            ->publiclyListed()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->selectRaw('count(*) as posts_count')
            ->groupBy('category')
            ->orderByDesc('posts_count')
            ->limit(12)
            ->get();

        $topContributors = User::query()
            ->whereHas('communityPosts', fn ($query) => $query->publiclyListed())
            ->withCount(['communityPosts as posts_count' => fn ($query) => $query->publiclyListed()])
            ->withSum(['communityPosts as views_sum' => fn ($query) => $query->publiclyListed()], 'views_count')
            ->orderByDesc('posts_count')
            ->limit(4)
            ->get();

        return [
            'hubStats' => [
                ['value' => $this->compactNumber($members), 'label' => 'Community Members', 'icon' => 'fa-users'],
                ['value' => $this->compactNumber($contributors), 'label' => 'Active Contributors', 'icon' => 'fa-user-group'],
                ['value' => $this->compactNumber($postsThisMonth), 'label' => 'Posts This Month', 'icon' => 'fa-pen-to-square'],
                ['value' => $this->compactNumber($topics).($topics > 0 ? '+' : ''), 'label' => 'Topics Covered', 'icon' => 'fa-bullseye'],
            ],
            'popularTopics' => $popularTopics,
            'topContributors' => $topContributors,
        ];
    }

    private function compactNumber(int $value): string
    {
        if ($value >= 1000000) {
            return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.').'M';
        }

        if ($value >= 1000) {
            return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'K';
        }

        return (string) $value;
    }

    private function paginateCommunityPosts(Request $request, ?User $author = null, array $highlightIds = [])
    {
        $listingHub = $author
            ? CommunityContentTaxonomy::resolveActiveHubSection(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null
            )
            : CommunityContentTaxonomy::resolveCommunityListingHub(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null
            );

        $query = $this->communityListingBaseQuery($request, $author, $listingHub)
            ->with('user')
            ->withCount(['reactions', 'comments', 'starRatings'])
            ->withAvg('starRatings', 'rating');

        if ($highlightIds !== []) {
            $placeholders = implode(',', array_fill(0, count($highlightIds), '?'));
            $query->orderByRaw("CASE WHEN id IN ({$placeholders}) THEN 0 ELSE 1 END", $highlightIds);
        }

        $query
            ->orderByDesc('is_featured')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_sponsored');

        CommunityEngagementController::applySubscriptionPriority($query, auth()->id());

        if ($request->string('sort')->toString() === 'views') {
            $query->orderByDesc('views_count');
        }

        return $query
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<CommunityPost>
     */
    private function communityListingBaseQuery(Request $request, ?User $author, ?string $listingHub)
    {
        return CommunityPost::query()
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->when($author, fn ($query) => $query
                ->where('user_id', $author->id)
                ->visibleOnAuthorProfile())
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))
            ->when(
                $listingHub && ! $request->filled('type'),
                function ($query) use ($listingHub): void {
                    $typeKeys = CommunityContentTaxonomy::hubSectionTypeKeys($listingHub);
                    if ($typeKeys !== []) {
                        $query->whereIn('content_type', $typeKeys);
                    }
                }
            )
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                if ($search === '') {
                    return;
                }

                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            })
            ->when(
                $request->string('filter')->toString() === 'editors',
                fn ($query) => $query->where(function ($builder): void {
                    $builder->where('is_highlighted', true)->orWhere('is_featured', true);
                })
            );
    }

    /**
     * @return array<int, list<array{label: string, class: string}>>
     */
    private function communityListingHighlights(Request $request, ?User $author = null): array
    {
        $listingHub = $author
            ? CommunityContentTaxonomy::resolveActiveHubSection(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null
            )
            : CommunityContentTaxonomy::resolveCommunityListingHub(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null
            );

        $base = $this->communityListingBaseQuery($request, $author, $listingHub);
        $highlights = [];

        $append = function (?CommunityPost $post, string $label, string $class) use (&$highlights): void {
            if ($post === null) {
                return;
            }

            $highlights[$post->id] ??= [];
            $highlights[$post->id][] = ['label' => $label, 'class' => $class];
        };

        $mostLiked = (clone $base)
            ->withCount(['reactions as likes_count' => fn ($query) => $query->where('reaction', '!=', 'Dislike')])
            ->orderByDesc('likes_count')
            ->orderByDesc('published_at')
            ->first();
        if ($mostLiked && (int) $mostLiked->likes_count > 0) {
            $append($mostLiked, 'Most Liked', 'community-score-badge--most-liked');
        }

        $mostSubscribed = $this->mostSubscribedListingPost(clone $base, $request);
        $append($mostSubscribed, 'Most Subscribed', 'community-score-badge--most-subscribed');

        $mostRead = (clone $base)
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->first();
        if ($mostRead && (int) $mostRead->views_count > 0) {
            $append($mostRead, 'Most Read', 'community-score-badge--most-read');
        }

        return $highlights;
    }

    private function mostSubscribedListingPost($baseQuery, Request $request): ?CommunityPost
    {
        $subscriptionQuery = CommunityCategorySubscription::query()
            ->select('content_type', 'category')
            ->selectRaw('count(*) as subscribers_count')
            ->groupBy('content_type', 'category')
            ->orderByDesc('subscribers_count');

        if ($request->filled('type')) {
            $subscriptionQuery->where('content_type', $request->string('type')->toString());
        }

        $topCategory = $subscriptionQuery->first();
        if ($topCategory && (int) $topCategory->subscribers_count > 0) {
            $fromCategory = (clone $baseQuery)
                ->where('content_type', $topCategory->content_type)
                ->where('category', $topCategory->category)
                ->orderByDesc('views_count')
                ->orderByDesc('published_at')
                ->first();

            if ($fromCategory) {
                return $fromCategory;
            }
        }

        $mostSaved = (clone $baseQuery)
            ->withCount('saves')
            ->orderByDesc('saves_count')
            ->orderByDesc('published_at')
            ->first();

        return $mostSaved && (int) $mostSaved->saves_count > 0 ? $mostSaved : null;
    }

    /**
     * @return array{
     *     featured: ?CommunityPost,
     *     sidePosts: \Illuminate\Support\Collection,
     *     listPosts: \Illuminate\Support\Collection,
     *     breakingPosts: \Illuminate\Support\Collection,
     *     trendingPosts: \Illuminate\Support\Collection
     * }
     */
    private function buildContentPortalData(Request $request, $posts, string|array $contentTypes): array
    {
        $contentTypes = array_values((array) $contentTypes);
        $items = collect($posts->items());
        $featured = $items->first(fn (CommunityPost $post): bool => $post->is_featured || $post->is_highlighted)
            ?? $items->first();
        $featuredId = $featured?->id;

        $remaining = $items->filter(fn (CommunityPost $post): bool => $post->id !== $featuredId)->values();

        $trendingPosts = CommunityPost::query()
            ->with('user')
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->whereIn('content_type', $contentTypes)
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return [
            'featured' => $featured,
            'sidePosts' => $remaining->take(3)->values(),
            'listPosts' => $remaining->slice(3)->values(),
            'breakingPosts' => $this->featuredPortalPosts(null, 4, $contentTypes),
            'trendingPosts' => $trendingPosts,
        ];
    }

    private function buildNewsPortalData(Request $request, $posts): array
    {
        return $this->buildContentPortalData($request, $posts, 'news');
    }

    private function communityPostsAjaxResponse($posts, ?Request $request = null, array $listingHighlights = []): JsonResponse
    {
        $request ??= request();
        $isAuthorPage = $request->routeIs('community.authors.show');
        $listingHub = $isAuthorPage
            ? CommunityContentTaxonomy::resolveActiveHubSection(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null
            )
            : CommunityContentTaxonomy::resolveCommunityListingHub(
                $request->string('hub')->toString() ?: null,
                $request->string('type')->toString() ?: null
            );
        $isPortalLayout = CommunityContentTaxonomy::shouldUsePortalListing(
            $request->string('type')->toString(),
            $listingHub,
            $isAuthorPage,
            CommunityContentTaxonomy::isAllPostsListing($request)
        );
        $portalScope = CommunityContentTaxonomy::resolvePortalScope(
            $request->string('type')->toString(),
            $listingHub
        );

        $partial = $isPortalLayout ? 'community.partials.news-list-items' : 'community.partials.post-cards';
        $listLayout = $isPortalLayout && $posts->currentPage() > 1 ? 'append' : 'list';

        return response()->json([
            'html' => view($partial, [
                'posts' => $posts,
                'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
                'layout' => $isPortalLayout ? $listLayout : 'grid',
                'portalType' => $portalScope['portal_key'],
                'activeHub' => $listingHub,
                'resolvedType' => $request->string('type')->toString(),
                'listingHighlights' => $listingHighlights,
            ])->render(),
            'next_page_url' => $posts->nextPageUrl(),
            'loaded_to' => $posts->lastItem() ?? 0,
            'total' => $posts->total(),
            'layout' => $isPortalLayout ? 'news-list' : 'grid',
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
     * Drop empty or already-deleted PHP temp uploads so validation does not fail
     * with "file does not exist or is not readable".
     */
    private function pruneUnreadableUploads(Request $request): void
    {
        $cleaned = $this->filterReadableUploadedFiles($request->allFiles());
        $request->files->replace(is_array($cleaned) ? $cleaned : []);
    }

    private function filterReadableUploadedFiles(mixed $files): mixed
    {
        if ($files instanceof UploadedFile) {
            try {
                $path = $files->getPathname();

                return $files->isValid() && $path !== '' && is_readable($path)
                    ? $files
                    : null;
            } catch (\Throwable) {
                return null;
            }
        }

        if (! is_array($files)) {
            return $files;
        }

        $filtered = [];
        foreach ($files as $key => $file) {
            $kept = $this->filterReadableUploadedFiles($file);
            if ($kept === null || $kept === []) {
                continue;
            }
            $filtered[$key] = $kept;
        }

        return $filtered;
    }

    /**
     * Convert KrutiDev / DevLys paste into Unicode Devanagari when it is detected.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function convertLegacyHindiFonts(array $validated): array
    {
        foreach (['title', 'excerpt', 'body'] as $field) {
            if (! isset($validated[$field]) || ! is_string($validated[$field]) || $validated[$field] === '') {
                continue;
            }

            $validated[$field] = KrutiDevToUnicode::convertIfNeeded($validated[$field]);
        }

        return $validated;
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
                    'content' => KrutiDevToUnicode::convertIfNeeded(is_array($page) ? (string) ($page['content'] ?? '') : (string) $page),
                    'language' => CommunityContentTaxonomy::bookPageLanguageCode(is_array($page) ? ($page['language'] ?? 'en') : 'en'),
                ];

                if ($usesChapters) {
                    $normalized['title'] = KrutiDevToUnicode::convertIfNeeded(trim(is_array($page) ? (string) ($page['title'] ?? '') : ''));
                    $normalized['summary'] = KrutiDevToUnicode::convertIfNeeded(trim(is_array($page) ? (string) ($page['summary'] ?? '') : ''));
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
     * @return list<array{question: string, options: list<string>, correct_answer: string}>
     */
    private function sanitizeChildrensCornerQuizPayload(mixed $quiz): array
    {
        return collect(is_array($quiz) ? $quiz : [])
            ->filter(fn (mixed $question): bool => is_array($question))
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
            ->filter(fn (array $question): bool => $question['question'] !== ''
                || $question['options'] !== []
                || $question['correct_answer'] !== '')
            ->values()
            ->all();
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
