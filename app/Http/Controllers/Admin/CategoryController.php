<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ModulePermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index()
    {
        return view('backend.categories.index', [
            'modules' => ModulePermissions::modules(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $categories = Category::query()
            ->with(['parent:id,name'])
            ->select(['id', 'name', 'parent_id', 'modules', 'created_at'])            
            ->withCount('children');

        return DataTables::of($categories)
            ->addColumn('category_type', fn (Category $category): string => $category->parent_id ? 'Sub Category' : 'Category')
            ->addColumn('parent_name', function (Category $category): string {
                return $category->parent?->name ? e($category->parent->name) : '<span class="text-muted">—</span>';
            })
            ->addColumn('modules_list', function (Category $category): string {
                $labels = ModulePermissions::modules();
                $moduleBadges = collect($category->modules ?? [])
                    ->filter(fn ($slug) => isset($labels[$slug]))
                    ->map(fn ($slug) => '<span class="badge text-bg-light border me-1 mb-1">'.e($labels[$slug]).'</span>')
                    ->implode(' ');

                return $moduleBadges !== '' ? $moduleBadges : '<span class="text-muted">—</span>';
            })
            ->editColumn('created_at', function (Category $category) {
                return $category->created_at ? $category->created_at->format('Y-m-d') : '';
            })
            ->addColumn('actions', function (Category $category): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-category" data-id="'.$category->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-category" data-id="'.$category->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->filterColumn('category_type', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if (str_contains($k, 'sub')) {
                    $query->whereNotNull('parent_id');

                    return;
                }

                if (str_contains($k, 'category')) {
                    $query->whereNull('parent_id');
                }
            })
            ->filterColumn('parent_name', function ($query, $keyword): void {
                $query->whereHas('parent', function ($q) use ($keyword): void {
                    $q->where('name', 'like', '%'.$keyword.'%');
                });
            })
            ->filterColumn('modules_list', function ($query, $keyword): void {
                $labels = ModulePermissions::modules();
                $keyword = strtolower((string) $keyword);
                $matchedSlugs = [];

                foreach ($labels as $slug => $label) {
                    if (str_contains(strtolower($label), $keyword) || str_contains($slug, $keyword)) {
                        $matchedSlugs[] = $slug;
                    }
                }

                if ($matchedSlugs === []) {
                    return;
                }

                $query->where(function ($q) use ($matchedSlugs): void {
                    foreach ($matchedSlugs as $slug) {
                        $q->orWhereJsonContains('modules', $slug);
                    }
                });
            })
            ->rawColumns(['parent_name', 'modules_list', 'actions'])
            ->make(true);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'modules' => $category->modules ?? [],
                'is_subcategory' => (bool) $category->parent_id,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateCategory($request);
        $modules = $this->resolveModules($validated['parent_id'] ?? null, $validated['modules'] ?? []);

        Category::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'modules' => $modules,
        ]);

        return response()->json(['message' => 'Category created successfully.']);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $this->validateCategory($request, $category);
        $parentId = $validated['parent_id'] ?? null;

        if ($parentId && $parentId === $category->id) {
            return response()->json(['message' => 'A category cannot be parent of itself.'], 422);
        }

        $modules = $this->resolveModules($parentId, $validated['modules'] ?? []);

        $category->update([
            'name' => $validated['name'],
            'parent_id' => $parentId,
            'modules' => $modules,
        ]);

        if (! $parentId) {
            $category->children()->update(['modules' => $modules]);
        }

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Delete sub categories first, then delete this category.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    public function parentOptions(Request $request): JsonResponse
    {
        $excludeId = (int) $request->integer('exclude_id');

        $categories = Category::query()
            ->whereNull('parent_id')
            ->when($excludeId > 0, fn ($query) => $query->where('id', '!=', $excludeId))
            ->orderBy('name')
            ->get(['id', 'name', 'modules']);

        return response()->json(['categories' => $categories]);
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(function ($query) use ($request) {
                    $query->where('parent_id', $request->input('parent_id'));
                })->ignore($category?->id),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(ModulePermissions::modules()))],
        ]);
    }

    private function resolveModules(?int $parentId, array $requestedModules): array
    {
        if ($parentId) {
            $parent = Category::query()->whereNull('parent_id')->findOrFail($parentId);

            return array_values(array_unique($parent->modules ?? []));
        }

        $allowedSlugs = array_keys(ModulePermissions::modules());

        return array_values(array_unique(array_values(array_intersect($allowedSlugs, $requestedModules))));
    }
}
