<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\InstituteDiaryHoliday;
use App\Support\InstituteDiaryConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstituteDiaryHolidayController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute, 403);

        $validated = $this->validateHoliday($request);
        $holiday = $institute->diaryHolidays()->create($validated);

        return response()->json([
            'message' => 'Holiday added successfully.',
            'item_html' => view('backend.institute.partials.diary-holiday-item', compact('holiday'))->render(),
            'id' => $holiday->id,
        ]);
    }

    public function update(Request $request, InstituteDiaryHoliday $holiday): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute && (int) $holiday->institute_id === (int) $institute->id, 403);

        $validated = $this->validateHoliday($request);
        $holiday->update($validated);

        return response()->json([
            'message' => 'Holiday updated successfully.',
            'item_html' => view('backend.institute.partials.diary-holiday-item', compact('holiday'))->render(),
            'id' => $holiday->id,
        ]);
    }

    public function destroy(Request $request, InstituteDiaryHoliday $holiday): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute && (int) $holiday->institute_id === (int) $institute->id, 403);

        $holidayId = $holiday->id;
        $holiday->delete();

        return response()->json([
            'message' => 'Holiday removed successfully.',
            'id' => $holidayId,
        ]);
    }

    /** @return array<string, mixed> */
    private function validateHoliday(Request $request): array
    {
        $types = array_keys(InstituteDiaryConfig::holidayTypes());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'holiday_type' => ['required', 'string', Rule::in($types)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'academic_year' => ['required', 'string', 'max:20'],
            'is_recurring' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
