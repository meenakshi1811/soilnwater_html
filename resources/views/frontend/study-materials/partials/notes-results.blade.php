<p class="sm-results-count">
    Showing {{ $materials->firstItem() ?: 0 }} to <span class="js-sm-results-last">{{ $materials->lastItem() ?: 0 }}</span> of <span class="js-sm-results-total">{{ number_format($materials->total()) }}</span> notes
</p>

@if($viewMode === 'grid')
    <div class="sm-grid-cards">
        @forelse($materials as $item)
            @include('frontend.study-materials.partials.note-grid-item', ['item' => $item])
        @empty
            <p class="sm-empty">No notes found for the selected filters.</p>
        @endforelse
    </div>
@else
    <div class="sm-note-list">
        @forelse($materials as $item)
            @include('frontend.study-materials.partials.note-list-item', ['item' => $item])
        @empty
            <p class="sm-empty">No notes found for the selected filters.</p>
        @endforelse
    </div>
@endif

{{-- The next page loads on scroll (study-materials-notes.js); the link is the fallback without it --}}
@if($materials->hasMorePages())
    <div class="sm-infinite js-sm-notes-more">
        <span class="sm-infinite__status" role="status">
            <span class="sm-infinite__spinner" aria-hidden="true"></span>
            Loading more notes...
        </span>
        <a href="{{ $materials->nextPageUrl() }}" class="sm-infinite__more js-sm-notes-more-link" rel="next">
            <span class="sm-infinite__error">Couldn't load more notes.</span>
            Load more notes
        </a>
    </div>
@elseif($materials->lastPage() > 1)
    <p class="sm-infinite sm-infinite--end">You've seen all {{ number_format($materials->total()) }} notes</p>
@endif
