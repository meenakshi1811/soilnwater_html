<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageShowcaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class HomepageShowcaseItemController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.homepage-showcase.index', [
            'types' => $this->typeOptions(),
            'tones' => $this->toneOptions(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $type = $request->string('type')->toString();

        $query = HomepageShowcaseItem::query()->select([
            'id',
            'type',
            'title',
            'category_label',
            'location',
            'sort_order',
            'is_active',
            'image_path',
            'created_at',
        ]);

        if ($type !== '') {
            $query->where('type', $type);
        }

        return DataTables::of($query)
            ->addColumn('type_label', fn (HomepageShowcaseItem $item) => $this->typeOptions()[$item->type] ?? $item->type)
            ->addColumn('thumb', function (HomepageShowcaseItem $item) {
                $url = e($item->imageUrl());

                return '<img src="'.$url.'" alt="" class="rounded" width="56" height="36" style="object-fit:cover">';
            })
            ->addColumn('status_badge', fn (HomepageShowcaseItem $item) => $item->is_active
                ? '<span class="badge text-bg-success">Active</span>'
                : '<span class="badge text-bg-secondary">Inactive</span>')
            ->editColumn('created_at', fn (HomepageShowcaseItem $item) => $item->created_at?->format('d M Y') ?? '-')
            ->addColumn('actions', function (HomepageShowcaseItem $item) {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<button type="button" class="btn btn-sm btn-outline-primary js-edit-showcase-item" data-id="'.$item->id.'"><i class="fa-solid fa-pen"></i></button>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-showcase-item" data-id="'.$item->id.'"><i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['thumb', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show(HomepageShowcaseItem $homepageShowcaseItem): JsonResponse
    {
        return response()->json([
            'item' => $homepageShowcaseItem,
            'image_url' => $homepageShowcaseItem->imageUrl(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateItem($request);
        $validated['image_path'] = $this->storeImage($request, null);
        unset($validated['image']);

        HomepageShowcaseItem::create($validated);

        return response()->json(['message' => 'Homepage item added successfully.']);
    }

    public function update(Request $request, HomepageShowcaseItem $homepageShowcaseItem): JsonResponse
    {
        $validated = $this->validateItem($request, $homepageShowcaseItem);
        if ($request->hasFile('image')) {
            $this->deleteStoredImage($homepageShowcaseItem->image_path);
            $validated['image_path'] = $this->storeImage($request, $homepageShowcaseItem->image_path);
        }
        unset($validated['image']);

        $homepageShowcaseItem->update($validated);

        return response()->json(['message' => 'Homepage item updated successfully.']);
    }

    public function destroy(HomepageShowcaseItem $homepageShowcaseItem): JsonResponse
    {
        $this->deleteStoredImage($homepageShowcaseItem->image_path);
        $homepageShowcaseItem->delete();

        return response()->json(['message' => 'Homepage item deleted successfully.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateItem(Request $request, ?HomepageShowcaseItem $item = null): array
    {
        $isUpdate = $item !== null;

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys($this->typeOptions()))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'link_url' => ['required', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'category_label' => ['nullable', 'string', 'max:120'],
            'category_tone' => ['nullable', 'string', 'max:32'],
            'category_icon' => ['nullable', 'string', 'max:64'],
            'discount_badge' => ['nullable', 'string', 'max:120'],
            'valid_until' => ['nullable', 'date'],
            'headline' => ['nullable', 'string', 'max:255'],
            'subheadline' => ['nullable', 'string', 'max:255'],
            'promo_badge' => ['nullable', 'string', 'max:120'],
            'promo_sub' => ['nullable', 'string', 'max:255'],
            'strip_primary' => ['nullable', 'string', 'max:500'],
            'strip_secondary' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [], [
            'link_url' => 'link',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['category_tone'] = filled($validated['category_tone'] ?? null) ? $validated['category_tone'] : 'slate';
        $validated['category_icon'] = $this->normalizeIcon($validated['category_icon'] ?? null);

        return $validated;
    }

    private function storeImage(Request $request, ?string $currentPath): string
    {
        if (! $request->hasFile('image')) {
            return (string) $currentPath;
        }

        $directory = public_path('uploads/homepage/showcase');
        File::ensureDirectoryExists($directory);

        $file = $request->file('image');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/homepage/showcase/'.$filename;
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

    private function normalizeIcon(mixed $icon): string
    {
        $icon = trim((string) $icon);
        if ($icon === '') {
            return 'fa-store';
        }

        if (! str_starts_with($icon, 'fa-')) {
            $icon = 'fa-'.ltrim($icon, 'fa ');
        }

        return $icon;
    }

    /**
     * @return array<string, string>
     */
    private function typeOptions(): array
    {
        return [
            HomepageShowcaseItem::TYPE_FEATURED_BUSINESS => 'Featured business',
            HomepageShowcaseItem::TYPE_OFFER_PROMO => 'Offer promo',
        ];
    }

    /**
     * @return list<string>
     */
    private function toneOptions(): array
    {
        return ['orange', 'green', 'red', 'purple', 'blue', 'brown', 'teal', 'slate', 'pink', 'indigo', 'amber'];
    }
}
