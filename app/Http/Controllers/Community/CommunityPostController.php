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

    public function show(CommunityPost $post): View
    {
        abort_unless($post->status === CommunityPost::STATUS_PUBLISHED || auth()->id() === $post->user_id || auth()->user()?->isAdmin(), 404);

        $post->load(['user', 'reactions']);

        return view('community.show', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::types(),
        ]);
    }


    public function react(Request $request, CommunityPost $post): RedirectResponse
    {
        abort_unless($post->status === CommunityPost::STATUS_PUBLISHED, 404);

        $data = $request->validate([
            'reaction' => ['required', Rule::in(['Helpful', 'Inspiring', 'Excellent', 'Informative'])],
        ]);

        CommunityPostReaction::query()->firstOrCreate([
            'community_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'reaction' => $data['reaction'],
        ]);

        return back()->with('success', 'Reaction added.');
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
        $data['allow_comments'] = $request->boolean('allow_comments');
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
        $data['allow_comments'] = $request->boolean('allow_comments');
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
        ], fn ($value) => filled($value) || is_bool($value));
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

    private function authorizeOwner(Request $request, CommunityPost $post): void
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
    }
}
