<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSize;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\ModulePermissions;
use Yajra\DataTables\Facades\DataTables;

class AdSizeController extends Controller
{
    public function index()
    {
        $categories = Category::query()->whereNull('parent_id')->orderBy('name')->pluck('name', 'id');

        $modules = ModulePermissions::modules();

        return view('backend.ads.admin.sizes.index', compact('categories', 'modules'));
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $sizes = AdSize::query()
            ->with('modulePrices:id,ad_size_id,module_key,amount')
            ->select(['id', 'size_key', 'name', 'width', 'height', 'admin_only', 'is_paid', 'amount', 'is_active', 'created_at']);

        $sponsoredDimensions = \App\Support\AdSizes::sponsoredFillerDimensions();

        return DataTables::of($sizes)
            ->addColumn('dimensions', function (AdSize $size) use ($sponsoredDimensions) {
                $label = $size->width.'×'.$size->height;
                $key = $size->width.'x'.$size->height;
                if (in_array($key, $sponsoredDimensions, true)) {
                    $label .= ' <span class="badge text-bg-info ms-1">Sponsored</span>';
                }

                return $label;
            })
            ->addColumn('placement', fn (AdSize $size) => $size->admin_only
                ? '<span class="badge text-bg-warning">Admin</span>'
                : '<span class="badge text-bg-success">User</span>')
            ->addColumn('paid_status', fn (AdSize $size) => $size->is_paid
                ? '<span class="badge text-bg-primary">Paid</span>'
                : '<span class="badge text-bg-secondary">Free</span>')
            ->addColumn('status_toggle', function (AdSize $size): string {
                $checked = $size->is_active ? 'checked' : '';
                $label = $size->is_active ? 'Active' : 'Inactive';

                return '<div class="form-check form-switch m-0">'
                    . '<input class="form-check-input js-size-status-toggle" type="checkbox" role="switch" data-id="'.$size->id.'" '.$checked.'>'
                    . '<label class="form-check-label ms-2">'.$label.'</label>'
                    . '</div>';
            })
            ->editColumn('created_at', fn (AdSize $size) => $size->created_at?->format('Y-m-d') ?? '-')
            ->addColumn('actions', function (AdSize $size): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<a class="btn btn-sm btn-outline-secondary" href="'.route('admin.ads.sizes.show', $size).'" title="View details"><i class="fa-solid fa-eye"></i></a>'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['dimensions', 'placement', 'paid_status', 'status_toggle', 'actions'])
            ->make(true);
    }

    public function show(Request $request, AdSize $size): JsonResponse|\Illuminate\View\View
    {
        $size->load('categoryPrices.category:id,name', 'modulePrices');

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'size' => $size,
                'category_prices' => $size->categoryPrices
                    ->mapWithKeys(fn ($price) => [(string) $price->category_id => $this->formatAmount($price->amount)])
                    ->all(),
                'module_prices' => ($size->modulePrices->isNotEmpty()
                    ? $size->modulePrices->mapWithKeys(fn ($price) => [$price->module_key => $this->formatAmount($price->amount)])->all()
                    : (($size->module_key && $size->module_price !== null)
                        ? [$size->module_key => $this->formatAmount($size->module_price)]
                        : [])),
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
        $modulePrices = $validated['module_prices'] ?? [];
        unset($validated['category_prices'], $validated['module_prices']);

        $size = AdSize::create($validated);
        $this->syncCategoryPrices($size, $categoryPrices);
        $this->syncModulePrices($size, $modulePrices);

        return response()->json(['message' => 'Ad size added successfully.']);
    }

    public function update(Request $request, AdSize $size): JsonResponse
    {
        $validated = $this->validateSize($request, $size);
        $categoryPrices = $validated['category_prices'] ?? [];
        $modulePrices = $validated['module_prices'] ?? [];
        unset($validated['category_prices'], $validated['module_prices']);

        $size->update($validated);
        $this->syncCategoryPrices($size, $categoryPrices);
        $this->syncModulePrices($size, $modulePrices);

        return response()->json(['message' => 'Ad size updated successfully.']);
    }

    public function destroy(AdSize $size): JsonResponse
    {
        $size->delete();

        return response()->json(['message' => 'Ad size deleted successfully.']);
    }

    public function updateStatus(Request $request, AdSize $size): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $size->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        return response()->json([
            'message' => $size->is_active ? 'Ad size activated successfully.' : 'Ad size deactivated successfully.',
            'is_active' => (bool) $size->is_active,
            'status_label' => $size->is_active ? 'Active' : 'Inactive',
        ]);
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
            'module_prices' => ['nullable', 'array'],
            'module_prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'admin_only' => ['nullable', 'boolean'],
            'is_paid' => ['nullable', 'boolean'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'category_prices' => ['nullable', 'array'],
            'category_prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $validated['admin_only'] = $request->boolean('admin_only');
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['module_prices'] = $this->normalizeModulePrices($validated['module_prices'] ?? []);
        $validated['category_prices'] = $this->normalizeCategoryPrices($validated['category_prices'] ?? []);

        if ($validated['is_paid'] && $validated['category_prices'] === [] && $validated['module_prices'] === [] && ! $request->filled('amount')) {
            abort(response()->json([
                'message' => 'Enter a base price or add at least one category/module price when paid is enabled.',
                'errors' => ['amount' => ['Enter a base price or add at least one category/module price when paid is enabled.']],
            ], 422));
        }

        $validated['amount'] = $validated['is_paid']
            ? ($request->filled('amount')
                ? $this->formatAmount($request->input('amount'))
                : ($validated['category_prices'] !== []
                    ? $this->formatAmount(min(array_values($validated['category_prices'])))
                    : $this->formatAmount(min(array_values($validated['module_prices'])))))
            : null;

        if (! $size) {
            $validated['is_active'] = true;
        }

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

    private function normalizeModulePrices(array $modulePrices): array
    {
        $validModuleKeys = array_keys(ModulePermissions::modules());
        $normalized = [];

        foreach ($modulePrices as $moduleKey => $amount) {
            if ($amount === null || $amount === '' || ! in_array((string) $moduleKey, $validModuleKeys, true)) {
                continue;
            }

            $normalized[(string) $moduleKey] = $this->formatAmount($amount);
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

    private function syncModulePrices(AdSize $size, array $modulePrices): void
    {
        $size->modulePrices()->delete();

        if (! $size->is_paid) {
            return;
        }

        foreach ($modulePrices as $moduleKey => $amount) {
            $size->modulePrices()->create([
                'module_key' => (string) $moduleKey,
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
