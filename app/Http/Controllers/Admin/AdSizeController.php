<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $sizes = AdSize::query()->select(['id', 'size_key', 'name', 'width', 'height', 'admin_only', 'is_paid', 'amount', 'created_at']);
        // echo'<pre>'
        return DataTables::of($sizes)
            ->addColumn('dimensions', fn (AdSize $size) => $size->width.'×'.$size->height)
            ->addColumn('placement', fn (AdSize $size) => $size->admin_only
                ? '<span class="badge text-bg-warning">Admin</span>'
                : '<span class="badge text-bg-success">User</span>')
            ->addColumn('paid_status', fn (AdSize $size) => $size->is_paid
                ? '<span class="badge text-bg-primary">Paid</span>'
                : '<span class="badge text-bg-secondary">Free</span>')
            ->addColumn('amount_display', fn (AdSize $size) => $size->is_paid
                ? '$'.number_format((float) ($size->amount ?? 0), 2)
                : '-')
            ->editColumn('created_at', fn (AdSize $size) => $size->created_at?->format('Y-m-d') ?? '-')
            ->addColumn('actions', function (AdSize $size): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-ad-size" data-id="'.$size->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['placement', 'paid_status', 'actions'])
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
            'is_paid' => ['nullable', 'boolean'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);


        if ($request->boolean('is_paid') && ! $request->filled('amount')) {
            abort(response()->json([
                'message' => 'The amount field is required when paid is enabled.',
                'errors' => ['amount' => ['The amount field is required when paid is enabled.']],
            ], 422));
        }

        $validated['admin_only'] = $request->boolean('admin_only');
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['amount'] = $validated['is_paid'] ? (float) ($validated['amount'] ?? 0) : null;
        $validated['is_active'] = true;

        return $validated;
    }
}
