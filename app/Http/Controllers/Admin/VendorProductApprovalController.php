<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ValidatesVendorProductRequest;
use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Models\Vendor;
use App\Models\VendorProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class VendorProductApprovalController extends Controller
{
    use ValidatesVendorProductRequest;

    public function create(): View
    {
        return view('backend.vendor.products.form', [
            'product' => new VendorProduct(),
            'categories' => $this->vendorCategories(),
            'isAdmin' => true,
            'vendors' => $this->approvedVendors(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
        ]);

        $data = $this->validatedVendorProduct($request);
        $data['vendor_id'] = (int) $request->input('vendor_id');
        $data['sku'] = $data['sku'] ?: 'SKU-'.Str::upper(Str::random(8));
        $product = VendorProduct::create($data);

        $product->loadMissing('vendor.user');
        PortalNotificationService::notifyUser(
            $product->vendor?->user,
            'Product added by admin',
            $product->name.' has been added on your behalf and is pending approval.',
            route('vendor.products.show', $product),
            'approval'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Product created successfully for the selected vendor.',
                'redirect' => route('admin.vendor-products.all.index'),
            ]);
        }

        return redirect()->route('admin.vendor-products.all.index')
            ->with('success', 'Product created successfully for the selected vendor.');
    }

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

    public function allProductsIndex(): View
    {
        return view('backend.admin.vendor-products.all-products');
    }

    public function allProductsData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $status = $request->string('status')->toString();

        $query = VendorProduct::query()
            ->with(['category:id,name', 'subcategory:id,name', 'vendor:id,company_name'])
            ->select(['id', 'vendor_id', 'name', 'category', 'category_id', 'subcategory_id', 'status', 'created_at']);

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        return DataTables::of($query)
            ->addColumn('vendor_name', fn (VendorProduct $product): string => e($product->vendor?->company_name ?? '—'))
            ->addColumn('category_display', function (VendorProduct $product): string {
                $category = $product->category?->name ?? (is_string($product->category) ? $product->category : '-');
                $subcategory = $product->subcategory?->name ?? '-';

                return e($category.' / '.$subcategory);
            })
            ->addColumn('status_badge', function (VendorProduct $product): string {
                $rowStatus = $product->status ?? 'pending';
                $badge = $rowStatus === 'approved' ? 'success' : ($rowStatus === 'rejected' ? 'danger' : 'warning');

                return '<span class="badge bg-'.$badge.'">'.ucfirst($rowStatus).'</span>';
            })
            ->addColumn('actions', function (VendorProduct $product): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.vendor-products.show', $product).'" class="btn btn-sm btn-outline-secondary">View</a>'
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
        $product->loadMissing('vendor.user');

        PortalNotificationService::notifyOwnerOfReview($product->vendor?->user, 'Product', $product->name, 'approved', route('vendor.products.show', $product));

        return response()->json(['message' => 'Product approved.']);
    }

    public function reject(VendorProduct $product): JsonResponse
    {
        $product->update(['status' => 'rejected', 'approved_at' => null, 'approved_by' => null]);
        $product->loadMissing('vendor.user');

        PortalNotificationService::notifyOwnerOfReview($product->vendor?->user, 'Product', $product->name, 'rejected', route('vendor.products.show', $product));

        return response()->json(['message' => 'Product rejected.']);
    }

    public function destroy(VendorProduct $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    private function approvedVendors()
    {
        return Vendor::query()
            ->where('status', 'approved')
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'display_name']);
    }
}
