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
    <div class="col-lg-4"><div class="chart-card p-4"><h5 class="mb-3">Media & Listing</h5><label class="form-label">Product Images</label><input class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" type="file" name="images[]" multiple accept="image/*">@error('images.*')<div id="images-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Max 4 MB per image.</small><label class="form-label mt-3">Product Video (MP4/WEBM)</label><input class="form-control @error('video_file') is-invalid @enderror" type="file" name="video_file" accept="video/mp4,video/webm">@error('video_file')<div id="video_file-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="text-muted">Max 20 MB.</small><label class="form-label mt-3">YouTube Link</label><input class="form-control @error('youtube_link') is-invalid @enderror" type="url" name="youtube_link" value="{{ old('youtube_link',$product->youtube_link) }}">@error('youtube_link')<div id="youtube_link-error" class="invalid-feedback d-block">{{ $message }}</div>@enderror<div class="form-check mt-3"><input class="form-check-input" type="checkbox" value="1" name="is_online_sale" @checked(old('is_online_sale',$product->is_online_sale))><label class="form-check-label">List for Online Sale</label></div></div></div>
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
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script>
(function initToastr() {
  if (!window.jQuery) {
    console.warn('jQuery is required for toastr but was not found.');
    return;
  }

  const configureToastr = function () {
    if (!window.toastr) return;
    window.toastr.options = {
      closeButton: true,
      progressBar: true,
      positionClass: 'toast-top-right',
      timeOut: 4000,
      extendedTimeOut: 2000
    };
  };

  if (window.toastr) {
    configureToastr();
    return;
  }

  const toastrScript = document.createElement('script');
  toastrScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js';
  toastrScript.onload = configureToastr;
  document.head.appendChild(toastrScript);
})();
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

function notify(type, msg) {
  const toastType = type === 'error' ? 'error' : 'success';
  if (window.toastr && typeof window.toastr[toastType] === 'function') {
    window.toastr[toastType](msg);
    return;
  }
  if (type === 'error') {
    alert(msg);
    return;
  }
  alert(msg);
}

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
      $submitBtn.prop('disabled', true).html('Saving...');
      return;
    }
    $submitBtn.prop('disabled', false).html(originalBtnHtml);
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

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new FormData(form)
      })
        .then(async (res) => {
          const payload = await res.json();
          if (!res.ok) {
            applyServerErrors(payload.errors || {});
            if (res.status === 422) {
              notify('error', 'Please fix the highlighted fields and try again.');
            } else {
              notify('error', payload.message || 'Unable to save product.');
            }
            return;
          }

          notify('success', payload.message || 'Product submitted successfully.');
          setTimeout(() => {
            window.location.href = payload.redirect || '{{ $isAdmin ? route('admin.vendor-products.all.index') : route('vendor.products.index') }}';
          }, 800);
        })
        .catch(() => {
          notify('error', 'Network error while saving product.');
        })
        .finally(() => {
          setSubmitLoading(false);
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
