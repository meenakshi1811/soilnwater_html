@extends('backend.layouts.app')
@section('title', $product->exists ? 'Edit Product' : 'Create Product')
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div><p class="ems-kicker mb-1">Vendor Portal</p><h2 class="admin-title mb-0">{{ $product->exists ? 'Edit Product' : 'Add New Product' }}</h2></div>
    <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">Back to Listing</a>
  </div>
  @php($oldTiers = old('bulk_min') ? collect(old('bulk_min'))->map(fn($m,$i)=>['buy_min'=>$m,'price'=>old('bulk_price')[$i] ?? ''])->values()->all() : ($product->bulk_tiers ?? [['buy_min'=>10,'price'=>'']]))
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="vendor-product-form" data-ajax-create="{{ $product->exists ? '0' : '1' }}" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('vendor.products.update',$product) : route('vendor.products.store') }}" class="row g-3">@csrf @if($product->exists) @method('PUT') @endif
    <div class="col-lg-8"><div class="chart-card p-4"><h5 class="mb-3">Basic Information</h5><div class="row g-3">
      <div class="col-12"><label class="form-label">Product Name *</label><input class="form-control" required name="name" value="{{ old('name',$product->name) }}"></div>
      <div class="col-md-6"><label class="form-label">Brand</label><input class="form-control" name="brand" value="{{ old('brand',$product->brand) }}"></div>
      <div class="col-md-6"><label class="form-label">SKU</label><input class="form-control" name="sku" value="{{ old('sku',$product->sku) }}"></div>
      <div class="col-md-6"><label class="form-label">Category *</label><select id="category_id" class="form-select" name="category_id" required><option value="">Select category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id',$product->category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label">Subcategory *</label><select id="subcategory_id" class="form-select" name="subcategory_id" required data-current="{{ old('subcategory_id',$product->subcategory_id) }}"><option value="">Select subcategory</option></select></div>
      <div class="col-md-6"><label class="form-label">Colors</label><input class="form-control" name="colors" placeholder="Red, Blue" value="{{ old('colors',$product->colors) }}"></div>
      <div class="col-md-6"><label class="form-label">Sizes</label><input class="form-control" name="sizes" placeholder="S, M, L" value="{{ old('sizes',$product->sizes) }}"></div>
      <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="4" name="description">{{ old('description',$product->description) }}</textarea></div>
    </div></div></div>
    <div class="col-lg-4"><div class="chart-card p-4"><h5 class="mb-3">Media & Listing</h5><label class="form-label">Product Images</label><input class="form-control" type="file" name="images[]" multiple accept="image/*"><small class="text-muted">Max 4 MB per image.</small><label class="form-label mt-3">Product Video (MP4/WEBM)</label><input class="form-control" type="file" name="video_file" accept="video/mp4,video/webm"><small class="text-muted">Max 20 MB.</small><label class="form-label mt-3">YouTube Link</label><input class="form-control" type="url" name="youtube_link" value="{{ old('youtube_link',$product->youtube_link) }}"><div class="form-check mt-3"><input class="form-check-input" type="checkbox" value="1" name="is_online_sale" @checked(old('is_online_sale',$product->is_online_sale))><label class="form-check-label">List for Online Sale</label></div></div></div>
    <div class="col-12"><div class="chart-card p-4"><h5 class="mb-3">Pricing & Logistics</h5><div class="row g-3"><div class="col-md-3"><label class="form-label">Base Price *</label><input type="number" step="0.01" id="base_price" class="form-control" name="base_price" value="{{ old('base_price',$product->base_price) }}"></div><div class="col-md-3"><label class="form-label">Discount %</label><input type="number" step="0.01" id="discount_percent" class="form-control" name="discount_percent" value="{{ old('discount_percent',$product->discount_percent ?? 0) }}"></div><div class="col-md-3"><label class="form-label">Final Price *</label><input type="number" step="0.01" id="final_price" class="form-control" name="final_price" value="{{ old('final_price',$product->final_price) }}"></div><div class="col-md-3"><label class="form-label">Shipping Charges *</label><input type="number" step="0.01" class="form-control" name="shipping_charges" value="{{ old('shipping_charges',$product->shipping_charges ?? 0) }}"></div><div class="col-md-3"><label class="form-label">Stock Quantity *</label><input type="number" class="form-control" name="stock_quantity" value="{{ old('stock_quantity',$product->stock_quantity ?? 0) }}"></div></div></div></div>


    <div class="col-12"><div class="chart-card p-4"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Specs</h5><button type="button" class="btn btn-link p-0 text-decoration-none" id="add-spec">+ Add Row</button></div><div id="specs-wrap" class="row g-2">@php($oldSpecs = old('spec_feature') ? collect(old('spec_feature'))->map(fn($f,$i)=>['feature'=>$f,'value'=>old('spec_value')[$i] ?? ''])->values()->all() : ($product->specs ?? [['feature'=>'','value'=>'']]))@foreach($oldSpecs as $spec)<div class="col-12 spec-row"><div class="row g-2"><div class="col-md-5"><input class="form-control" name="spec_feature[]" placeholder="Feature" value="{{ $spec['feature'] ?? '' }}"></div><div class="col-md-5"><input class="form-control" name="spec_value[]" placeholder="Value" value="{{ $spec['value'] ?? '' }}"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-spec">Remove</button></div></div></div>@endforeach</div></div></div>

    <div class="col-12"><div class="chart-card p-4" style="background:#f1f5ff;border:1px solid #c9d8ff;"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 text-primary"><i class="fa-solid fa-layer-group me-2"></i>Bulk Quantity Pricing (B2B)</h5><button type="button" class="btn btn-primary btn-sm" id="add-tier">+ Add Tier</button></div><div id="tiers-wrap" class="row g-2">@foreach($oldTiers as $tier)<div class="col-12 tier-row"><div class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label">Buy Min</label><input type="number" class="form-control" name="bulk_min[]" value="{{ $tier['buy_min'] ?? '' }}" min="1"></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" class="form-control" name="bulk_price[]" value="{{ $tier['price'] ?? '' }}" placeholder="Unit Price"></div><div class="col-md-3"><button type="button" class="btn btn-outline-danger btn-sm remove-tier">Remove</button></div></div></div>@endforeach</div><small class="text-primary d-block mt-2">Example: Buy 10+ @ ₹90/unit (Discounted from Base Price)</small></div></div>


    <div class="col-12">
      <div class="form-check">
        <input class="form-check-input @error('accept_terms') is-invalid @enderror" type="checkbox" value="1" id="accept_terms" name="accept_terms" required {{ old('accept_terms') ? 'checked' : '' }}>
        <label class="form-check-label" for="accept_terms">I accept the <a href="{{ route('frontend.terms.show', ['moduleKey' => 'vendors']) }}" target="_blank" rel="noopener">Terms & Conditions</a>.</label>
            @error('accept_terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="col-12 text-end"><button class="btn btn-dark px-4 py-2">Save & Send for Approval</button></div>
  </form>
</div>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
const subcategoryByCategory = @json($categories->mapWithKeys(fn($c)=>[$c->id=>$c->children->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])->values()]));
const cat = document.getElementById('category_id'); const sub = document.getElementById('subcategory_id');
function fillSub(){const rows=subcategoryByCategory[cat.value]||[]; const selected=sub.dataset.current; sub.innerHTML='<option value="">Select subcategory</option>'; rows.forEach(r=>{const o=document.createElement('option');o.value=r.id;o.textContent=r.name;if(String(r.id)===String(selected))o.selected=true;sub.appendChild(o);});}
cat?.addEventListener('change',()=>{sub.dataset.current='';fillSub();}); fillSub();
['base_price','discount_percent'].forEach(i=>document.getElementById(i)?.addEventListener('input',()=>{const b=parseFloat(document.getElementById('base_price').value||0),d=parseFloat(document.getElementById('discount_percent').value||0);document.getElementById('final_price').value=(b-(b*d/100)).toFixed(2)}));

const specsWrap=document.getElementById('specs-wrap');document.getElementById('add-spec')?.addEventListener('click',()=>{const row=document.createElement('div');row.className='col-12 spec-row';row.innerHTML='<div class="row g-2"><div class="col-md-5"><input class="form-control" name="spec_feature[]" placeholder="Feature"></div><div class="col-md-5"><input class="form-control" name="spec_value[]" placeholder="Value"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-spec">Remove</button></div></div>';specsWrap.appendChild(row)});specsWrap?.addEventListener('click',e=>{if(e.target.classList.contains('remove-spec'))e.target.closest('.spec-row').remove();});

const wrap=document.getElementById('tiers-wrap');document.getElementById('add-tier')?.addEventListener('click',()=>{const row=document.createElement('div');row.className='col-12 tier-row';row.innerHTML='<div class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label">Buy Min</label><input type="number" class="form-control" name="bulk_min[]" min="1"></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" class="form-control" name="bulk_price[]" placeholder="Unit Price"></div><div class="col-md-3"><button type="button" class="btn btn-outline-danger btn-sm remove-tier">Remove</button></div></div>';wrap.appendChild(row)});wrap?.addEventListener('click',e=>{if(e.target.classList.contains('remove-tier'))e.target.closest('.tier-row').remove();});

const productForm=document.getElementById('vendor-product-form');
productForm?.addEventListener('submit',async function(e){
  if(productForm.dataset.ajaxCreate!=='1') return;
  e.preventDefault();
  const submitBtn=productForm.querySelector('button[type="submit"]');
  const originalText=submitBtn?.innerHTML;
  if(submitBtn){submitBtn.disabled=true;submitBtn.innerHTML='Saving...';}
  const fd=new FormData(productForm);
  try{
    const res=await fetch(productForm.action,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd});
    const payload=await res.json();
    if(!res.ok){
      const msg=payload.message || Object.values(payload.errors||{}).flat().join('\n') || 'Unable to save product.';
      if(window.toastr?.error){toastr.error(msg);} else if(window.FormHelper?.showToast){window.FormHelper.showToast('danger',msg);} else {alert(msg);} 
      return;
    }
    const okMsg=payload.message || 'Product submitted successfully.';
    if(window.toastr?.success){toastr.success(okMsg);} else if(window.FormHelper?.showToast){window.FormHelper.showToast('success',okMsg);} else {alert(okMsg);} 
    setTimeout(()=>window.location.href=(payload.redirect || '{{ route('vendor.products.index') }}'),800);
  }catch(err){
    const msg='Network error while saving product.';
    if(window.toastr?.error){toastr.error(msg);} else if(window.FormHelper?.showToast){window.FormHelper.showToast('danger',msg);} else {alert(msg);} 
  }finally{if(submitBtn){submitBtn.disabled=false;submitBtn.innerHTML=originalText;}}
});

</script>
@endpush
