<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\EducatorInstituteAffiliation;
use App\Models\Institute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducatorInstituteAffiliationController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        $institutes = Institute::query()
            ->approved()
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($inner) use ($query): void {
                    $inner->where('institution_name', 'like', '%'.$query.'%')
                        ->orWhere('display_name', 'like', '%'.$query.'%')
                        ->orWhere('city', 'like', '%'.$query.'%');
                });
            })
            ->with('user:id,role')
            ->orderBy('institution_name')
            ->limit(12)
            ->get()
            ->map(fn (Institute $institute): array => [
                'id' => $institute->id,
                'name' => $institute->displayName(),
                'type' => $institute->user?->isSchool() ? 'School' : 'Institute',
                'city' => $institute->city,
                'url' => $institute->publicUrl(),
            ])
            ->values();

        return response()->json([
            'ok' => true,
            'results' => $institutes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $educator = $request->user()->educator;
        abort_unless($educator?->isApproved(), 403);

        $validated = $request->validate([
            'institute_id' => ['required', 'integer', 'exists:institutes,id'],
            'role_title' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $institute = Institute::query()->approved()->findOrFail($validated['institute_id']);

        if (EducatorInstituteAffiliation::query()
            ->where('educator_id', $educator->id)
            ->where('institute_id', $institute->id)
            ->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'You are already linked to this school or institute.',
            ], 422);
        }

        $affiliation = EducatorInstituteAffiliation::create([
            'educator_id' => $educator->id,
            'institute_id' => $institute->id,
            'role_title' => $validated['role_title'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'sort_order' => (int) $educator->instituteAffiliations()->count(),
        ]);

        $affiliation->load(['institute.user:id,role']);

        return response()->json([
            'ok' => true,
            'message' => 'School / institute linked to your profile.',
            'html' => view('backend.educator.partials.institute-affiliation-item', [
                'affiliation' => $affiliation,
            ])->render(),
        ]);
    }

    public function destroy(EducatorInstituteAffiliation $affiliation): JsonResponse
    {
        $educator = auth()->user()->educator;
        abort_unless($educator && (int) $affiliation->educator_id === (int) $educator->id, 404);

        $affiliation->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Association removed.',
        ]);
    }
}
