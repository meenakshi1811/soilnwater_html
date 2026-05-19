@extends('backend.layouts.app')
@section('title','Manage Products')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2"><div><p class="ems-kicker mb-1">Vendor Portal</p><h2 class="admin-title mb-0">Manage Products</h2></div><a href="{{ route('vendor.products.create') }}" class="btn btn-primary ems-btn-primary"><i class="fa-solid fa-plus me-1"></i>Create Product</a></div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="chart-card p-3 p-lg-4"><form class="row g-2 mb-3"><div class="col-md-5"><input class="form-control" name="q" value="{{ $q }}" placeholder="Search by product name or sku"></div><div class="col-auto"><button class="btn btn-outline-primary">Search</button></div></form>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>@forelse($products as $product)<tr><td><div class="fw-semibold">{{ $product->name }}</div><div class="small text-muted">SKU: {{ $product->sku ?: '-' }}</div></td><td>{{ $product->category?->name ?? $product->category ?? '-' }}<div class="small text-muted">{{ $product->subcategory?->name ?? '-' }}</div></td><td>₹{{ number_format($product->final_price,2) }}</td><td>{{ $product->stock_quantity }}</td><td><span class="badge bg-{{ $product->status==='approved'?'success':($product->status==='rejected'?'danger':'warning') }}">{{ ucfirst($product->status ?? "pending") }}</span></td><td class="text-end"><a href="{{ route('vendor.products.show',$product) }}" class="btn btn-sm btn-outline-secondary">View</a> <a href="{{ route('vendor.products.edit',$product) }}" class="btn btn-sm btn-outline-primary">Edit</a> <button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="{{ $product->id }}">Delete</button></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No products found.</td></tr>@endforelse</tbody></table></div>{{ $products->links() }}</div>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.js-delete').forEach(function (button) {
  button.addEventListener('click', function () {
    var row = button.closest('tr');
    var deleteUrl = '/vendor/products/' + button.dataset.id;
    var csrfToken = document.querySelector('meta[name=csrf-token]')?.content;

    var onConfirmDelete = function () {
      fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        }
      })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Unable to delete product.');
        }

        row?.remove();
        if (window.toastr && typeof window.toastr.success === 'function') {
          window.toastr.success('Product deleted successfully.');
        }
      })
      .catch(function () {
        if (window.toastr && typeof window.toastr.error === 'function') {
          window.toastr.error('Unable to delete product.');
        }
      });
    };

    if (window.Swal && typeof window.Swal.fire === 'function') {
      window.Swal.fire({
        title: 'Delete product?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
      }).then(function (result) {
        if (result.isConfirmed) {
          onConfirmDelete();
        }
      });
      return;
    }

    if (confirm('Delete this product permanently?')) {
      onConfirmDelete();
    }
  });
});
</script>
@endpush
