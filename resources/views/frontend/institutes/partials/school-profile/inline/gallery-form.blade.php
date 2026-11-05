@php
  $listingContext = $listingContext ?? 'schools';
  $items = \App\Support\InstituteGallery::entries($institute->gallery);
@endphp
<section>
  <form id="instGalleryForm" class="sch-portal-form mb-4" enctype="multipart/form-data" novalidate
        data-store-url="{{ route($listingContext.'.inline.gallery.add', $institute->slug) }}">
    @csrf
    <div class="alert d-none mb-3" role="alert"></div>
    <label class="form-label">Add photos / videos *</label>
    <input type="file" name="files[]" class="form-control" multiple
           accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime">
    <p class="form-text">Up to {{ \App\Support\InstituteGallery::MAX_NEW_UPLOADS }} files at a time. Photos up to {{ \App\Support\InstituteGallery::imageLimitLabel() }}, videos up to {{ \App\Support\InstituteGallery::videoLimitLabel() }}.</p>
    <button type="submit" class="btn btn-primary js-inst-submit-btn">
      <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Upload</span>
      <span class="btn-loader d-none" aria-hidden="true"></span>
    </button>
  </form>

  <div class="sch-manage-list" data-empty-text="No gallery items yet.">
    @forelse($items as $item)
      @include('frontend.institutes.partials.school-profile.inline.gallery-item', [
        'institute' => $institute,
        'listingContext' => $listingContext,
        'type' => $item['type'],
        'path' => $item['path'],
      ])
    @empty
      <p class="sch-manage-empty mb-0">No gallery items yet.</p>
    @endforelse
  </div>
</section>
