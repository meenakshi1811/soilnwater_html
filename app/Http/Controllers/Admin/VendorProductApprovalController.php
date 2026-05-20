<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class VendorProductApprovalController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.vendor-products.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = VendorProduct::query()
            ->with(['category:id,name', 'subcategory:id,name'])
            ->where(fn ($pending) => $pending->where('status', 'pending')->orWhereNull('status'))
            ->select(['id', 'name', 'category', 'category_id', 'subcategory_id', 'status', 'created_at']);

        return DataTables::of($query)
            ->addColumn('category_display', function (VendorProduct $product): string {
                $category = $product->category?->name ?? (is_string($product->category) ? $product->category : '-');
                $subcategory = $product->subcategory?->name ?? '-';

                return e($category.' / '.$subcategory);
            })
            ->addColumn('status_badge', function (VendorProduct $product): string {
                $status = $product->status ?? 'pending';
                $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');

                return '<span class="badge bg-'.$badge.'">'.ucfirst($status).'</span>';
            })
            ->addColumn('actions', function (VendorProduct $product): string {
                $status = $product->status ?? 'pending';
                $approve = $status !== 'approved' ? '<button type="button" class="btn btn-sm btn-success js-approve" data-id="'.$product->id.'">Approve</button>' : '';
                $reject = $status !== 'rejected' ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject" data-id="'.$product->id.'">Reject</button>' : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.vendor-products.show', $product).'" class="btn btn-sm btn-outline-secondary">View</a>'
                    .$approve
                    .$reject
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="'.$product->id.'">Delete</button>'
                    .'</div>';
            })
            ->editColumn('created_at', function (VendorProduct $product): string {
                return optional($product->created_at)
                    ? $product->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '-';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(VendorProduct $product): View
    {
        $product->load(['category:id,name', 'subcategory:id,name']);

        return view('backend.admin.vendor-products.show', compact('product'));
    }

    public function approve(VendorProduct $product, Request $request): JsonResponse
    {
        // echo'<pre>';print_r($product);echo'</pre>';exit();
        $product->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);

        return response()->json(['message' => 'Product approved.']);
    }

    public function reject(VendorProduct $product): JsonResponse
    {
        $product->update(['status' => 'rejected', 'approved_at' => null, 'approved_by' => null]);

        return response()->json(['message' => 'Product rejected.']);
    }

    public function destroy(VendorProduct $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
