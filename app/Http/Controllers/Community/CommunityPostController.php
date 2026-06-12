<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityPostReaction;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunityPostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = CommunityPost::query()
            ->with('user')
            ->published()
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('community.index', [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::types(),
            'activeType' => $request->string('type')->toString(),
        ]);
    }


    public function author(Request $request, string $uniqueName): View
    {
        $author = $this->resolveAuthor($uniqueName);

        $posts = CommunityPost::query()
            ->with('user')
            ->published()
            ->where('user_id', $author->id)
            ->when($request->filled('type'), fn ($query) => $query->where('content_type', $request->string('type')->toString()))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('community.index', [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::types(),
            'activeType' => $request->string('type')->toString(),
            'activeAuthor' => $author,
        ]);
    }

    public function show(CommunityPost $post): View
    {
        abort_unless($post->status === CommunityPost::STATUS_PUBLISHED || auth()->id() === $post->user_id || auth()->user()?->isAdmin(), 404);

        $post->load(['user', 'reactions']);

        return view('community.show', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::types(),
        ]);
    }


    public function react(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        abort_unless($post->status === CommunityPost::STATUS_PUBLISHED, 404);

        $data = $request->validate([
            'reaction' => ['required', Rule::in(['Helpful', 'Inspiring', 'Excellent', 'Informative', 'Support', 'Vote'])],
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
        $posts = CommunityPost::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('backend.community-posts.index', [
            'posts' => $posts,
        ]);
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
            'post' => new CommunityPost(['status' => CommunityPost::STATUS_PUBLISHED, 'allow_comments' => true]),
            'types' => CommunityContentTaxonomy::types(),
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

        if ($request->hasFile('issue_attachments')) {
            $data['meta']['issue_attachments'] = $this->storeIssueAttachments($request);
        }
        $data['allow_comments'] = $this->shouldAllowComments($request);
        $data['status'] = $request->input('status', CommunityPost::STATUS_PUBLISHED);
        $data['published_at'] = $data['status'] === CommunityPost::STATUS_PUBLISHED ? now() : null;

        if ($request->hasFile('featured_image')) {
            $data['featured_image_path'] = $this->storeFeaturedImage($request);
        }

        $post = CommunityPost::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Community post created successfully.',
                'redirect' => route('community.posts.show', $post),
            ]);
        }

        return redirect()->route('community.posts.show', $post)->with('success', 'Community post created successfully.');
    }

    public function edit(Request $request, CommunityPost $post): View
    {
        $this->authorizeOwner($request, $post);

        return view('backend.community-posts.form', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::types(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        $this->authorizeOwner($request, $post);

        $data = $this->validated($request);
        $data['tags'] = $this->normalizeTags($data['tags'] ?? null);
        $data['meta'] = $this->metaPayload($request);

        if ($request->hasFile('issue_attachments')) {
            $data['meta']['issue_attachments'] = array_values(array_merge(
                (array) data_get($post->meta, 'issue_attachments', []),
                $this->storeIssueAttachments($request)
            ));
        } elseif (data_get($post->meta, 'issue_attachments')) {
            $data['meta']['issue_attachments'] = data_get($post->meta, 'issue_attachments');
        }

        $data['allow_comments'] = $this->shouldAllowComments($request);
        $data['status'] = $request->input('status', CommunityPost::STATUS_PUBLISHED);
        $data['published_at'] = $data['status'] === CommunityPost::STATUS_PUBLISHED ? ($post->published_at ?? now()) : null;

        if ($request->hasFile('featured_image')) {
            $this->deleteFeaturedImage($post->featured_image_path);
            $data['featured_image_path'] = $this->storeFeaturedImage($request);
        }

        if ($post->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        $post->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Community post updated successfully.',
                'redirect' => route('community.posts.show', $post),
            ]);
        }

        return redirect()->route('community.posts.show', $post)->with('success', 'Community post updated successfully.');
    }

    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        $this->authorizeOwner($request, $post);

        $this->deleteFeaturedImage($post->featured_image_path);

        $post->delete();

        return redirect()->route('community.posts.index')->with('success', 'Community post deleted successfully.');
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
    private function validated(Request $request): array
    {
        $typeKeys = array_keys(CommunityContentTaxonomy::types());
        $contentType = $request->input('content_type');

        return $request->validate([
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
            'body' => ['required', 'string', 'min:20'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'tags' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in([CommunityPost::STATUS_DRAFT, CommunityPost::STATUS_PUBLISHED])],
            'allow_comments' => ['nullable', 'boolean'],
            'author_bio' => ['nullable', 'string', 'max:500'],
            'location' => ['required', 'string', 'max:160'],
            'location_lat' => ['required', 'numeric', 'between:-90,90'],
            'location_lng' => ['required', 'numeric', 'between:-180,180'],
            'parent_approved' => ['nullable', 'boolean'],
            'school_name' => ['nullable', 'string', 'max:160'],
            'consultation_fee' => ['nullable', 'string', 'max:120'],
            'competition_deadline' => ['nullable', 'date'],
            'report_subtitle' => ['nullable', 'string', 'max:255'],
            'reporting_period' => [Rule::requiredIf($contentType === 'reports'), 'nullable', 'string', 'max:120'],
            'report_date' => [Rule::requiredIf($contentType === 'reports'), 'nullable', 'date'],
            'prepared_by' => [Rule::requiredIf($contentType === 'reports'), 'nullable', 'string', 'max:160'],
            'report_scope' => ['nullable', 'string', 'max:1000'],
            'methodology' => [Rule::requiredIf($contentType === 'reports'), 'nullable', 'string', 'max:2000'],
            'data_sources' => [Rule::requiredIf($contentType === 'reports'), 'nullable', 'string', 'max:2000'],
            'key_findings' => [Rule::requiredIf($contentType === 'reports'), 'nullable', 'string', 'max:3000'],
            'recommendations' => [Rule::requiredIf($contentType === 'reports'), 'nullable', 'string', 'max:3000'],
            'news_subtitle' => ['nullable', 'string', 'max:255'],
            'news_dateline' => [Rule::requiredIf($contentType === 'news'), 'nullable', 'string', 'max:160'],
            'news_date' => [Rule::requiredIf($contentType === 'news'), 'nullable', 'date'],
            'reporter_name' => [Rule::requiredIf($contentType === 'news'), 'nullable', 'string', 'max:160'],
            'news_source' => [Rule::requiredIf($contentType === 'news'), 'nullable', 'string', 'max:160'],
            'source_url' => ['nullable', 'url', 'max:255'],
            'fact_summary' => [Rule::requiredIf($contentType === 'news'), 'nullable', 'string', 'max:2000'],
            'verification_notes' => [Rule::requiredIf($contentType === 'news'), 'nullable', 'string', 'max:2000'],
            'impact_area' => ['nullable', 'string', 'max:1000'],
            'quote_attribution' => ['nullable', 'string', 'max:1000'],
            'issue_priority' => [Rule::requiredIf($contentType === 'my-area'), 'nullable', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'issue_status' => ['nullable', Rule::in(['Open', 'Under Review', 'Resolved'])],
            'reported_to' => ['nullable', 'string', 'max:160'],
            'issue_reference' => ['nullable', 'string', 'max:160'],
            'issue_attachments' => ['nullable', 'array', 'max:6'],
            'issue_attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi,pdf,doc,docx'],
            'voice_topic' => [Rule::requiredIf($contentType === 'my-voice'), 'nullable', 'string', 'max:160'],
            'voice_perspective' => [Rule::requiredIf($contentType === 'my-voice'), 'nullable', Rule::in(['Personal Experience', 'Opinion', 'Suggestion', 'Concern', 'Open Letter'])],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function metaPayload(Request $request): array
    {
        return array_filter([
            'author_bio' => $request->input('author_bio'),
            'location' => $request->input('location'),
            'location_lat' => $request->input('location_lat'),
            'location_lng' => $request->input('location_lng'),
            'parent_approved' => $request->boolean('parent_approved'),
            'school_name' => $request->input('school_name'),
            'consultation_fee' => $request->input('consultation_fee'),
            'competition_deadline' => $request->input('competition_deadline'),
            'report_subtitle' => $request->input('report_subtitle'),
            'reporting_period' => $request->input('reporting_period'),
            'report_date' => $request->input('report_date'),
            'prepared_by' => $request->input('prepared_by'),
            'report_scope' => $request->input('report_scope'),
            'methodology' => $request->input('methodology'),
            'data_sources' => $request->input('data_sources'),
            'key_findings' => $request->input('key_findings'),
            'recommendations' => $request->input('recommendations'),
            'news_subtitle' => $request->input('news_subtitle'),
            'news_dateline' => $request->input('news_dateline'),
            'news_date' => $request->input('news_date'),
            'reporter_name' => $request->input('reporter_name'),
            'news_source' => $request->input('news_source'),
            'source_url' => $request->input('source_url'),
            'fact_summary' => $request->input('fact_summary'),
            'verification_notes' => $request->input('verification_notes'),
            'impact_area' => $request->input('impact_area'),
            'quote_attribution' => $request->input('quote_attribution'),
            'issue_priority' => $request->input('issue_priority'),
            'issue_status' => $request->input('issue_status', 'Open'),
            'reported_to' => $request->input('reported_to'),
            'issue_reference' => $request->input('issue_reference'),
            'voice_topic' => $request->input('voice_topic'),
            'voice_perspective' => $request->input('voice_perspective'),
        ], fn ($value) => filled($value) || is_bool($value));
    }


    private function shouldAllowComments(Request $request): bool
    {
        if ($request->input('content_type') === 'reports') {
            return false;
        }

        return $request->boolean('allow_comments');
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

    private function storeFeaturedImage(Request $request): string
    {
        $file = $request->file('featured_image');
        $directory = public_path('uploads/community-posts');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/community-posts/'.$filename;
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

    private function authorizeOwner(Request $request, CommunityPost $post): void
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
    }
}
