<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Models\EducatorEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducatorProfileController extends Controller
{
    public function show(string $slug): View
    {
        $educator = Educator::query()
            ->approved()
            ->where('slug', $slug)
            ->with([
                'user:id,name,profile_image',
                'reviews' => fn ($q) => $q->latest()->limit(12),
                'studyMaterials' => fn ($q) => $q->approved()->latest()->limit(8),
            ])
            ->withCount(['followers', 'studyMaterials as approved_materials_count' => fn ($q) => $q->where('status', 'approved')])
            ->firstOrFail();

        $isFollowing = auth()->check()
            && $educator->followers()->where('user_id', auth()->id())->exists();

        $notes = $educator->studyMaterials->where('material_type', 'notes')->values();
        $courses = $educator->studyMaterials->where('material_type', '!=', 'notes')->values();

        return view('frontend.educator.show', compact('educator', 'isFollowing', 'notes', 'courses'));
    }

    public function enquiry(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $educator = Educator::query()->approved()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        EducatorEnquiry::create([
            'educator_id' => $educator->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'] ?? auth()->user()?->email,
            'phone' => $validated['phone'] ?? auth()->user()?->phone_number,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Enquiry sent successfully.']);
        }

        return back()->with('status', 'Enquiry sent successfully.');
    }

    public function follow(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $educator = Educator::query()->approved()->where('slug', $slug)->firstOrFail();
        $userId = auth()->id();

        $attached = $educator->followers()->where('user_id', $userId)->exists();
        if ($attached) {
            $educator->followers()->detach($userId);
            $following = false;
            $message = 'Unfollowed '.$educator->display_name.'.';
        } else {
            $educator->followers()->attach($userId);
            $following = true;
            $message = 'You are now following '.$educator->display_name.'.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'following' => $following,
                'followers_count' => $educator->followers()->count(),
            ]);
        }

        return back()->with('status', $message);
    }
}
