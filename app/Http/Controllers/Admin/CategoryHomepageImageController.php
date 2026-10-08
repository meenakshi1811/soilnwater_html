<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryHomepageImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CategoryHomepageImageController extends Controller
{
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

                if ($category->parent) {
                    return e($category->parent->name.' → '.$category->name);
                }

                return e($category->name);
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

    public function categoryOptions(): JsonResponse
    {
        $categories = Category::query()
            ->with(['parent:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $options = $categories->map(function (Category $category) {
            $label = $category->parent
                ? $category->parent->name.' → '.$category->name
                : $category->name;

            return [
                'id' => $category->id,
                'label' => $label,
            ];
        })->values();

        return response()->json(['categories' => $options]);
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
