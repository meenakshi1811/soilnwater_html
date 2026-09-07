<p class="sm-results-count">
    Showing {{ $materials->firstItem() ?: 0 }} to {{ $materials->lastItem() ?: 0 }} of {{ number_format($materials->total()) }} notes
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

@if($materials->hasPages())
    <div class="sm-pagination-wrap">
        {{ $materials->onEachSide(1)->links('frontend.study-materials.partials.pagination') }}
    </div>
@endif
