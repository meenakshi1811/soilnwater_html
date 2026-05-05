<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TermsAndConditionController extends Controller
{
    /**
     * @return array<string, string>
     */
    private function modules(): array
    {
        return [
            'main' => 'Main',
            'register' => 'Register',
            'ads' => 'Ads',
            'offer' => 'Offer',
            'ecommerce' => 'E-Commerce',
            'vendors' => 'Vendors',
            'services' => 'Services',
            'properties' => 'Properties',
            'builders' => 'Builders',
            'consultants' => 'Consultants',
            'enquiry' => 'Enquiry',
            'products' => 'Products',
            'user_enquiry' => 'User Enquiry',
        ];
    }

    public function index()
    {
        return view('backend.terms-and-conditions.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $records = TermsAndCondition::query()->select(['id', 'module_key', 'module_name', 'content', 'updated_at']);

        return DataTables::of($records)
            ->editColumn('updated_at', function (TermsAndCondition $item): string {
                return $item->updated_at?->format('Y-m-d H:i');
            })
            ->addColumn('content_preview', function (TermsAndCondition $item): string {
                return e(mb_strimwidth(trim(strip_tags($item->content)), 0, 120, '...'));
            })
            ->addColumn('actions', function (TermsAndCondition $item): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-terms" data-id="'.$item->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-terms" data-id="'.$item->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function moduleOptions(Request $request): JsonResponse
    {
        $editingId = (int) $request->query('editing_id', 0);
        $used = TermsAndCondition::query()->pluck('module_key')->all();
        $modules = $this->modules();
        $editingRecord = $editingId > 0 ? TermsAndCondition::query()->find($editingId) : null;

        $options = [];
        foreach ($modules as $key => $label) {
            if (in_array($key, $used, true)) {
                if (! $editingRecord || $editingRecord->module_key !== $key) {
                    continue;
                }
            }

            $options[] = [
                'key' => $key,
                'name' => $label,
            ];
        }

        return response()->json(['modules' => $options]);
    }

    public function show(TermsAndCondition $termsAndCondition): JsonResponse
    {
        return response()->json([
            'item' => $termsAndCondition,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateData($request);
        $modules = $this->modules();

        $item = TermsAndCondition::create([
            'module_key' => $validated['module_key'],
            'module_name' => $modules[$validated['module_key']],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Terms and conditions saved successfully.',
            'item' => $item,
        ]);
    }

    public function update(Request $request, TermsAndCondition $termsAndCondition): JsonResponse
    {
        $validated = $this->validateData($request, $termsAndCondition);
        $modules = $this->modules();

        $termsAndCondition->update([
            'module_key' => $validated['module_key'],
            'module_name' => $modules[$validated['module_key']],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Terms and conditions updated successfully.',
        ]);
    }

    public function destroy(TermsAndCondition $termsAndCondition): JsonResponse
    {
        $termsAndCondition->delete();

        return response()->json([
            'message' => 'Terms and conditions deleted successfully.',
        ]);
    }

    private function validateData(Request $request, ?TermsAndCondition $termsAndCondition = null): array
    {
        return $request->validate([
            'module_key' => [
                'required',
                Rule::in(array_keys($this->modules())),
                Rule::unique('terms_and_conditions', 'module_key')->ignore($termsAndCondition?->id),
            ],
            'content' => ['required', 'string'],
        ]);
    }
}
