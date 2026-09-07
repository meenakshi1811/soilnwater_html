@foreach($categoryTabs as $tab)
    @php
        $tabQuery = $queryWithoutPage;
        if ($tab['value']) {
            $tabQuery['category'] = $tab['value'];
        } else {
            unset($tabQuery['category']);
        }
        $isActive = ($tab['value'] === null && empty($filters['category'])) || ($filters['category'] ?? null) === $tab['value'];
    @endphp
    <a
        href="{{ route('study-materials.notes', $tabQuery) }}"
        class="sm-category-tab js-sm-notes-tab {{ $isActive ? 'is-active' : '' }}"
        data-category="{{ $tab['value'] ?? '' }}"
    >{{ $tab['label'] }}</a>
@endforeach
