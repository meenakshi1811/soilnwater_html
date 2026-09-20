@php
    $monthStart = $calendarStart->copy()->startOfMonth();
    $monthEnd = $calendarStart->copy()->endOfMonth();
    $gridStart = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $gridEnd = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);

    $holidayDays = [];
    foreach ($holidays as $holiday) {
        $cursor = $holiday->start_date->copy();
        $end = $holiday->end_date->copy();
        while ($cursor->lte($end)) {
            if ($cursor->between($monthStart, $monthEnd)) {
                $holidayDays[$cursor->format('Y-m-d')][] = $holiday->name;
            }
            $cursor->addDay();
        }
    }

    $prev = $monthStart->copy()->subMonth()->format('Y-m');
    $next = $monthStart->copy()->addMonth()->format('Y-m');
    $baseQuery = array_filter([
        'academic_year' => $academicYear ?? null,
    ]);
@endphp
<div class="sch-diary-calendar">
  <div class="sch-diary-calendar__head">
    <h3 class="sch-diary-calendar__month">{{ $monthStart->format('F Y') }}</h3>
    <div class="sch-diary-calendar__nav">
      <a class="sch-diary-calendar__nav-btn" href="{{ $diaryUrl.'?'.http_build_query(array_merge($baseQuery, ['calendar_month' => $prev])) }}" aria-label="Previous month">&larr;</a>
      <a class="sch-diary-calendar__nav-btn" href="{{ $diaryUrl.'?'.http_build_query(array_merge($baseQuery, ['calendar_month' => $monthStart->format('Y-m')])) }}">Today</a>
      <a class="sch-diary-calendar__nav-btn" href="{{ $diaryUrl.'?'.http_build_query(array_merge($baseQuery, ['calendar_month' => $next])) }}" aria-label="Next month">&rarr;</a>
    </div>
  </div>
  <div class="sch-diary-calendar__grid">
    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $weekday)
      <div class="sch-diary-calendar__weekday">{{ $weekday }}</div>
    @endforeach
    @for($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay())
      @php
          $key = $day->format('Y-m-d');
          $inMonth = $day->month === $monthStart->month;
          $marks = $holidayDays[$key] ?? [];
          $isToday = $day->isToday();
      @endphp
      <div
        class="sch-diary-calendar__cell {{ $inMonth ? '' : 'is-outside' }} {{ count($marks) ? 'has-holiday' : '' }} {{ $isToday ? 'is-today' : '' }}"
        @if(count($marks)) title="{{ implode(', ', $marks) }}" @endif
      >
        <span class="sch-diary-calendar__date">{{ $day->format('j') }}</span>
        @if(count($marks))
          <span class="sch-diary-calendar__dots" aria-hidden="true">
            @for($i = 0; $i < min(3, count($marks)); $i++)
              <span class="sch-diary-calendar__dot"></span>
            @endfor
          </span>
        @endif
      </div>
    @endfor
  </div>
  <p class="sch-diary-calendar__hint">Green dates indicate scheduled holidays for the selected academic year.</p>
</div>
