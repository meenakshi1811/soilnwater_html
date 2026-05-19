@extends('backend.layouts.app')
@section('title','Manage Products')
@section('content')
<div class="admin-panel ems-page">
<div class="d-flex justify-content-between mb-3"><h2 class="admin-title">Manage Products</h2><a href="{{ route('vendor.products.create') }}" class="btn btn-primary">Create Product</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<form class="mb-3"><input class="form-control" name="q" value="{{ $q }}" placeholder="Search products"></form>
<div class="chart-card p-3 table-responsive"><table class="table"><thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead><tbody>@forelse($products as $product)<tr><td>{{ $product->name }}<div class="small text-muted">{{ $product->sku }}</div></td><td>{{ $product->category }}</td><td>₹{{ number_format($product->final_price,2) }}</td><td>{{ $product->stock_quantity }}</td><td class="text-end"><a href="{{ route('vendor.products.show',$product) }}" class="btn btn-sm btn-outline-secondary">View</a> <a href="{{ route('vendor.products.edit',$product) }}" class="btn btn-sm btn-outline-primary">Edit</a> <button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="{{ $product->id }}">Delete</button></td></tr>@empty<tr><td colspan="5" class="text-center">No products found.</td></tr>@endforelse</tbody></table>{{ $products->links() }}</div>
</div>
@endsection
@push('scripts')<script>document.querySelectorAll('.js-delete').forEach(b=>b.addEventListener('click',()=>{if(!confirm('Delete product?'))return;fetch('/vendor/products/'+b.dataset.id,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}}).then(()=>location.reload())}))</script>@endpush
