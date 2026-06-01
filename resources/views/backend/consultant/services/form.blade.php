@extends('backend.layouts.app')
@section('title', $service->exists ? 'Edit Consultation Service' : 'Create Consultation Service')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="ems-kicker mb-1">Consultant Portal</p><h2 class="admin-title mb-0">{{ $service->exists ? 'Edit Consultation Service' : 'Add Consultation Service' }}</h2></div><a href="{{ route('consultant.services.index') }}" class="btn btn-outline-secondary">Back to Listing</a></div>
  @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form method="POST" enctype="multipart/form-data" action="{{ $service->exists ? route('consultant.services.update', $service) : route('consultant.services.store') }}" class="row g-3">@csrf @if($service->exists) @method('PUT') @endif
    <div class="col-lg-8"><div class="chart-card p-4"><h5 class="mb-3">Service Information</h5><div class="row g-3">
      <div class="col-12"><label class="form-label">Service Name *</label><input class="form-control" name="name" value="{{ old('name', $service->name) }}" required></div>
      <div class="col-md-6"><label class="form-label">Category *</label><select id="category_id" class="form-select" name="category_id" required><option value="">Select category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id', $service->category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label">Subcategory</label><select id="subcategory_id" class="form-select" name="subcategory_id" data-current="{{ old('subcategory_id', $service->subcategory_id) }}"><option value="">Select subcategory</option></select></div>
      <div class="col-md-6"><label class="form-label">Price *</label><input class="form-control" type="number" step="0.01" min="0" name="price" value="{{ old('price', $service->price ?? 0) }}" required></div>
      <div class="col-md-6"><label class="form-label">Duration</label><input class="form-control" name="duration" placeholder="30 minutes / 1 hour" value="{{ old('duration', $service->duration) }}"></div>
      <div class="col-12"><label class="form-label">Short Description</label><input class="form-control" name="short_description" maxlength="500" value="{{ old('short_description', $service->short_description) }}"></div>
      <div class="col-12"><label class="form-label">Full Description</label><textarea class="form-control" rows="5" name="description">{{ old('description', $service->description) }}</textarea></div>
    </div></div></div>
    <div class="col-lg-4"><div class="chart-card p-4"><h5 class="mb-3">Media & Availability</h5>
      <label class="form-label">Service Images</label><input class="form-control" type="file" name="images[]" multiple accept="image/*"><small class="text-muted">Max 4 MB per image. Uploading new images replaces existing images.</small>
      @if($service->images)<div class="d-flex flex-wrap gap-2 mt-2">@foreach($service->images as $image)<img src="{{ asset($image) }}" alt="{{ $service->name }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px;">@endforeach</div>@endif
      <label class="form-label mt-3">Location</label><input class="form-control" name="location" value="{{ old('location', $service->location) }}">
      <div class="form-check mt-3"><input class="form-check-input" type="checkbox" value="1" id="is_online" name="is_online" @checked(old('is_online', $service->is_online))><label class="form-check-label" for="is_online">Available for online consultation</label></div>
      @unless($service->exists)<div class="form-check mt-3"><input class="form-check-input" type="checkbox" value="1" id="accept_terms" name="accept_terms" required><label class="form-check-label" for="accept_terms">I confirm this service is ready for admin review.</label></div>@endunless
      <button type="submit" class="btn btn-primary ems-btn-primary w-100 mt-4">{{ $service->exists ? 'Update & Resubmit' : 'Submit for Approval' }}</button>
    </div></div>
  </form>
</div>
@endsection
@push('scripts')
<script>
const categories = @json($categories->mapWithKeys(fn($cat) => [$cat->id => $cat->children->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->values()])->toArray());
function fillSubcategories() {
  const category = document.getElementById('category_id');
  const subcategory = document.getElementById('subcategory_id');
  const current = subcategory.dataset.current;
  subcategory.innerHTML = '<option value="">Select subcategory</option>';
  (categories[category.value] || []).forEach(function (item) {
    const option = document.createElement('option'); option.value = item.id; option.textContent = item.name; option.selected = String(item.id) === String(current); subcategory.appendChild(option);
  });
}
document.getElementById('category_id')?.addEventListener('change', function () { document.getElementById('subcategory_id').dataset.current = ''; fillSubcategories(); });
fillSubcategories();
</script>
@endpush
