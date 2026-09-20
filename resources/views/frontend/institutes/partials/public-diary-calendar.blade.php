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
<div class="inst-diary-calendar">
  <div class="inst-diary-calendar__head d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="h6 mb-0">{{ $monthStart->format('F Y') }}</h3>
    <div class="btn-group btn-group-sm">
      <a class="btn btn-outline-secondary" href="{{ $diaryUrl.'?'.http_build_query(array_merge($baseQuery, ['calendar_month' => $prev])) }}">&larr;</a>
      <a class="btn btn-outline-secondary" href="{{ $diaryUrl.'?'.http_build_query(array_merge($baseQuery, ['calendar_month' => $monthStart->format('Y-m')])) }}">Today</a>
      <a class="btn btn-outline-secondary" href="{{ $diaryUrl.'?'.http_build_query(array_merge($baseQuery, ['calendar_month' => $next])) }}">&rarr;</a>
    </div>
  </div>
  <div class="inst-diary-calendar__grid">
    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $weekday)
      <div class="inst-diary-calendar__weekday">{{ $weekday }}</div>
    @endforeach
    @for($day = $gridStart->copy(); $day->lte($gridEnd); $day->addDay())
      @php
          $key = $day->format('Y-m-d');
          $inMonth = $day->month === $monthStart->month;
          $marks = $holidayDays[$key] ?? [];
      @endphp
      <div class="inst-diary-calendar__cell {{ $inMonth ? '' : 'is-outside' }} {{ count($marks) ? 'has-holiday' : '' }}" title="{{ implode(', ', $marks) }}">
        <span class="inst-diary-calendar__date">{{ $day->format('j') }}</span>
        @if(count($marks))
          <span class="inst-diary-calendar__dot" aria-hidden="true"></span>
        @endif
      </div>
    @endfor
  </div>
</div>
