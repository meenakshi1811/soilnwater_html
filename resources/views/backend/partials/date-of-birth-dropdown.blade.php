@php
    $prefix = $prefix ?? 'child';
    $required = $required ?? true;
    $maxYear = now()->year;
    $minYear = now()->year - 25;
    $selectedDay = old($prefix.'_dob_day');
    $selectedMonth = old($prefix.'_dob_month');
    $selectedYear = old($prefix.'_dob_year');
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
@endphp

<div class="date-of-birth-dropdown">
    <label class="form-label">
        Date of birth
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    <div class="row g-2">
        <div class="col-4">
            <select class="form-select js-dob-day" id="{{ $prefix }}_dob_day" name="dob_day" @if($required) required @endif aria-label="Day">
                <option value="">Day</option>
                @for($day = 1; $day <= 31; $day++)
                    <option value="{{ $day }}" @selected((string) $selectedDay === (string) $day)>{{ $day }}</option>
                @endfor
            </select>
        </div>
        <div class="col-4">
            <select class="form-select js-dob-month" id="{{ $prefix }}_dob_month" name="dob_month" @if($required) required @endif aria-label="Month">
                <option value="">Month</option>
                @foreach($months as $monthNumber => $monthLabel)
                    <option value="{{ $monthNumber }}" @selected((string) $selectedMonth === (string) $monthNumber)>{{ $monthLabel }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4">
            <select class="form-select js-dob-year" id="{{ $prefix }}_dob_year" name="dob_year" @if($required) required @endif aria-label="Year">
                <option value="">Year</option>
                @for($year = $maxYear; $year >= $minYear; $year--)
                    <option value="{{ $year }}" @selected((string) $selectedYear === (string) $year)>{{ $year }}</option>
                @endfor
            </select>
        </div>
    </div>
    <input type="hidden" class="js-dob-combined" name="date_of_birth" value="{{ old('date_of_birth') }}">
</div>
