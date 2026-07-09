@extends('backend.layouts.app')
@section('title', $product->exists ? 'Edit Product' : 'Create Product')
@section('content')
@php($isAdmin = $isAdmin ?? false)
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div><p class="ems-kicker mb-1">{{ $isAdmin ? 'Admin Portal' : 'Vendor Portal' }}</p><h2 class="admin-title mb-0">{{ $product->exists ? 'Edit Product' : ($isAdmin ? 'Create Product for Vendor' : 'Add New Product') }}</h2></div>
    <a href="{{ $isAdmin ? route('admin.vendor-products.all.index') : route('vendor.products.index') }}" class="btn btn-outline-secondary">Back to Listing</a>
  </div>
  @php($oldTiers = old('bulk_min') ? collect(old('bulk_min'))->map(fn($m,$i)=>['buy_min'=>$m,'price'=>old('bulk_price')[$i] ?? ''])->values()->all() : ($product->bulk_tiers ?? [['buy_min'=>10,'price'=>'']]))
  @php($visibleErrors = collect($errors->getMessages())->except(['latitude', 'longitude'])->flatten())
  @if ($visibleErrors->isNotEmpty())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($visibleErrors as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="vendor-product-form" data-ajax-create="{{ $product->exists ? '0' : '1' }}" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? ($isAdmin ? route('vendor.products.update', $product) : route('vendor.products.update',$product)) : ($isAdmin ? route('admin.vendor-products.store') : route('vendor.products.store')) }}" class="row g-3">@csrf @if($product->exists) @method('PUT') @endif
    <div class="col-lg-8"><div class="chart-card p-4"><h5 class="mb-3">Basic Information</h5><div class="row g-3">
      @if($isAdmin)
      <div class="col-12"><label class="form-label">Vendor *</label><select class="form-select @error('vendor_id') is-invalid @enderror" name="vendor_id" required><option value="">Select vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->display_name ?: $vendor->company_name }}</option>@endforeach</select>@error('vendor_id')<div id="vendor_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      @endif
      <div class="col-12"><label class="form-label">Product Name *</label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name',$product->name) }}">@error('name')<div id="name-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Brand</label><input class="form-control @error('brand') is-invalid @enderror" name="brand" value="{{ old('brand',$product->brand) }}">@error('brand')<div id="brand-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">SKU</label><input class="form-control @error('sku') is-invalid @enderror" name="sku" value="{{ old('sku',$product->sku) }}">@error('sku')<div id="sku-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Category *</label><select id="category_id" class="form-select @error('category_id') is-invalid @enderror" name="category_id"><option value="">Select category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id',$product->category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach</select>@error('category_id')<div id="category_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Subcategory *</label><select id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" name="subcategory_id" data-current="{{ old('subcategory_id',$product->subcategory_id) }}"><option value="">Select subcategory</option></select>@error('subcategory_id')<div id="subcategory_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Child Category</label><select id="child_category_id" class="form-select @error('child_category_id') is-invalid @enderror" name="child_category_id" data-current="{{ old('child_category_id',$product->child_category_id) }}"><option value="">Select child category</option></select>@error('child_category_id')<div id="child_category_id-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Colors</label><input class="form-control @error('colors') is-invalid @enderror" name="colors" placeholder="Red, Blue" value="{{ old('colors',$product->colors) }}">@error('colors')<div id="colors-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-md-6"><label class="form-label">Sizes</label><input class="form-control @error('sizes') is-invalid @enderror" name="sizes" placeholder="S, M, L" value="{{ old('sizes',$product->sizes) }}">@error('sizes')<div id="sizes-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
      <div class="col-12"><label class="form-label">Description</label><textarea class="form-control @error('description') is-invalid @enderror" rows="4" name="description">{{ old('description',$product->description) }}</textarea>@error('description')<div id="description-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
    </div></div></div>
    <div class="col-lg-4"><div class="chart-card p-4"><h5 class="mb-3">Media & Listing</h5>
      <div class="vendor-media-block">
        <label class="form-label">Product Images</label>
        <label class="vendor-media-dropzone" for="productImagesInput" id="imageDropzone">
          <input class="vendor-media-input @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" type="file" id="productImagesInput" name="images[]" multiple accept="image/*">
          <div class="vendor-media-dropzone__inner">
            <span class="vendor-media-dropzone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
            <strong>Upload product images</strong>
            <span>Click to browse or drop files here</span>
            <small>Up to 4 MB per image</small>
          </div>
        </label>
        @error('images.*')<div id="images-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <div id="imagePreviewGrid" class="vendor-media-preview-grid"></div>
      </div>

      <div class="vendor-media-block mt-4">
        <label class="form-label">Product Video (MP4/WEBM)</label>
        <label class="vendor-media-dropzone vendor-media-dropzone--video" for="productVideoInput" id="videoDropzone">
          <input class="vendor-media-input @error('video_file') is-invalid @enderror" type="file" id="productVideoInput" name="video_file" accept="video/mp4,video/webm">
          <div class="vendor-media-dropzone__inner">
            <span class="vendor-media-dropzone__icon"><i class="fa-solid fa-video"></i></span>
            <strong>Upload product video</strong>
            <span>Click to browse or drop a video file</span>
            <small>Max 20 MB · MP4 or WEBM</small>
          </div>
        </label>
        @error('video_file')<div id="video_file-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <div id="videoPreviewWrap" class="vendor-video-preview-card d-none">
          <video id="videoPreviewPlayer" controls playsinline preload="metadata"></video>
          <div class="vendor-video-preview-card__meta">
            <div>
              <strong id="videoPreviewName"></strong>
              <small id="videoPreviewSize" class="d-block text-muted"></small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" id="removeVideoPreview">
              <i class="fa-solid fa-trash-can me-1"></i> Remove
            </button>
          </div>
        </div>
      </div>

      <label class="form-label mt-4">YouTube Link</label>
      <input class="form-control @error('youtube_link') is-invalid @enderror" type="url" name="youtube_link" value="{{ old('youtube_link',$product->youtube_link) }}" placeholder="https://youtube.com/watch?v=...">
      @error('youtube_link')<div id="youtube_link-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror
      <div class="form-check mt-3">
        <input class="form-check-input" type="checkbox" value="1" name="is_online_sale" id="is_online_sale" @checked(old('is_online_sale',$product->is_online_sale))>
        <label class="form-check-label" for="is_online_sale">List for Online Sale</label>
      </div>
    </div></div>
    <div class="col-12"><div class="chart-card p-4"><h5 class="mb-3">Pricing & Logistics</h5><div class="row g-3"><div class="col-md-3"><label class="form-label">Base Price *</label><input type="number" step="0.01" id="base_price" class="form-control @error('base_price') is-invalid @enderror" name="base_price" value="{{ old('base_price',$product->base_price) }}">@error('base_price')<div id="base_price-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-3"><label class="form-label">Discount %</label><input type="number" step="0.01" id="discount_percent" class="form-control @error('discount_percent') is-invalid @enderror" name="discount_percent" value="{{ old('discount_percent',$product->discount_percent ?? 0) }}">@error('discount_percent')<div id="discount_percent-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-3"><label class="form-label">Final Price *</label><input type="number" step="0.01" id="final_price" class="form-control @error('final_price') is-invalid @enderror" name="final_price" value="{{ old('final_price',$product->final_price) }}">@error('final_price')<div id="final_price-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-3"><label class="form-label">Shipping Charges *</label><input type="number" step="0.01" class="form-control @error('shipping_charges') is-invalid @enderror" name="shipping_charges" value="{{ old('shipping_charges',$product->shipping_charges ?? 0) }}">@error('shipping_charges')<div id="shipping_charges-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-3"><label class="form-label">Stock Quantity *</label><input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" name="stock_quantity" value="{{ old('stock_quantity',$product->stock_quantity ?? 0) }}">@error('stock_quantity')<div id="stock_quantity-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label">Location in India *</label><input id="location" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location',$product->location) }}" placeholder="Search location in India" autocomplete="off">@error('location')<div id="location-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Select a location suggestion from India only.</small></div><input id="latitude" type="hidden" name="latitude" value="{{ old('latitude',$product->latitude) }}"><input id="longitude" type="hidden" name="longitude" value="{{ old('longitude',$product->longitude) }}"></div></div></div>


    <div class="col-12"><div class="chart-card p-4"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Specs</h5><button type="button" class="btn btn-link p-0 text-decoration-none" id="add-spec">+ Add Row</button></div><div id="specs-wrap" class="row g-2">@php($oldSpecs = old('spec_feature') ? collect(old('spec_feature'))->map(fn($f,$i)=>['feature'=>$f,'value'=>old('spec_value')[$i] ?? ''])->values()->all() : ($product->specs ?? [['feature'=>'','value'=>'']]))@foreach($oldSpecs as $spec)<div class="col-12 spec-row"><div class="row g-2"><div class="col-md-5"><input class="form-control" name="spec_feature[]" placeholder="Feature" value="{{ $spec['feature'] ?? '' }}"></div><div class="col-md-5"><input class="form-control" name="spec_value[]" placeholder="Value" value="{{ $spec['value'] ?? '' }}"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-spec">Remove</button></div></div></div>@endforeach</div></div></div>

    <div class="col-12"><div class="chart-card p-4" style="background:#f1f5ff;border:1px solid #c9d8ff;"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 text-primary"><i class="fa-solid fa-layer-group me-2"></i>Bulk Quantity Pricing (B2B)</h5><button type="button" class="btn btn-primary btn-sm" id="add-tier">+ Add Tier</button></div><div id="tiers-wrap" class="row g-2">@foreach($oldTiers as $tier)<div class="col-12 tier-row"><div class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label">Buy Min</label><input type="number" class="form-control" name="bulk_min[]" value="{{ $tier['buy_min'] ?? '' }}" min="1"></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" class="form-control" name="bulk_price[]" value="{{ $tier['price'] ?? '' }}" placeholder="Unit Price"></div><div class="col-md-3"><button type="button" class="btn btn-outline-danger btn-sm remove-tier">Remove</button></div></div></div>@endforeach</div><small class="text-primary d-block mt-2">Example: Buy 10+ @ ₹90/unit (Discounted from Base Price)</small></div></div>


    <div class="col-12">
      @unless($isAdmin)
      <div class="form-check">
        <input class="form-check-input @error('accept_terms') is-invalid @enderror" type="checkbox" value="1" id="accept_terms" name="accept_terms" {{ old('accept_terms') ? 'checked' : '' }}>
        <label class="form-check-label" for="accept_terms">I accept the <a href="{{ route('frontend.terms.show', ['moduleKey' => 'vendors']) }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-underline" style="color:#0d6efd;">Terms & Conditions</a>.</label>
            @error('accept_terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
      </div>
      @endunless
    </div>

    <div class="col-12 text-end"><button type="submit" id="productSubmitBtn" class="btn btn-dark px-4 py-2">Save & Send for Approval</button></div>
  </form>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
.vendor-media-block { margin-bottom: 0.25rem; }
.vendor-media-dropzone {
  display: block;
  border: 1.5px dashed #cbd5e1;
  border-radius: 16px;
  background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
  padding: 1.1rem;
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  margin-bottom: 0.75rem;
}
.vendor-media-dropzone:hover,
.vendor-media-dropzone.is-dragover {
  border-color: #2563eb;
  background: #f8fbff;
  box-shadow: 0 8px 22px rgba(37, 99, 235, 0.08);
}
.vendor-media-dropzone--video:hover,
.vendor-media-dropzone--video.is-dragover {
  border-color: #7c3aed;
  box-shadow: 0 8px 22px rgba(124, 58, 237, 0.08);
}
.vendor-media-input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}
.vendor-media-dropzone__inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.25rem;
  color: #475569;
}
.vendor-media-dropzone__inner strong {
  color: #0f172a;
  font-size: 0.92rem;
}
.vendor-media-dropzone__inner span {
  font-size: 0.82rem;
}
.vendor-media-dropzone__inner small {
  color: #94a3b8;
}
.vendor-media-dropzone__icon {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(37, 99, 235, 0.1);
  color: #2563eb;
  font-size: 1.1rem;
  margin-bottom: 0.35rem;
}
.vendor-media-dropzone.has-files {
  padding: 0.75rem;
}
.vendor-media-dropzone.has-files .vendor-media-dropzone__inner strong {
  font-size: 0.84rem;
}
.vendor-media-dropzone--video .vendor-media-dropzone__icon {
  background: rgba(124, 58, 237, 0.1);
  color: #7c3aed;
}
.vendor-media-preview-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}
.vendor-media-preview-card {
  position: relative;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
}
.vendor-media-preview-card img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: cover;
  display: block;
}
.vendor-media-preview-card__name {
  display: block;
  padding: 0.45rem 0.55rem;
  font-size: 0.72rem;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.vendor-media-preview-remove {
  position: absolute;
  top: 0.45rem;
  right: 0.45rem;
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.72);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease;
}
.vendor-media-preview-remove:hover {
  background: #dc2626;
}
.vendor-video-preview-card {
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
}
.vendor-video-preview-card video {
  width: 100%;
  display: block;
  max-height: 220px;
  background: #0f172a;
}
.vendor-video-preview-card__meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.85rem 0.95rem;
}
.vendor-video-preview-card__meta strong {
  display: block;
  font-size: 0.86rem;
  color: #0f172a;
  word-break: break-word;
}
#productSubmitBtn.is-loading {
  pointer-events: none;
  opacity: 0.85;
}
</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
toastr.options = {
  closeButton: true,
  progressBar: true,
  positionClass: 'toast-top-right',
  timeOut: 4000,
  extendedTimeOut: 2000
};
</script>
<script>
const categoryTree = {!! json_encode(
  $categories->mapWithKeys(function ($category) {
    return [
      $category->id => $category->children->map(function ($subCategory) {
        return [
          'id' => $subCategory->id,
          'name' => $subCategory->name,
          'children' => $subCategory->children->map(function ($childCategory) {
            return [
              'id' => $childCategory->id,
              'name' => $childCategory->name,
            ];
          })->values(),
        ];
      })->values(),
    ];
  }),
  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
) !!};
const cat = document.getElementById('category_id'); const sub = document.getElementById('subcategory_id'); const child = document.getElementById('child_category_id');
function fillSub(){const rows=categoryTree[cat.value]||[]; const selected=sub.dataset.current; sub.innerHTML='<option value="">Select subcategory</option>'; rows.forEach(r=>{const o=document.createElement('option');o.value=r.id;o.textContent=r.name;if(String(r.id)===String(selected))o.selected=true;sub.appendChild(o);});}
function fillChild(){const rows=categoryTree[cat.value]||[];const chosen=rows.find(r=>String(r.id)===String(sub.value));const children=chosen?.children||[];const selected=child.dataset.current;child.innerHTML='<option value="">Select child category</option>';children.forEach(r=>{const o=document.createElement('option');o.value=r.id;o.textContent=r.name;if(String(r.id)===String(selected))o.selected=true;child.appendChild(o);});}
cat?.addEventListener('change',()=>{sub.dataset.current='';child.dataset.current='';fillSub();fillChild();if(window.jQuery){jQuery(sub).valid();}});
sub?.addEventListener('change',()=>{child.dataset.current='';fillChild();if(window.jQuery){jQuery(child).valid();}});
fillSub(); fillChild();
['base_price','discount_percent'].forEach(i=>document.getElementById(i)?.addEventListener('input',()=>{const b=parseFloat(document.getElementById('base_price').value||0),d=parseFloat(document.getElementById('discount_percent').value||0);document.getElementById('final_price').value=(b-(b*d/100)).toFixed(2)}));

const specsWrap=document.getElementById('specs-wrap');document.getElementById('add-spec')?.addEventListener('click',()=>{const row=document.createElement('div');row.className='col-12 spec-row';row.innerHTML='<div class="row g-2"><div class="col-md-5"><input class="form-control" name="spec_feature[]" placeholder="Feature"></div><div class="col-md-5"><input class="form-control" name="spec_value[]" placeholder="Value"></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-spec">Remove</button></div></div>';specsWrap.appendChild(row)});specsWrap?.addEventListener('click',e=>{if(e.target.classList.contains('remove-spec'))e.target.closest('.spec-row').remove();});

const wrap=document.getElementById('tiers-wrap');document.getElementById('add-tier')?.addEventListener('click',()=>{const row=document.createElement('div');row.className='col-12 tier-row';row.innerHTML='<div class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label">Buy Min</label><input type="number" class="form-control" name="bulk_min[]" min="1"></div><div class="col-md-5"><label class="form-label">Price ₹</label><input type="number" step="0.01" class="form-control" name="bulk_price[]" placeholder="Unit Price"></div><div class="col-md-3"><button type="button" class="btn btn-outline-danger btn-sm remove-tier">Remove</button></div></div>';wrap.appendChild(row)});wrap?.addEventListener('click',e=>{if(e.target.classList.contains('remove-tier'))e.target.closest('.tier-row').remove();});

function notify(type, msg) {
  const toastType = type === 'error' ? 'error' : 'success';
  if (window.toastr && typeof window.toastr[toastType] === 'function') {
    window.toastr[toastType](msg);
    return;
  }
  alert(msg);
}

(function initVendorProductMedia() {
  const imageInput = document.getElementById('productImagesInput');
  const imageGrid = document.getElementById('imagePreviewGrid');
  const imageDropzone = document.getElementById('imageDropzone');
  const videoInput = document.getElementById('productVideoInput');
  const videoDropzone = document.getElementById('videoDropzone');
  const videoWrap = document.getElementById('videoPreviewWrap');
  const videoPlayer = document.getElementById('videoPreviewPlayer');
  const videoName = document.getElementById('videoPreviewName');
  const videoSize = document.getElementById('videoPreviewSize');
  const removeVideoBtn = document.getElementById('removeVideoPreview');
  if (!imageInput || !imageGrid) return;

  const imageFiles = [];
  let videoObjectUrl = '';

  function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const power = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / Math.pow(1024, power);
    return `${value.toFixed(power === 0 ? 0 : 1)} ${units[power]}`;
  }

  function syncImageInput() {
    const dt = new DataTransfer();
    imageFiles.forEach((file) => dt.items.add(file));
    imageInput.files = dt.files;
  }

  function renderImages() {
    imageGrid.querySelectorAll('img').forEach((img) => {
      if (img.src && img.src.startsWith('blob:')) {
        URL.revokeObjectURL(img.src);
      }
    });
    imageGrid.innerHTML = '';
    imageFiles.forEach((file, index) => {
      const card = document.createElement('div');
      card.className = 'vendor-media-preview-card';
      const img = document.createElement('img');
      img.alt = file.name;
      img.src = URL.createObjectURL(file);
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'vendor-media-preview-remove';
      removeBtn.setAttribute('data-index', String(index));
      removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      const name = document.createElement('span');
      name.className = 'vendor-media-preview-card__name';
      name.textContent = file.name;
      card.appendChild(img);
      card.appendChild(removeBtn);
      card.appendChild(name);
      imageGrid.appendChild(card);
    });
    if (imageDropzone) {
      imageDropzone.classList.toggle('has-files', imageFiles.length > 0);
      const title = imageDropzone.querySelector('.vendor-media-dropzone__inner strong');
      if (title) {
        title.textContent = imageFiles.length ? 'Add more images' : 'Upload product images';
      }
    }
  }

  function addImages(files) {
    let rejected = false;
    Array.from(files || []).forEach((file) => {
      if (!file.type.startsWith('image/')) return;
      if (file.size > 4 * 1024 * 1024) {
        rejected = true;
        return;
      }
      imageFiles.push(file);
    });
    if (rejected) {
      notify('error', 'Each image must be 4 MB or smaller.');
    }
    syncImageInput();
    renderImages();
  }

  function renderVideo() {
    if (videoObjectUrl) {
      URL.revokeObjectURL(videoObjectUrl);
      videoObjectUrl = '';
    }
    const file = videoInput?.files?.[0];
    if (!file) {
      videoWrap?.classList.add('d-none');
      videoDropzone?.classList.remove('d-none');
      if (videoPlayer) videoPlayer.removeAttribute('src');
      return;
    }
    videoObjectUrl = URL.createObjectURL(file);
    if (videoPlayer) videoPlayer.src = videoObjectUrl;
    if (videoName) videoName.textContent = file.name;
    if (videoSize) videoSize.textContent = formatBytes(file.size);
    videoWrap?.classList.remove('d-none');
    videoDropzone?.classList.add('d-none');
  }

  function bindDropzone(zone, onFiles) {
    if (!zone) return;
    ['dragenter', 'dragover'].forEach((eventName) => {
      zone.addEventListener(eventName, (event) => {
        event.preventDefault();
        zone.classList.add('is-dragover');
      });
    });
    ['dragleave', 'drop'].forEach((eventName) => {
      zone.addEventListener(eventName, (event) => {
        event.preventDefault();
        zone.classList.remove('is-dragover');
        if (eventName === 'drop' && event.dataTransfer?.files?.length) {
          onFiles(event.dataTransfer.files);
        }
      });
    });
  }

  imageInput.addEventListener('change', function () {
    addImages(this.files);
  });

  imageGrid.addEventListener('click', function (event) {
    const button = event.target.closest('.vendor-media-preview-remove');
    if (!button) return;
    const index = Number(button.getAttribute('data-index'));
    if (Number.isNaN(index)) return;
    imageFiles.splice(index, 1);
    syncImageInput();
    renderImages();
  });

  videoInput?.addEventListener('change', function () {
    const file = this.files?.[0];
    if (file && file.size > 20 * 1024 * 1024) {
      this.value = '';
      notify('error', 'Video must be 20 MB or smaller.');
      renderVideo();
      return;
    }
    renderVideo();
  });

  removeVideoBtn?.addEventListener('click', function () {
    if (videoInput) videoInput.value = '';
    renderVideo();
  });

  bindDropzone(imageDropzone, addImages);
  bindDropzone(videoDropzone, function (files) {
    const file = files?.[0];
    if (!file) return;
    if (!file.type.startsWith('video/')) {
      notify('error', 'Please choose an MP4 or WEBM video file.');
      return;
    }
    if (file.size > 20 * 1024 * 1024) {
      notify('error', 'Video must be 20 MB or smaller.');
      return;
    }
    const dt = new DataTransfer();
    dt.items.add(file);
    if (videoInput) videoInput.files = dt.files;
    renderVideo();
  });
})();

window.initVendorProductLocationAutocomplete = function () {
  const locationInput = document.getElementById('location');
  const latitudeInput = document.getElementById('latitude');
  const longitudeInput = document.getElementById('longitude');
  if (!locationInput || !window.google || !google.maps || !google.maps.places) return;

  let selectedPlaceId = '';
  const autocomplete = new google.maps.places.Autocomplete(locationInput, {
    fields: ['formatted_address', 'geometry', 'address_components', 'place_id'],
    componentRestrictions: { country: 'in' }
  });

  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace();
    if (!place?.geometry?.location) {
      latitudeInput.value = '';
      longitudeInput.value = '';
      selectedPlaceId = '';
      return;
    }
    selectedPlaceId = place.place_id || '';
    locationInput.value = place.formatted_address || locationInput.value;
    latitudeInput.value = place.geometry.location.lat().toFixed(7);
    longitudeInput.value = place.geometry.location.lng().toFixed(7);
    if (window.jQuery) {
      jQuery(locationInput).valid();
    }
  });

  locationInput.addEventListener('input', () => {
    if (selectedPlaceId) selectedPlaceId = '';
    latitudeInput.value = '';
    longitudeInput.value = '';
    if (window.jQuery) {
      jQuery(locationInput).valid();
    }
  });
};

$(function () {
  const $form = $('#vendor-product-form');
  if (!$form.length || String($form.data('ajax-create')) !== '1') {
    return;
  }

  const hiddenValidationFields = ['latitude', 'longitude'];
  const $submitBtn = $('#productSubmitBtn');
  let originalBtnHtml = $submitBtn.html();

  $.validator.addMethod('locationPicked', function () {
    return String($('#latitude').val() || '').trim() !== '' && String($('#longitude').val() || '').trim() !== '';
  }, 'Please select a location from the suggestions list.');

  function setSubmitLoading(isLoading) {
    if (isLoading) {
      originalBtnHtml = $submitBtn.html();
      $submitBtn.prop('disabled', true).addClass('is-loading').html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');
      return;
    }
    $submitBtn.prop('disabled', false).removeClass('is-loading').html(originalBtnHtml);
  }

  function applyServerErrors(errors) {
    const validator = $form.data('validator');
    const mapped = {};

    Object.entries(errors || {}).forEach(([field, messages]) => {
      const normalizedField = field.replace(/\.[0-9]+(?=\.|$)/g, '').replace(/\*$/, '');
      const message = Array.isArray(messages) ? messages[0] : String(messages || 'Invalid value');

      if (hiddenValidationFields.includes(normalizedField)) {
        mapped.location = 'Please select a location from the suggestions list.';
        return;
      }

      mapped[normalizedField] = message;
    });

    if (validator && Object.keys(mapped).length) {
      validator.showErrors(mapped);
    }
  }

  $form.validate({
    ignore: [],
    rules: {
      name: { required: true, maxlength: 255 },
      category_id: { required: true },
      subcategory_id: { required: true },
      base_price: { required: true, number: true, min: 0 },
      final_price: { required: true, number: true, min: 0 },
      stock_quantity: { required: true, digits: true, min: 0 },
      shipping_charges: { required: true, number: true, min: 0 },
      location: { required: true, locationPicked: true, maxlength: 255 },
      youtube_link: { url: true },
      discount_percent: { number: true, min: 0, max: 100 },
      accept_terms: { required: {{ $isAdmin ? 'false' : 'true' }} },
      @if($isAdmin)
      vendor_id: { required: true },
      @endif
    },
    messages: {
      name: { required: 'Please enter the product name.' },
      category_id: { required: 'Please select a category.' },
      subcategory_id: { required: 'Please select a subcategory.' },
      base_price: { required: 'Please enter the base price.' },
      final_price: { required: 'Please enter the final price.' },
      stock_quantity: { required: 'Please enter stock quantity.' },
      shipping_charges: { required: 'Please enter shipping charges.' },
      location: { required: 'Please enter a location.' },
      accept_terms: { required: 'Please accept the terms and conditions.' },
      @if($isAdmin)
      vendor_id: { required: 'Please select a vendor.' },
      @endif
    },
    errorElement: 'div',
    errorClass: 'invalid-feedback d-block',
    highlight: function (element) {
      const $element = $(element);
      $element.addClass('is-invalid').removeClass('is-valid');
      $element.closest('.col-12, .col-md-6, .col-md-3, .form-check, .col-lg-4, .col-lg-8')
        .find('.invalid-feedback')
        .removeClass('d-none');
    },
    unhighlight: function (element) {
      const $element = $(element);
      $element.removeClass('is-invalid').addClass('is-valid');
      $element.closest('.col-12, .col-md-6, .col-md-3, .form-check, .col-lg-4, .col-lg-8')
        .find('.invalid-feedback')
        .text('')
        .addClass('d-none');
    },
    errorPlacement: function (error, element) {
      const $container = $(element).closest('.col-12, .col-md-6, .col-md-3, .form-check, .col-lg-4, .col-lg-8');
      const $existing = $container.find('.invalid-feedback').not(error).first();
      if ($existing.length) {
        $existing.text(error.text()).removeClass('d-none');
        error.remove();
        return;
      }
      error.insertAfter(element);
    },
    invalidHandler: function () {
      setSubmitLoading(false);
    },
    submitHandler: function (form) {
      setSubmitLoading(true);

      $.ajax({
        url: form.action,
        method: 'POST',
        data: new FormData(form),
        processData: false,
        contentType: false,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        success: function (payload) {
          notify('success', payload.message || 'Product submitted successfully.');
          setTimeout(function () {
            window.location.href = payload.redirect || '{{ $isAdmin ? route('admin.vendor-products.all.index') : route('vendor.products.index') }}';
          }, 800);
        },
        error: function (xhr) {
          const payload = xhr.responseJSON || {};
          applyServerErrors(payload.errors || {});
          if (xhr.status === 422) {
            notify('error', 'Please fix the highlighted fields and try again.');
            return;
          }
          notify('error', payload.message || 'Unable to save product.');
        },
        complete: function () {
          setSubmitLoading(false);
        }
      });
    }
  });

  $('#category_id, #subcategory_id').on('change', function () {
    $(this).valid();
  });
});

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initVendorProductLocationAutocomplete"></script>
@endpush
