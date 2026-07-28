@php
    $prefix = $prefix ?? 'discussionWidget';
    $pickerId = "{$prefix}GroupPick";
    $selectedId = "{$prefix}GroupSelected";
    $searchId = "{$prefix}GroupSearch";
    $listId = "{$prefix}GroupList";
    $nextId = "{$prefix}GroupNext";
    $loadingId = "{$prefix}GroupLoading";
@endphp

<section class="discussion-group-pick" id="{{ $pickerId }}" data-prefix="{{ $prefix }}">
    <div class="discussion-group-pick__selected-wrap">
        <div class="discussion-group-pick__selected" id="{{ $selectedId }}" hidden></div>
    </div>
    <div class="discussion-group-pick__search-bar">
        <div class="discussion-group-pick__search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search"
                   id="{{ $searchId }}"
                   class="discussion-group-pick__search"
                   placeholder="Search name or email"
                   autocomplete="off"
                   aria-label="Search contacts">
        </div>
    </div>
    <div class="discussion-group-pick__scroll">
        <div class="discussion-group-pick__loading" id="{{ $loadingId }}">
            <span class="discussion-widget__spinner" aria-hidden="true"></span>
            <span>Loading contacts…</span>
        </div>
        <div class="discussion-group-pick__list" id="{{ $listId }}" role="listbox" aria-label="Select group members"></div>
    </div>
    <button type="button"
            class="discussion-group-pick__next"
            id="{{ $nextId }}"
            hidden
            aria-label="Continue to group details">
        <i class="fa-solid fa-arrow-right"></i>
    </button>
</section>
