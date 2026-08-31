@php
    $sidebarUser = $sidebarUser ?? \App\Support\AuthActor::user();
    $sidebarIsAdmin = $sidebarIsAdmin ?? ($sidebarUser?->isAdmin() ?? false);
    $moduleSidebarSections = \App\Support\ModuleSidebar::visibleSections($sidebarUser, $sidebarIsAdmin);
@endphp

@foreach($moduleSidebarSections as $section)
    <li class="admin-sidebar-group">
        <details {{ $section['active'] ? 'open' : '' }}>
            <summary class="{{ $section['active'] ? 'active' : '' }} d-flex align-items-center justify-content-between">
                <span class="d-inline-flex align-items-center gap-2">
                    <i class="{{ $section['icon'] }}"></i>
                    <span>{{ $section['label'] }}</span>
                </span>
                <i class="fa-solid fa-chevron-down small"></i>
            </summary>
            <ul class="list-unstyled ps-4">
                @foreach($section['items'] as $item)
                    <li>
                        <a class="{{ $item['active'] ? 'active' : '' }}" href="{{ route($item['route']) }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
    </li>
@endforeach
