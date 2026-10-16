<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryHomepageImage;
use App\Models\ConsultantService;
use App\Models\Offer;
use App\Models\ServiceProviderService;
use App\Models\UserAd;
use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Support\HomepageCardCategory;
use App\Support\HomepageEducationKnowledgeCards;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CategoryHomepageImageController extends Controller
{
    /**
     * Where each homepage section's postings keep their category: the model, the column naming
     * whose posting it is (counted once per category) and its category columns. Education is
     * keyword-based, below.
     */
    private const CONTEXT_CATEGORY_SOURCES = [
        CategoryHomepageImage::CONTEXT_ADS => [
            ['model' => UserAd::class, 'owner' => 'id', 'columns' => ['category_id', 'subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_OFFERS => [
            ['model' => Offer::class, 'owner' => 'id', 'columns' => ['category_id', 'subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_VENDORS => [
            ['model' => VendorProduct::class, 'owner' => 'vendor_id', 'columns' => ['category_id', 'subcategory_id', 'child_category_id']],
            ['model' => Vendor::class, 'owner' => 'id', 'columns' => ['business_category_id', 'business_subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_SERVICES => [
            ['model' => ServiceProviderService::class, 'owner' => 'service_provider_id', 'columns' => ['category_id', 'subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_CONSULTANTS => [
            ['model' => ConsultantService::class, 'owner' => 'consultant_id', 'columns' => ['category_id', 'subcategory_id']],
        ],
    ];

    /**
     * What the count next to each category counts: [singular, plural].
     */
    private const CONTEXT_COUNT_NOUNS = [
        CategoryHomepageImage::CONTEXT_ADS => ['ad', 'ads'],
        CategoryHomepageImage::CONTEXT_OFFERS => ['offer', 'offers'],
        CategoryHomepageImage::CONTEXT_VENDORS => ['vendor', 'vendors'],
        CategoryHomepageImage::CONTEXT_SERVICES => ['service provider', 'service providers'],
        CategoryHomepageImage::CONTEXT_CONSULTANTS => ['consultant', 'consultants'],
    ];

    public function index(): View
    {
        return view('backend.admin.category-homepage-images.index', [
            'contexts' => CategoryHomepageImage::contextLabels(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $context = $request->string('context')->toString();

        $query = CategoryHomepageImage::query()
            ->with(['category:id,name,parent_id', 'category.parent:id,name'])
            ->select(['id', 'category_id', 'context', 'image_path', 'updated_at']);

        if ($context !== '') {
            $query->where('context', $context);
        }

        return DataTables::of($query)
            ->addColumn('category_label', function (CategoryHomepageImage $row): string {
                $category = $row->category;
                if (! $category) {
                    return '—';
                }

                // Plain text: DataTables escapes non-raw columns itself (escaping here too showed "&amp;")
                if ($category->parent) {
                    return $category->parent->name.' › '.$category->name;
                }

                return $category->name;
            })
            ->addColumn('context_label', fn (CategoryHomepageImage $row) => CategoryHomepageImage::contextLabels()[$row->context] ?? $row->context)
            ->addColumn('thumb', function (CategoryHomepageImage $row) {
                $url = e($row->imageUrl());

                return '<img src="'.$url.'" alt="" class="rounded" width="72" height="48" style="object-fit:cover">';
            })
            ->editColumn('updated_at', fn (CategoryHomepageImage $row) => $row->updated_at?->format('d M Y') ?? '-')
            ->addColumn('actions', function (CategoryHomepageImage $row) {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<button type="button" class="btn btn-sm btn-outline-primary js-edit-category-image" data-id="'.$row->id.'"><i class="fa-solid fa-pen"></i></button>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-category-image" data-id="'.$row->id.'"><i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['thumb', 'actions'])
            ->make(true);
    }

    /**
     * Categories that have postings in the chosen homepage section, each with how many it has.
     * `include` keeps an existing item's category selectable when editing, even if nothing
     * uses it any more.
     */
    public function categoryOptions(Request $request): JsonResponse
    {
        $context = $request->string('context')->toString();
        $includeId = $request->integer('include') ?: null;
        $counts = $this->postingCounts($context);
        $nouns = self::CONTEXT_COUNT_NOUNS[$context] ?? null;

        $categories = Category::query()
            ->with(['parent:id,name,parent_id', 'parent.parent:id,name'])
            ->where(function (Builder $query) use ($context, $counts, $includeId): void {
                $this->restrictToContext($query, $context, $counts);

                if ($includeId) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        // Grouped by main category for a searchable, readable dropdown: the main category itself
        // is "Entire category", deeper levels read "Subcategory › Child"; label is the full path
        $options = $categories->map(function (Category $category) use ($counts, $nouns): array {
            $parent = $category->parent;
            $grandparent = $parent?->parent;

            if ($parent === null) {
                $option = ['id' => $category->id, 'group' => $category->name, 'text' => 'Entire category', 'depth' => 0, 'label' => $category->name];
            } elseif ($grandparent === null) {
                $option = ['id' => $category->id, 'group' => $parent->name, 'text' => $category->name, 'depth' => 1, 'label' => $parent->name.' › '.$category->name];
            } else {
                $option = [
                    'id' => $category->id,
                    'group' => $grandparent->name,
                    'text' => $parent->name.' › '.$category->name,
                    'depth' => 2,
                    'label' => $grandparent->name.' › '.$parent->name.' › '.$category->name,
                ];
            }

            // e.g. "3 vendors"; none for education, which has no postings to count
            $count = $counts === null ? null : ($counts[$category->id] ?? 0);
            $option['count_label'] = $count === null || $nouns === null ? null : $count.' '.($count === 1 ? $nouns[0] : $nouns[1]);

            return $option;
        })
            ->sortBy(fn (array $option): array => [mb_strtolower($option['group']), $option['depth'] === 0 ? 0 : 1, mb_strtolower($option['text'])])
            ->values();

        return response()->json(['categories' => $options]);
    }

    /**
     * @param  array<int, int>|null  $counts  posting counts of the section, when it has postings
     */
    private function restrictToContext(Builder $query, string $context, ?array $counts): void
    {
        if ($counts !== null) {
            $ids = array_keys($counts);
            $query->whereIn('id', $ids === [] ? [0] : $ids);

            return;
        }

        // Education cards look their category up by keywords: offer exactly the categories
        // (and parent levels) those keywords resolve to
        if ($context === CategoryHomepageImage::CONTEXT_EDUCATION) {
            $ids = collect(HomepageEducationKnowledgeCards::imageKeywordSets())
                ->flatMap(fn (array $keywords): array => array_values(array_filter(HomepageCardCategory::idsFromKeywords($keywords))))
                ->unique()
                ->values()
                ->all();

            $query->whereIn('id', $ids === [] ? [0] : $ids);

            return;
        }

        $query->whereRaw('1 = 1');
    }

    /**
     * How many vendors / service providers / consultants / offers / ads use each category of the
     * section, each counted once. A posting under a subcategory also counts for its parent levels,
     * so "Entire category" shows the whole category's total. Null for sections without postings.
     *
     * @return array<int, int>|null category id => count
     */
    private function postingCounts(string $context): ?array
    {
        $sources = self::CONTEXT_CATEGORY_SOURCES[$context] ?? null;
        if ($sources === null) {
            return null;
        }

        // category id => [owner id => true]
        $owners = [];
        foreach ($sources as $source) {
            foreach ($source['columns'] as $column) {
                $pairs = $source['model']::query()->toBase()
                    ->whereNotNull($column)
                    ->distinct()
                    ->get([$column.' as category_id', $source['owner'].' as owner_id']);

                foreach ($pairs as $pair) {
                    $owners[(int) $pair->category_id][(int) $pair->owner_id] = true;
                }
            }
        }

        $parents = $this->parentMap(array_keys($owners));
        $totals = [];
        foreach ($owners as $categoryId => $ownerIds) {
            // The category, its parent and grandparent (three levels at most)
            for ($id = $categoryId, $level = 0; $id !== null && $level < 3; $id = $parents[$id] ?? null, $level++) {
                $totals[$id] = ($totals[$id] ?? []) + $ownerIds;
            }
        }

        return array_map('count', $totals);
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, int|null> category id => parent id, for these categories and their parent levels
     */
    private function parentMap(array $ids): array
    {
        $map = [];
        while ($ids !== []) {
            $next = [];
            foreach (Category::query()->whereIn('id', $ids)->pluck('parent_id', 'id') as $id => $parentId) {
                $map[(int) $id] = $parentId === null ? null : (int) $parentId;
                if ($parentId !== null) {
                    $next[] = (int) $parentId;
                }
            }
            $ids = array_values(array_unique(array_diff($next, array_keys($map))));
        }

        return $map;
    }

    public function show(CategoryHomepageImage $categoryHomepageImage): JsonResponse
    {
        $categoryHomepageImage->load(['category:id,name,parent_id', 'category.parent:id,name']);

        return response()->json([
            'item' => $categoryHomepageImage,
            'image_url' => $categoryHomepageImage->imageUrl(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateItem($request);
        $validated['image_path'] = $this->storeImage($request, null);
        unset($validated['image']);

        CategoryHomepageImage::create($validated);

        return response()->json(['message' => 'Category homepage image saved.']);
    }

    public function update(Request $request, CategoryHomepageImage $categoryHomepageImage): JsonResponse
    {
        $validated = $this->validateItem($request, $categoryHomepageImage);
        if ($request->hasFile('image')) {
            $this->deleteStoredImage($categoryHomepageImage->image_path);
            $validated['image_path'] = $this->storeImage($request, $categoryHomepageImage->image_path);
        }
        unset($validated['image']);

        $categoryHomepageImage->update($validated);

        return response()->json(['message' => 'Category homepage image updated.']);
    }

    public function destroy(CategoryHomepageImage $categoryHomepageImage): JsonResponse
    {
        $this->deleteStoredImage($categoryHomepageImage->image_path);
        $categoryHomepageImage->delete();

        return response()->json(['message' => 'Category homepage image removed.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateItem(Request $request, ?CategoryHomepageImage $item = null): array
    {
        $isUpdate = $item !== null;

        return $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
                Rule::unique('category_homepage_images', 'category_id')
                    ->where(fn ($query) => $query->where('context', $request->input('context')))
                    ->ignore($item?->id),
            ],
            'context' => ['required', Rule::in(array_keys(CategoryHomepageImage::contextLabels()))],
            'image' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function storeImage(Request $request, ?string $currentPath): string
    {
        if (! $request->hasFile('image')) {
            return (string) $currentPath;
        }

        $directory = public_path('uploads/homepage/category-cards');
        File::ensureDirectoryExists($directory);

        $file = $request->file('image');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/homepage/category-cards/'.$filename;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! filled($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $full = public_path($path);
        if (File::isFile($full)) {
            File::delete($full);
        }
    }
}
