<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSize;
use App\Support\AdSizes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class AdSizeController extends Controller
{
    public function index()
    {
        return view('backend.ads.admin.sizes.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $sizes = AdSize::query()->select(['id', 'size_key', 'name', 'width', 'height', 'admin_only', 'created_at']);

        return DataTables::of($sizes)
            ->addColumn('dimensions', fn (AdSize $size) => $size->width.'×'.$size->height)
            ->addColumn('placement', fn (AdSize $size) => $size->admin_only
                ? '<span class="badge text-bg-warning">Admin</span>'
                : '<span class="badge text-bg-success">User</span>')
            ->editColumn('created_at', fn (AdSize $size) => $size->created_at?->format('Y-m-d') ?? '-')
            ->addColumn('actions', function (AdSize $size): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['placement', 'actions'])
            ->make(true);
    }

    public function show(AdSize $size): JsonResponse
    {
        return response()->json(['size' => $size]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateSize($request);

        AdSize::create($validated);

        return response()->json(['message' => 'Ad size added successfully.']);
    }

    public function update(Request $request, AdSize $size): JsonResponse
    {
        $validated = $this->validateSize($request, $size);

        $size->update($validated);

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
        ]);

        if (array_key_exists($validated['size_key'], AdSizes::all()) && (! $size || $size->size_key !== $validated['size_key'])) {
            throw ValidationException::withMessages([
                'size_key' => 'Size key already exists in default sizes. Choose a different key.',
            ]);
        }

        $validated['admin_only'] = $request->boolean('admin_only');
        $validated['is_active'] = true;

        return $validated;
    }
}
