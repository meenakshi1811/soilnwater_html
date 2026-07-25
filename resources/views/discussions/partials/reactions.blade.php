@php
    use App\Support\DiscussionReactions;
    $reactionIcons = DiscussionReactions::icons();
@endphp

<div class="discussion-reactions d-flex flex-wrap gap-2"
     data-reactable-type="{{ $reactableType }}"
     data-reactable-id="{{ $reactableId }}"
     data-react-url="{{ $reactUrl }}">
    @foreach(DiscussionReactions::labels() as $label)
        @php
            $count = $counts[$label] ?? 0;
            $isActive = in_array($label, $userReactions, true);
            $icon = $reactionIcons[$label] ?? 'fa-face-smile';
        @endphp
        <button type="button"
                class="btn btn-sm discussion-reaction-btn discussion-reaction-icon-btn {{ $isActive ? 'btn-success' : 'btn-outline-secondary' }}"
                data-reaction="{{ $label }}"
                data-active="{{ $isActive ? '1' : '0' }}"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                title="{{ $label }}"
                aria-label="{{ $label }}{{ $count > 0 ? ' ('.$count.')' : '' }}">
            <i class="fa-solid {{ $icon }}"></i>
            @if($count > 0)
                <span class="discussion-reaction-count">{{ $count }}</span>
            @endif
        </button>
    @endforeach
</div>
