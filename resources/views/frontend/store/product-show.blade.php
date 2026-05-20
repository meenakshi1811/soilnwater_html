@extends('frontend.layouts.app')

@section('title', $product->name.' - '.$vendor->publicDisplayName())

@section('content')
<div class="container py-5">
    <a href="{{ route('store.show', $vendor->slug) }}#products" class="btn btn-link ps-0">← Back to products</a>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    @php($image = is_array($product->images) ? ($product->images[0] ?? null) : null)
                    <img src="{{ $image ? asset($image) : asset('assets/images/ad-sample.png') }}" class="img-fluid rounded mb-3" alt="{{ $product->name }}">
                    <h1 class="h3">{{ $product->name }}</h1>
                    <p class="text-muted mb-2">{{ $product->brand ?: 'Brand not specified' }}</p>
                    <p>{!! nl2br(e($product->description ?: 'No description available.')) !!}</p>
                    <p class="mb-1"><strong>Price:</strong> ₹{{ number_format((float) $product->final_price, 2) }}</p>
                    <p class="mb-1"><strong>Stock:</strong> {{ number_format((int) $product->stock_quantity) }}</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#enquiryModal">Send Enquiry</button>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @foreach($groupedAds as $sizeType => $sizeAds)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light"><small class="fw-semibold">Ad Placement: {{ $sizeType }}</small></div>
                    <div class="card-body p-2">
                        @foreach($sizeAds->take(2) as $ad)
                            @if($ad->final_image)
                                <a href="{{ route('frontend.ads.show', $ad) }}"><img src="{{ asset($ad->final_image) }}" class="img-fluid rounded mb-2" alt="{{ $ad->title }}"></a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Send Product Enquiry</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        @auth
        <form id="enquiryForm">
          @csrf
          <div class="mb-2"><label>Email</label><input type="email" name="email" value="{{ auth()->user()->email }}" class="form-control" required></div>
          <div class="mb-2"><label>Number</label><input type="text" name="phone_number" value="{{ auth()->user()->phone_number }}" class="form-control" required></div>
          <div class="mb-2"><label>Way to connect</label><select name="preferred_contact" class="form-select" required><option value="text">Text</option><option value="whatsapp">WhatsApp</option><option value="call">Call</option><option value="email">Email</option></select></div>
          <div class="mb-2"><label>Reason</label><textarea name="reason" class="form-control" rows="4" required></textarea></div>
          <button class="btn btn-primary w-100" type="submit">Send Enquiry</button>
        </form>
        @else
        <div class="alert alert-warning mb-0">Please login to send enquiry.</div>
        @endauth
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@auth
<script>
document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){
 e.preventDefault();
 const fd = new FormData(this);
 const res = await fetch("{{ route('store.products.enquiry', [$vendor->slug, $product->id]) }}", {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:fd});
 const data = await res.json();
 alert(data.message || 'Done');
 if(res.ok){ bootstrap.Modal.getInstance(document.getElementById('enquiryModal')).hide(); }
});
</script>
@endauth
@endpush
