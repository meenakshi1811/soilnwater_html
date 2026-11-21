@forelse ($vendors as $vendor)
    @include('frontend.vendors.partials.compact-card', ['vendor' => $vendor, 'hasLocation' => $hasLocation ?? false])
@empty
    <div class="vendors-empty-state">
        <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-store"></i></div>
        <h3>No vendors found</h3>
        <p>Try adjusting your filters or search term.</p>
    </div>
@endforelse
