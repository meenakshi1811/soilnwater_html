@extends('backend.layouts.app')
@section('title','View Product')

@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <p class="ems-kicker mb-1">Vendor Portal</p>
      <h2 class="admin-title mb-0">Product Details</h2>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="{{ route('vendor.products.edit', $product) }}">Edit Product</a>
      <a class="btn btn-outline-secondary" href="{{ route('vendor.products.index') }}">Back to Listing</a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="chart-card p-3 p-lg-4 h-100">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
          <div>
            <h3 class="mb-1">{{ $product->name }}</h3>
            <div class="text-muted small">SKU: {{ $product->sku ?: 'N/A' }} · Brand: {{ $product->brand ?: 'N/A' }}</div>
          </div>
          <span class="badge bg-{{ $product->status==='approved'?'success':($product->status==='rejected'?'danger':'warning') }} fs-6">
            {{ ucfirst($product->status ?? 'pending') }}
          </span>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">Base Price</div><div class="fw-semibold">₹{{ number_format((float) $product->base_price, 2) }}</div></div></div>
          <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">Discount</div><div class="fw-semibold">{{ (float) $product->discount_percent }}%</div></div></div>
          <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">Final Price</div><div class="fw-semibold text-success">₹{{ number_format((float) $product->final_price, 2) }}</div></div></div>
          <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">Stock Quantity</div><div class="fw-semibold">{{ (int) $product->stock_quantity }}</div></div></div>
          <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">Shipping Charges</div><div class="fw-semibold">₹{{ number_format((float) $product->shipping_charges, 2) }}</div></div></div>
          <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">Online Sale</div><div class="fw-semibold">{{ $product->is_online_sale ? 'Enabled' : 'Disabled' }}</div></div></div>
        </div>

        <h5 class="mb-2">Description</h5>
        <p class="text-secondary mb-4">{{ $product->description ?: 'No description added yet.' }}</p>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <h6 class="mb-1">Category</h6>
            <p class="mb-0">{{ $product->category?->name ?? $product->category ?? 'N/A' }}</p>
          </div>
          <div class="col-md-6">
            <h6 class="mb-1">Subcategory</h6>
            <p class="mb-0">{{ $product->subcategory?->name ?? 'N/A' }}</p>
          </div>
          <div class="col-md-6">
            <h6 class="mb-1">Child Category</h6>
            <p class="mb-0">{{ $product->childCategory?->name ?? 'N/A' }}</p>
          </div>
          <div class="col-md-6">
            <h6 class="mb-1">Available Colors</h6>
            <p class="mb-0">{{ $product->colors ?: 'N/A' }}</p>
          </div>
          <div class="col-md-6">
            <h6 class="mb-1">Available Sizes</h6>
            <p class="mb-0">{{ $product->sizes ?: 'N/A' }}</p>
          </div>
          <div class="col-12">
            <h6 class="mb-1">Location</h6>
            <p class="mb-0">{{ $product->location ?: 'N/A' }} @if($product->latitude && $product->longitude) <span class="text-muted">({{ $product->latitude }}, {{ $product->longitude }})</span>@endif</p>
          </div>
        </div>

        @if(!empty($product->specs))
          <h5 class="mb-2">Specifications</h5>
          <div class="table-responsive mb-4">
            <table class="table table-sm align-middle">
              <thead><tr><th>Feature</th><th>Value</th></tr></thead>
              <tbody>
                @foreach($product->specs as $spec)
                  <tr>
                    <td>{{ $spec['feature'] ?? '-' }}</td>
                    <td>{{ $spec['value'] ?? '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif

        @if(!empty($product->bulk_tiers))
          <h5 class="mb-2">Bulk Pricing</h5>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Minimum Quantity</th><th>Price per Unit</th></tr></thead>
              <tbody>
                @foreach($product->bulk_tiers as $tier)
                  <tr>
                    <td>{{ (int) ($tier['buy_min'] ?? 0) }}</td>
                    <td>₹{{ number_format((float) ($tier['price'] ?? 0), 2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-4">
      <div class="chart-card p-3 p-lg-4 mb-4">
        <h5 class="mb-3">Media</h5>
        @if(!empty($product->images))
          <div class="row g-2 mb-3">
            @foreach($product->images as $image)
              <div class="col-6">
                <a href="{{ asset($image) }}" target="_blank" rel="noopener">
                  <img src="{{ asset($image) }}" class="img-fluid rounded border" alt="{{ $product->name }} image">
                </a>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-muted mb-3">No product images uploaded.</p>
        @endif

        @if(!empty($product->video_file))
          <h6 class="mb-1">Product Video</h6>
          <video class="w-100 rounded border mb-3" controls preload="metadata" src="{{ asset($product->video_file) }}"></video>
        @endif

        @if(!empty($product->youtube_link))
          <h6 class="mb-1">YouTube Link</h6>
          <a href="{{ $product->youtube_link }}" target="_blank" rel="noopener">{{ $product->youtube_link }}</a>
        @endif
      </div>

      <div class="chart-card p-3 p-lg-4">
        <h5 class="mb-3">Approval Info</h5>
        <div class="small text-muted mb-1">Created</div>
        <div class="mb-3">{{ optional($product->created_at)->format('d M Y, h:i A') ?: 'N/A' }}</div>
        <div class="small text-muted mb-1">Last Updated</div>
        <div class="mb-3">{{ optional($product->updated_at)->format('d M Y, h:i A') ?: 'N/A' }}</div>
        <div class="small text-muted mb-1">Approved At</div>
        <div>{{ optional($product->approved_at)->format('d M Y, h:i A') ?: 'Not approved yet' }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
