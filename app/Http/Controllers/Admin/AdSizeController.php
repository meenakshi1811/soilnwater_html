<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSize;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AdSizeController extends Controller
{
    public function index()
    {
        $categories = Category::query()->whereNull('parent_id')->orderBy('name')->pluck('name', 'id');

        return view('backend.ads.admin.sizes.index', compact('categories'));
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $sizes = AdSize::query()
            ->select(['id', 'size_key', 'name', 'width', 'height', 'admin_only', 'is_paid', 'amount', 'created_at']);

        return DataTables::of($sizes)
            ->addColumn('dimensions', fn (AdSize $size) => $size->width.'×'.$size->height)
            ->addColumn('placement', fn (AdSize $size) => $size->admin_only
                ? '<span class="badge text-bg-warning">Admin</span>'
                : '<span class="badge text-bg-success">User</span>')
            ->addColumn('paid_status', fn (AdSize $size) => $size->is_paid
                ? '<span class="badge text-bg-primary">Paid</span>'
                : '<span class="badge text-bg-secondary">Free</span>')
            ->editColumn('created_at', fn (AdSize $size) => $size->created_at?->format('Y-m-d') ?? '-')
            ->addColumn('actions', function (AdSize $size): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<a class="btn btn-sm btn-outline-secondary" href="'.route('admin.ads.sizes.show', $size).'" title="View details"><i class="fa-solid fa-eye"></i></a>'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['placement', 'paid_status', 'actions'])
            ->make(true);
    }

    public function show(Request $request, AdSize $size): JsonResponse|\Illuminate\View\View
    {
        $size->load('categoryPrices.category:id,name');

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'size' => $size,
                'category_prices' => $size->categoryPrices
                    ->mapWithKeys(fn ($price) => [(string) $price->category_id => $this->formatAmount($price->amount)])
                    ->all(),
            ]);
        }

        return view('backend.ads.admin.sizes.show', [
            'size' => $size,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateSize($request);
        $categoryPrices = $validated['category_prices'] ?? [];
        unset($validated['category_prices']);

        $size = AdSize::create($validated);
        $this->syncCategoryPrices($size, $categoryPrices);

        return response()->json(['message' => 'Ad size added successfully.']);
    }

    public function update(Request $request, AdSize $size): JsonResponse
    {
        $validated = $this->validateSize($request, $size);
        $categoryPrices = $validated['category_prices'] ?? [];
        unset($validated['category_prices']);

        $size->update($validated);
        $this->syncCategoryPrices($size, $categoryPrices);

        return response()->json(['message' => 'Ad size updated successfully.']);
    }

    public function destroy(AdSize $size): JsonResponse
    {
        $size->delete();

        return response()->json(['message' => 'Ad size deleted successfully.']);
    }

    private function validateSize(Request $request, ?AdSize $size = null): array
    {
        $validated = $request->validate([
            'size_key' => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('ad_sizes', 'size_key')->ignore($size?->id),
            ],
            'name' => ['required', 'string', 'max:120'],
            'width' => ['required', 'integer', 'min:1', 'max:5000'],
            'height' => ['required', 'integer', 'min:1', 'max:5000'],
            'admin_only' => ['nullable', 'boolean'],
            'is_paid' => ['nullable', 'boolean'],
            'category_prices' => ['nullable', 'array'],
            'category_prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $validated['admin_only'] = $request->boolean('admin_only');
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['category_prices'] = $this->normalizeCategoryPrices($validated['category_prices'] ?? []);

        if ($validated['is_paid'] && $validated['category_prices'] === []) {
            abort(response()->json([
                'message' => 'Add at least one category price when paid is enabled.',
                'errors' => ['category_prices' => ['Add at least one category price when paid is enabled.']],
            ], 422));
        }

        $validated['amount'] = $validated['is_paid']
            ? min(array_values($validated['category_prices']))
            : null;
        $validated['is_active'] = true;

        return $validated;
    }

    private function normalizeCategoryPrices(array $categoryPrices): array
    {
        $validCategoryIds = $this->adCategories()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $normalized = [];

        foreach ($categoryPrices as $categoryId => $amount) {
            if ($amount === null || $amount === '' || ! in_array((string) $categoryId, $validCategoryIds, true)) {
                continue;
            }

            $normalized[(int) $categoryId] = $this->formatAmount($amount);
        }

        return $normalized;
    }

    private function syncCategoryPrices(AdSize $size, array $categoryPrices): void
    {
        $size->categoryPrices()->delete();

        if (! $size->is_paid) {
            return;
        }

        foreach ($categoryPrices as $categoryId => $amount) {
            $size->categoryPrices()->create([
                'category_id' => (int) $categoryId,
                'amount' => $this->formatAmount($amount),
            ]);
        }
    }

    private function formatAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function adCategories()
    {
        return Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'ads')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
