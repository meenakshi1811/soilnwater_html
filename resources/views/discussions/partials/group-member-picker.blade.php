@php
    $prefix = $prefix ?? 'discussionWidget';
    $selectedId = "{$prefix}GroupSelected";
    $searchId = "{$prefix}GroupSearch";
    $listId = "{$prefix}GroupList";
    $nextId = "{$prefix}GroupNext";
    $footerId = "{$prefix}GroupFooter";
    $countId = "{$prefix}GroupCount";
    $loadingId = "{$prefix}GroupLoading";
@endphp

<section class="discussion-group-pick" data-prefix="{{ $prefix }}">
    <div class="discussion-group-pick__selected-wrap">
        <div class="discussion-group-pick__selected" id="{{ $selectedId }}" hidden></div>
    </div>
    <div class="discussion-group-pick__search-bar">
        <div class="discussion-group-pick__search-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search"
                   id="{{ $searchId }}"
                   class="discussion-group-pick__search"
                   placeholder="Enter mobile number"
                   inputmode="numeric"
                   autocomplete="off"
                   aria-label="Search by mobile number">
        </div>
    </div>
    <div class="discussion-group-pick__scroll">
        <div class="discussion-group-pick__loading" id="{{ $loadingId }}" hidden>
            <span class="discussion-widget__spinner" aria-hidden="true"></span>
            <span>Searching…</span>
        </div>
        <div class="discussion-group-pick__list" id="{{ $listId }}" role="listbox" aria-label="Select group members">
            <div class="discussion-group-pick__empty">Enter a mobile number to find a member.</div>
        </div>
    </div>
    <div class="discussion-group-pick__footer" id="{{ $footerId }}" hidden>
        <span class="discussion-group-pick__footer-count" id="{{ $countId }}"></span>
        <button type="button"
                class="discussion-group-pick__next"
                id="{{ $nextId }}"
                aria-label="Continue to group details">
            <span class="discussion-group-pick__next-label">Next</span>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </button>
    </div>
</section>
