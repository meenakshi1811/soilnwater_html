@php
    $post = $post ?? null;
    if (! $post?->isYouthCornerPost()) {
        return;
    }

    $achievements = collect($post->youthCornerAchievements())
        ->filter(fn (array $achievement): bool => filled(data_get($achievement, 'achievement_title'))
            || filled(data_get($achievement, 'title'))
            || filled(data_get($achievement, 'certificate.url')))
        ->values()
        ->all();
@endphp

@if($achievements !== [])
    <div class="community-news-sidebar__card community-news-sidebar__card--youth-achievements">
        <div class="yc-sidebar-achievements__head">
            <span class="yc-sidebar-achievements__icon" aria-hidden="true">
                <i class="fa-solid fa-award"></i>
            </span>
            <div>
                <p class="community-news-sidebar__label mb-0">Achievements &amp; certificates</p>
                <p class="yc-sidebar-achievements__lead mb-0">{{ count($achievements) }} {{ Str::plural('record', count($achievements)) }}</p>
            </div>
        </div>

        <ul class="yc-sidebar-achievements__list">
            @foreach($achievements as $achievement)
                @php
                    $title = data_get($achievement, 'achievement_title', data_get($achievement, 'title', 'Achievement'));
                    $year = data_get($achievement, 'year');
                    $certificateUrl = data_get($achievement, 'certificate.url');
                    $certificateName = data_get($achievement, 'certificate.name', 'View certificate');
                @endphp
                <li class="yc-sidebar-achievement">
                    <div class="yc-sidebar-achievement__badge" aria-hidden="true">
                        <i class="fa-solid fa-medal"></i>
                    </div>
                    <div class="yc-sidebar-achievement__body">
                        <strong class="yc-sidebar-achievement__title">{{ $title }}</strong>
                        @if(filled($year))
                            <span class="yc-sidebar-achievement__year">{{ $year }}</span>
                        @endif
                        @if(filled($certificateUrl))
                            <a href="{{ $certificateUrl }}" target="_blank" rel="noopener" class="yc-sidebar-achievement__certificate">
                                <i class="fa-solid fa-file-certificate" aria-hidden="true"></i>
                                <span>{{ $certificateName }}</span>
                                <i class="fa-solid fa-arrow-up-right-from-square yc-sidebar-achievement__certificate-arrow" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
