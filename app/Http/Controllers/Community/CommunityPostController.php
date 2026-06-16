<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityAuthorQuestion;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostPollVote;
use App\Models\CommunityPostReaction;
use App\Models\User;
use App\Services\PortalNotificationService;
use App\Support\CommunityContentTaxonomy;
use App\Support\CommunityPostFormFields;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CommunityPostController extends Controller
{
    private const MAX_FEATURED_IMAGES = 5;

    private const MAX_TAGS = 10;

    private const MAX_VIDEO_FILE_KB = 51200;

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
        ]);
    }

    public function show(CommunityPost $post): View
    {
        abort_unless(
            $post->isPubliclyVisible()
            || auth()->id() === $post->user_id
            || auth()->user()?->isAdmin(),
            404
        );

        $post->load([
            'user',
            'reactions',
            'pollVotes',
            'discussionComments.user',
            'discussionComments.replies.user',
        ]);

        return view('community.show', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'answeredAuthorQuestions' => $post->user_id
                ? $this->answeredQuestionsForPost($post)
                : collect(),
        ]);
    }

    public function react(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);

        $data = $request->validate([
            'reaction' => ['required', Rule::in(['Helpful', 'Inspiring', 'Excellent', 'Informative', 'Support', 'Vote', 'Dislike'])],
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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'reaction' => $data['reaction'],
                'active' => $active,
                'counts' => $post->reactions()
                    ->selectRaw('reaction, count(*) as total')
                    ->groupBy('reaction')
                    ->pluck('total', 'reaction'),
            ]);
        }

        return back()->with('success', $message);
    }

    public function votePoll(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->allowsPoll(), 403, 'Polls are disabled for this post.');

        $data = $request->validate([
            'option' => ['required', Rule::in(array_keys(CommunityPost::POLL_OPTIONS))],
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
        abort_unless($post->isPubliclyVisible(), 404);
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
        ]);

        return back()->with('success', filled($data['parent_id'] ?? null) ? 'Reply added to the discussion.' : 'Comment added to the discussion.');
    }

    public function followAuthor(Request $request, User $author): RedirectResponse
    {
        abort_if($request->user()->id === $author->id, 422, 'You cannot follow yourself.');

        \Illuminate\Support\Facades\DB::table('community_author_follows')->updateOrInsert(
            ['user_id' => $request->user()->id, 'author_id' => $author->id],
            ['updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Author followed successfully.');
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
            ->addColumn('category_display', fn (CommunityPost $post): string => e(
                filled(data_get($post->meta, 'report_type'))
                    ? data_get($post->meta, 'report_type', $post->category)
                    : $post->category
            ))
            ->addColumn('status_badge', fn (CommunityPost $post): string => '<span class="badge '.$post->statusBadgeClass().'">'.e($post->statusLabel()).'</span>')
            ->addColumn('published_display', function (CommunityPost $post): string {
                if ($post->published_at) {
                    return $post->published_at->timezone(config('app.timezone'))->format('d M Y, h:i A');
                }

                return $post->isPendingApproval() ? 'Awaiting approval' : '—';
            })
            ->addColumn('actions', function (CommunityPost $post): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('community.posts.show', $post).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .'<a href="'.route('community.posts.edit', $post).'" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fa-solid fa-pen"></i></a>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-post" data-slug="'.e($post->slug).'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function destroyPost(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        $this->authorizeOwner($request, $post);

        foreach ($post->featuredImages() as $imagePath) {
            $this->deleteFeaturedImage($imagePath);
        }

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
        ], [
            'author_slug.regex' => 'Use lowercase letters, numbers, and single hyphens only.',
        ]);

        $user->forceFill(['author_slug' => Str::slug($data['author_slug'])])->save();

        return back()->with('success', 'Author profile URL updated successfully.');
    }

    public function create(): View
    {
        return view('backend.community-posts.form', [
            'post' => new CommunityPost(['status' => CommunityPost::STATUS_PUBLISHED, 'allow_comments' => true, 'allow_sharing' => true, 'allow_poll' => false]),
            'types' => CommunityContentTaxonomy::formTypes(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['tags'] = $this->normalizeTags($data['tags'] ?? null);
        $data['meta'] = $this->metaPayload($request);
        $this->mergeBookPagesIntoMeta($request, $data);

        if ($request->hasFile('issue_attachments')) {
            $data['meta']['issue_attachments'] = $this->storeIssueAttachments($request);
        }
        $data['allow_comments'] = $this->shouldAllowComments($request);
        $data['allow_sharing'] = $this->shouldAllowSharing($request);
        $data['allow_poll'] = $this->shouldAllowPoll($request);
        $data = array_merge($data, $this->resolvePublicationState($request, $post = null));
        [$data['featured_images'], $data['featured_image_path']] = $this->resolveFeaturedImages($request);
        $data['video'] = $this->resolveVideo($request);

        $post = CommunityPost::create($data);

        if ($post->isPendingApproval()) {
            $this->notifyAdminsOfPendingPost($post);
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
        $data['tags'] = $this->normalizeTags($data['tags'] ?? null);
        $data['meta'] = $this->metaPayload($request);
        $this->mergeBookPagesIntoMeta($request, $data);

        if ($request->hasFile('issue_attachments')) {
            $data['meta']['issue_attachments'] = array_values(array_merge(
                (array) data_get($post->meta, 'issue_attachments', []),
                $this->storeIssueAttachments($request)
            ));
        } elseif (data_get($post->meta, 'issue_attachments')) {
            $data['meta']['issue_attachments'] = data_get($post->meta, 'issue_attachments');
        }

        $data['allow_comments'] = $this->shouldAllowComments($request);
        $data['allow_sharing'] = $this->shouldAllowSharing($request);
        $data['allow_poll'] = $this->shouldAllowPoll($request);
        $wasPending = $post->isPendingApproval();
        $data = array_merge($data, $this->resolvePublicationState($request, $post));

        [$featuredImages, $featuredImagePath] = $this->resolveFeaturedImages($request, $post);
        $this->deleteRemovedFeaturedImages($post->featuredImages(), $featuredImages);
        $data['featured_images'] = $featuredImages;
        $data['featured_image_path'] = $featuredImagePath;
        $data['video'] = $this->resolveVideo($request, $post);

        if ($post->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        $post->update($data);

        if ($post->isPendingApproval() && ! $wasPending) {
            $this->notifyAdminsOfPendingPost($post->fresh());
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
        $directory = public_path('uploads/community-posts/inline');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return response()->json([
            'url' => asset('uploads/community-posts/inline/'.$filename),
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

        $rules = [
            'content_type' => ['required', Rule::in($typeKeys)],
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
            'body' => [Rule::requiredIf(fn () => ! CommunityPost::isBookContentType($contentType))],
            'book_pages' => [Rule::requiredIf(fn () => CommunityPost::isBookContentType($contentType)), 'array', 'min:1'],
            'book_pages.*.content' => ['nullable', 'string'],
            'book_pages.*.language' => ['nullable', Rule::in(['en', 'hi'])],
            'editor_language' => ['nullable', Rule::in(['en', 'hi'])],
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
            'allow_sharing' => ['nullable', 'boolean'],
            'allow_poll' => ['nullable', 'boolean'],
            'poll_subject' => [
                Rule::requiredIf(fn () => $request->boolean('allow_poll')),
                'nullable',
                'string',
                'max:160',
            ],
            'author_bio' => ['nullable', 'string', 'max:500'],
            'location_type' => ['required', Rule::in(array_keys(CommunityPost::LOCATION_TYPES))],
            'location' => [
                Rule::requiredIf(fn () => in_array($request->input('location_type'), CommunityPost::locationTypesRequiringPlace(), true)),
                'nullable',
                'string',
                'max:160',
            ],
            'location_lat' => [
                Rule::requiredIf(fn () => in_array($request->input('location_type'), CommunityPost::locationTypesRequiringPlace(), true)),
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'location_lng' => [
                Rule::requiredIf(fn () => in_array($request->input('location_type'), CommunityPost::locationTypesRequiringPlace(), true)),
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
            'recommendations' => ['nullable', 'string', 'max:3000'],
            'issue_attachments' => ['nullable', 'array', 'max:6'],
            'issue_attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi,pdf,doc,docx'],
        ];

        if (is_string($contentType)) {
            $rules = array_merge($rules, CommunityPostFormFields::validationRules($contentType));
        }

        $validated = $request->validate($rules);
        $this->assertFeaturedImageLimit($request, $post);

        if (CommunityPost::isBookContentType($contentType)) {
            $bookPages = $this->normalizeBookPages($validated['book_pages'] ?? []);

            if ($bookPages === []) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'book_pages' => 'Please add content to at least one book page.',
                ]);
            }

            $validated['body'] = CommunityPost::bodyFromBookPages($bookPages);
            $validated['book_pages'] = $bookPages;
        }

        unset($validated['book_pages']);

        if (! in_array($validated['location_type'], CommunityPost::locationTypesRequiringPlace(), true)) {
            $validated = array_merge($validated, CommunityPost::defaultLocationForType($validated['location_type']));
        }

        if (($validated['status'] ?? null) === CommunityPost::STATUS_DRAFT) {
            $validated['publish_as'] = null;
            $validated['pen_name'] = null;
        } elseif (($validated['publish_as'] ?? null) !== CommunityPost::PUBLISH_AS_PEN_NAME) {
            $validated['pen_name'] = null;
        }

        if (! ($validated['allow_poll'] ?? false)) {
            $validated['poll_subject'] = null;
        }

        return $validated;
    }

    private function assertFeaturedImageLimit(Request $request, ?CommunityPost $post = null): void
    {
        $existing = $post ? $post->featuredImages() : [];
        $removed = (array) $request->input('removed_featured_images', []);
        $remaining = count(array_values(array_filter(
            $existing,
            fn (string $path) => ! in_array($path, $removed, true)
        )));
        $incoming = count($request->file('featured_images', []));

        if (($remaining + $incoming) > self::MAX_FEATURED_IMAGES) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'featured_images' => 'You can upload up to '.self::MAX_FEATURED_IMAGES.' featured images.',
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
        $directory = public_path('uploads/community-posts');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return collect($request->file('featured_images', []))
            ->map(function ($file) use ($directory): string {
                $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
                $file->move($directory, $filename);

                return 'uploads/community-posts/'.$filename;
            })
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
        $payload['recommendations'] = $request->input('recommendations');
        $editorLanguage = $request->input('editor_language', 'en');
        $payload['editor_language'] = in_array($editorLanguage, ['en', 'hi'], true) ? $editorLanguage : 'en';

        return array_filter($payload, fn ($value) => filled($value) || is_bool($value));
    }

    private function shouldAllowComments(Request $request): bool
    {
        return $request->boolean('allow_comments');
    }

    private function shouldAllowSharing(Request $request): bool
    {
        return $request->boolean('allow_sharing');
    }

    private function shouldAllowPoll(Request $request): bool
    {
        return $request->boolean('allow_poll');
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
            ->map(fn (string $tag) => trim($tag))
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
        $directory = public_path('uploads/community-posts/issues');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return collect($request->file('issue_attachments', []))
            ->map(function ($file) use ($directory): array {
                $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
                $file->move($directory, $filename);
                $path = 'uploads/community-posts/issues/'.$filename;

                return [
                    'path' => $path,
                    'url' => asset($path),
                    'name' => $file->getClientOriginalName(),
                    'type' => Str::before($file->getMimeType(), '/'),
                ];
            })
            ->values()
            ->all();
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
        $directory = public_path('uploads/community-posts/videos');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return [
            'type' => 'upload',
            'path' => 'uploads/community-posts/videos/'.$filename,
            'name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * @param  array{type: string, path?: string}|null  $video
     */
    private function deleteVideoFile(?array $video): void
    {
        if (($video['type'] ?? null) !== 'upload' || blank($video['path'] ?? null)) {
            return;
        }

        $publicPath = public_path($video['path']);
        if (is_file($publicPath)) {
            unlink($publicPath);

            return;
        }

        Storage::disk('public')->delete($video['path']);
    }

    private function deleteFeaturedImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $publicPath = public_path($path);
        if (is_file($publicPath)) {
            unlink($publicPath);

            return;
        }

        Storage::disk('public')->delete($path);
    }


    private function paginateCommunityPosts(Request $request, ?User $author = null)
    {
        return CommunityPost::query()
            ->with('user')
            ->withCount(['reactions', 'comments'])
            ->publiclyListed()
            ->when($author, fn ($query) => $query
                ->where('user_id', $author->id)
                ->visibleOnAuthorProfile())
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->orderByDesc('is_featured')
            ->orderByDesc('is_highlighted')
            ->orderByDesc('is_sponsored')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
    }

    private function communityPostsAjaxResponse($posts): JsonResponse
    {
        return response()->json([
            'html' => view('community.partials.post-cards', ['posts' => $posts])->render(),
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

        $pages = $this->normalizeBookPages($request->input('book_pages', []));

        if ($pages !== []) {
            $data['meta']['book_pages'] = $pages;
            $data['body'] = CommunityPost::bodyFromBookPages($pages);
        }
    }

    /**
     * @param  list<array{content?: string, language?: string}>|list<string>  $pages
     * @return list<array{content: string, language: string}>
     */
    private function normalizeBookPages(array $pages): array
    {
        return collect($pages)
            ->map(fn (mixed $page): array => [
                'content' => is_array($page) ? (string) ($page['content'] ?? '') : (string) $page,
                'language' => in_array(is_array($page) ? ($page['language'] ?? 'en') : 'en', ['en', 'hi'], true)
                    ? (is_array($page) ? ($page['language'] ?? 'en') : 'en')
                    : 'en',
            ])
            ->filter(fn (array $page): bool => filled(strip_tags($page['content'])))
            ->values()
            ->all();
    }
}
