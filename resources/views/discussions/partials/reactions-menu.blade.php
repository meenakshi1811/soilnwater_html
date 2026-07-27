@php
    use App\Support\DiscussionReactions;
    $reactionIcons = DiscussionReactions::icons();
    $counts = $counts ?? [];
    $userReactions = $userReactions ?? [];
    $hasSummary = collect($counts)->filter(fn ($c) => $c > 0)->isNotEmpty();
@endphp

<div class="discussion-msg__actions">
    <div class="discussion-msg__menu">
        <button type="button"
                class="discussion-msg__menu-btn"
                aria-label="Message actions"
                aria-expanded="false">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <div class="discussion-msg__menu-panel" hidden>
            <p class="discussion-msg__menu-title">React to message</p>
            <div class="discussion-reactions discussion-reactions--menu"
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
                            class="discussion-reaction-btn discussion-reaction-menu-item {{ $isActive ? 'is-active' : '' }}"
                            data-reaction="{{ $label }}"
                            data-active="{{ $isActive ? '1' : '0' }}"
                            aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                        <i class="fa-solid {{ $icon }}"></i>
                        <span>{{ $label }}</span>
                        @if($count > 0)
                            <span class="discussion-reaction-count">{{ $count }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
<div class="discussion-msg__reaction-summary{{ $hasSummary ? '' : ' is-empty' }}">
    @foreach(DiscussionReactions::labels() as $label)
        @php $count = $counts[$label] ?? 0; @endphp
        @if($count > 0)
            <span class="discussion-msg__reaction-chip {{ in_array($label, $userReactions, true) ? 'is-mine' : '' }}" title="{{ $label }}">
                <i class="fa-solid {{ $reactionIcons[$label] ?? 'fa-face-smile' }}"></i>
                <span>{{ $count }}</span>
            </span>
        @endif
    @endforeach
</div>
