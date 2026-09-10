@php
    $childProfile = $childProfile ?? null;
    $activeNav = $activeNav ?? 'overview';
    $avatarUrl = filled($childProfile?->profile_image) ? asset($childProfile->profile_image) : null;
    $initials = collect(preg_split('/\s+/', trim($childProfile?->full_name ?? '')) ?: [])->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'CH';
    $classBoard = $classBoard ?? collect([$childProfile?->class_grade, $childProfile?->board])->filter()->implode(' • ');

    $navItems = [
        ['key' => 'overview', 'label' => 'Overview', 'icon' => 'fa-border-all', 'url' => route('child.dashboard')],
        ['key' => 'materials', 'label' => 'Study Materials', 'icon' => 'fa-book-open', 'url' => route('study-materials.library')],
        ['key' => 'courses', 'label' => 'Courses & Learning', 'icon' => 'fa-graduation-cap', 'url' => route('frontend.index')],
        ['key' => 'tests', 'label' => 'Tests & Assessments', 'icon' => 'fa-file-circle-check', 'url' => route('frontend.index')],
        ['key' => 'questions', 'label' => 'Questions & Answers', 'icon' => 'fa-circle-question', 'url' => route('community.posts.create')],
        ['key' => 'teachers', 'label' => 'Teachers & Guidance', 'icon' => 'fa-chalkboard-user', 'url' => route('educator.index')],
        ['key' => 'goals', 'label' => 'Goals & Progress', 'icon' => 'fa-bullseye', 'url' => route('child.dashboard')],
        ['key' => 'achievements', 'label' => 'Achievements', 'icon' => 'fa-medal', 'url' => route('child.dashboard')],
        ['key' => 'saved', 'label' => 'Saved & Bookmarks', 'icon' => 'fa-bookmark', 'url' => route('community.saved.index')],
    ];
@endphp

<aside class="child-sidebar">
    <div class="child-sidebar__profile">
        @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="{{ $childProfile->full_name }}" class="child-sidebar__avatar">
        @else
            <div class="child-sidebar__avatar child-sidebar__avatar--placeholder">{{ $initials }}</div>
        @endif
        <div class="child-sidebar__info">
            <strong>{{ $childProfile?->full_name }}</strong>
            @if($childProfile?->is_primary)
                <span class="child-sidebar__badge">Primary Child</span>
            @endif
            <span class="child-sidebar__meta">{{ $classBoard ?: 'Class details pending' }}</span>
        </div>
    </div>

    <nav class="child-sidebar__nav" aria-label="Child portal navigation">
        @foreach($navItems as $item)
            <a href="{{ $item['url'] }}"
               class="child-sidebar__link {{ $activeNav === $item['key'] ? 'is-active' : '' }}"
               @if(!in_array($item['key'], ['overview', 'goals', 'achievements'], true)) target="_blank" @endif>
                <i class="fa-solid {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="child-sidebar__help">
        <strong>Need Help?</strong>
        <p>Get support for your learning journey.</p>
        <a href="{{ route('frontend.index') }}" class="child-btn child-btn--outline child-btn--sm">Contact Support</a>
    </div>
</aside>
