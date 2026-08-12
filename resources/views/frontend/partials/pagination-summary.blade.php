@php($paginator = $paginator ?? null)

@if($paginator && $paginator->total() > 0)
    <p class="frontend-pagination-summary text-muted small mb-3">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </p>
@endif
