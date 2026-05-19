@extends('backend.layouts.app')

@section('title', 'Vendor Product Approvals')

@section('content')
<div class="admin-panel ems-page">
    <h2 class="admin-title mb-3">Vendor Product Approvals</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form class="mb-3">
        <select name="status" class="form-select" style="max-width:220px" onchange="this.form.submit()">
            <option value="pending" @selected($status === 'pending')>Pending</option>
            <option value="approved" @selected($status === 'approved')>Approved</option>
            <option value="rejected" @selected($status === 'rejected')>Rejected</option>
        </select>
    </form>

    <div class="chart-card p-3 table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $categoryName = $product->category?->name ?? (is_string($product->category) ? $product->category : '-');
                        $subcategoryName = $product->subcategory?->name ?? (is_string($product->subcategory) ? $product->subcategory : '-');
                        $productStatus = $product->status ?? 'pending';
                    @endphp

                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $categoryName }} / {{ $subcategoryName }}</td>
                        <td>
                            <span class="badge bg-{{ $productStatus === 'approved' ? 'success' : ($productStatus === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($productStatus) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if ($productStatus !== 'approved')
                                <form class="d-inline" method="POST" action="{{ route('admin.vendor-products.approve', $product) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Approve</button>
                                </form>
                            @endif

                            @if ($productStatus !== 'rejected')
                                <form class="d-inline" method="POST" action="{{ route('admin.vendor-products.reject', $product) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No records.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $products->links() }}
    </div>
</div>
@endsection
