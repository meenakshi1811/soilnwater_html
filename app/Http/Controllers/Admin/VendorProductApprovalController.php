<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use Illuminate\Http\Request;

class VendorProductApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $products = VendorProduct::query()->with(['category:id,name', 'subcategory:id,name'])
            ->when($status === 'pending', fn ($q) => $q->where(function ($pending) {
                $pending->where('status', 'pending')->orWhereNull('status');
            }))
            ->when(in_array($status, ['approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('backend.admin.vendor-products.index', compact('products', 'status'));
    }

    public function approve(VendorProduct $product, Request $request)
    {
        $product->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);
        return back()->with('success', 'Product approved.');
    }

    public function reject(VendorProduct $product)
    {
        $product->update(['status' => 'rejected', 'approved_at' => null, 'approved_by' => null]);
        return back()->with('success', 'Product rejected.');
    }
}
