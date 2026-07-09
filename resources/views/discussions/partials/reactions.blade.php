@php
    use App\Support\DiscussionReactions;
    $reactionIcons = [
        'Like' => 'fa-thumbs-up',
        'Love' => 'fa-heart',
        'Insightful' => 'fa-lightbulb',
        'Agree' => 'fa-check',
    ];
@endphp

<div class="discussion-reactions d-flex flex-wrap gap-2"
     data-reactable-type="{{ $reactableType }}"
     data-reactable-id="{{ $reactableId }}"
     data-react-url="{{ $reactUrl }}">
    @foreach(DiscussionReactions::labels() as $label)
        @php
            $count = $counts[$label] ?? 0;
            $isActive = in_array($label, $userReactions, true);
        @endphp
        <button type="button"
                class="btn btn-sm discussion-reaction-btn {{ $isActive ? 'btn-success' : 'btn-outline-secondary' }}"
                data-reaction="{{ $label }}"
                data-active="{{ $isActive ? '1' : '0' }}"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}">
            <i class="fa-solid {{ $reactionIcons[$label] ?? 'fa-face-smile' }} me-1"></i>
            <span class="discussion-reaction-label">{{ $label }}</span>
            <span class="discussion-reaction-count">{{ $count > 0 ? $count : '' }}</span>
        </button>
    @endforeach
</div>
