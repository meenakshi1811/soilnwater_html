@php
    $avatarUrl = $avatarUrl ?? null;
    $initials = $initials ?? 'CA';
    $sizeClass = $sizeClass ?? '';
    $alt = $alt ?? 'Author photo';
@endphp
@if ($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="{{ $alt }}" class="community-author-avatar community-author-avatar--image {{ $sizeClass }}" loading="lazy">
@else
    <span class="community-author-avatar {{ $sizeClass }}" aria-hidden="true">{{ $initials }}</span>
@endif
