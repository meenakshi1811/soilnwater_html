<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\InstituteScholarship;
use App\Support\ManagedInstitute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * A school's, college's or institute's scholarships in its dashboard (and the admin's, managing
 * one): list, add, edit, open / close and delete. Each shows on its profile and in the Education Hub.
 */
class InstituteScholarshipController extends Controller
{
    public function index(): View
    {
        $institute = ManagedInstitute::current();
        abort_unless($institute, 404);

        return view('backend.institute.scholarships.index', [
            'institute' => $institute,
            'scholarships' => $institute->scholarships()->paginate(15),
            'types' => InstituteScholarship::TYPES,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $institute = ManagedInstitute::current();
        abort_unless($institute, 404);

        $validated = $request->validate($this->rules(true), $this->messages());

        $scholarship = $institute->scholarships()->create([
            ...$validated,
            'status' => $validated['status'] ?? InstituteScholarship::STATUS_OPEN,
            'published_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Scholarship published.',
            'html' => view('backend.institute.partials.scholarship-item', compact('scholarship'))->render(),
        ]);
    }

    public function update(Request $request, InstituteScholarship $scholarship): JsonResponse
    {
        $this->authorizeScholarship($scholarship);

        // (just opening or closing it, from the list)
        if ($request->keys() === ['status'] || array_diff($request->keys(), ['status', '_token', '_method']) === []) {
            $validated = $request->validate(['status' => ['required', Rule::in([InstituteScholarship::STATUS_OPEN, InstituteScholarship::STATUS_CLOSED])]]);
        } else {
            $validated = $request->validate($this->rules(false), $this->messages());
        }

        $scholarship->update($validated);

        return response()->json([
            'ok' => true,
            'message' => isset($validated['title']) ? 'Scholarship updated.' : ($scholarship->status === 'open' ? 'Scholarship reopened.' : 'Scholarship closed.'),
            'html' => view('backend.institute.partials.scholarship-item', compact('scholarship'))->render(),
        ]);
    }

    public function destroy(InstituteScholarship $scholarship): JsonResponse
    {
        $this->authorizeScholarship($scholarship);
        $scholarship->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Scholarship deleted.',
        ]);
    }

    /** @return array<string, mixed> */
    private function rules(bool $creating): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'scholarship_type' => ['required', 'string', Rule::in(array_keys(InstituteScholarship::TYPES))],
            'amount_label' => ['nullable', 'string', 'max:120'],
            'awards_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'eligible_for' => ['nullable', 'string', 'max:160'],
            'min_marks' => ['nullable', 'string', 'max:120'],
            'income_limit' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'eligibility' => ['required', 'string', 'max:5000'],
            'documents' => ['nullable', 'string', 'max:5000'],
            'how_to_apply' => ['nullable', 'string', 'max:5000'],
            'apply_url' => ['nullable', 'url:http,https', 'max:500'],
            'application_start' => ['nullable', 'date'],
            'application_deadline' => array_values(array_filter([
                'nullable',
                'date',
                $creating ? 'after_or_equal:today' : null,
                'after_or_equal:application_start',
            ])),
            'status' => ['nullable', Rule::in([InstituteScholarship::STATUS_OPEN, InstituteScholarship::STATUS_CLOSED])],
        ];
    }

    /** @return array<string, string> */
    private function messages(): array
    {
        return [
            'title.required' => 'Give the scholarship a name.',
            'eligibility.required' => 'Write who can apply (the eligibility criteria).',
            'apply_url.url' => 'The application link must be a web address starting with http:// or https://.',
            'application_deadline.after_or_equal' => 'The last date must be today or later, and not before the start date.',
            'awards_count.integer' => 'The number of scholarships must be a whole number.',
        ];
    }

    private function authorizeScholarship(InstituteScholarship $scholarship): void
    {
        $institute = ManagedInstitute::current();
        abort_unless($institute && (int) $scholarship->institute_id === (int) $institute->id, 404);
    }
}
