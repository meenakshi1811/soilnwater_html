@php
  use App\Support\InstituteGrades;

  /** @var \App\Models\Institute $institute */
  $gradeEntries = $institute->gradesOfferedEntries();
  $gradeStages = InstituteGrades::byStage($gradeEntries);
  $gradeTotals = InstituteGrades::totals($gradeEntries);
  // (no stage names when nothing could be placed in one, e.g. a college's courses)
  $showStageNames = ! (count($gradeStages) === 1 && $gradeStages[0]['key'] === 'other');
@endphp

<div id="sch-classes-detail" class="sch-classes-detail">
  <div class="sch-grades__head">
    <h3 class="sch-classes-detail__title mb-0">Classes offered</h3>
    <ul class="sch-grades__totals">
      <li><strong>{{ number_format($gradeTotals['classes']) }}</strong> {{ \Illuminate\Support\Str::plural('class', $gradeTotals['classes']) }}</li>
      @if($gradeTotals['sections'] !== null)
        <li><strong>{{ number_format($gradeTotals['sections']) }}</strong> {{ \Illuminate\Support\Str::plural('section', $gradeTotals['sections']) }}</li>
      @endif
      @if($gradeTotals['students'] !== null)
        <li><strong>{{ number_format($gradeTotals['students']) }}</strong> students</li>
      @endif
    </ul>
  </div>

  <div class="sch-grades {{ $showStageNames ? '' : 'sch-grades--plain' }}">
    @foreach($gradeStages as $stage)
      <div class="sch-grades__stage sch-grades__stage--{{ $stage['key'] }}">
        @if($showStageNames)
          <p class="sch-grades__stage-name">
            {{ $stage['label'] }}
            <span>{{ count($stage['entries']) }} {{ \Illuminate\Support\Str::plural('class', count($stage['entries'])) }}</span>
          </p>
        @endif
        <ul class="sch-grades__list">
          @foreach($stage['entries'] as $entry)
            <li class="sch-grade">
              <span class="sch-grade__name">{{ $entry['class'] }}</span>
              @if($entry['total'] !== null)
                <span class="sch-grade__total">{{ number_format($entry['total']) }} students</span>
              @endif
              @if($entry['sections'] !== null || $entry['students_per_section'] !== null)
                <span class="sch-grade__meta">
                  @if($entry['sections'] !== null)
                    {{ $entry['sections'] }} {{ \Illuminate\Support\Str::plural('section', $entry['sections']) }}
                  @endif
                  @if($entry['sections'] !== null && $entry['students_per_section'] !== null)
                    · {{ $entry['students_per_section'] }} each
                  @elseif($entry['students_per_section'] !== null)
                    {{ $entry['students_per_section'] }} per section
                  @endif
                </span>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </div>
</div>
