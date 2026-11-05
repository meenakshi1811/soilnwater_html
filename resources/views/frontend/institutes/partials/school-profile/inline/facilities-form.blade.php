@php
  $listingContext = $listingContext ?? 'schools';
  $facilities = array_values(array_filter((array) ($institute->facilities ?? []), fn ($n) => is_string($n) && trim($n) !== ''));
  $images = (array) ($institute->facility_images ?? []);
@endphp
<section>
  <form id="instFacilityForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate
        data-store-url="{{ route($listingContext.'.inline.facilities.add', $institute->slug) }}">
    @csrf
    <div class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-7">
        <label class="form-label">Facility name *</label>
        <input type="text" name="name" class="form-control" maxlength="120" placeholder="e.g. Science Lab, Library, Playground">
      </div>
      <div class="col-md-5">
        <label class="form-label">Photo *</label>
        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
        <p class="form-text mb-0">JPG, PNG or WebP, up to 2 MB.</p>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary js-inst-submit-btn">
          <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Add facility</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>

  <div class="sch-manage-list" data-empty-text="No facilities added yet.">
    @forelse($facilities as $name)
      @include('frontend.institutes.partials.school-profile.inline.facility-item', [
        'institute' => $institute,
        'listingContext' => $listingContext,
        'name' => $name,
        'image' => $images[$name] ?? null,
      ])
    @empty
      <p class="sch-manage-empty mb-0">No facilities added yet.</p>
    @endforelse
  </div>
</section>
