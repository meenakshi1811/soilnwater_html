<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\InstituteEnquiryReceivedMail;
use App\Models\Institute;
use App\Models\InstituteCompareItem;
use App\Models\InstituteEnquiry;
use App\Models\InstituteProfileFeedback;
use App\Services\PortalNotificationService;
use App\Support\SchoolInstituteHelper;
use App\Support\SchoolProfilePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InstituteProfileController extends Controller
{
    public function schoolShow(string $slug): View
    {
        return $this->show($slug, 'school');
    }

    public function instituteShow(string $slug): View
    {
        return $this->show($slug, 'institute');
    }

    public function show(string $slug, string $ownerRole = 'school'): View
    {
        $institute = $this->findApprovedProfile($slug, $ownerRole);

        $view = $ownerRole === 'school'
            ? 'frontend.institutes.show-school'
            : 'frontend.institutes.show';

        $authUser = auth()->user();
        $engagement = $this->engagementState($institute, $authUser);

        return view($view, [
            'institute' => $institute,
            'profile' => $ownerRole === 'school' ? new SchoolProfilePresenter($institute) : null,
            'ownerRole' => $ownerRole,
            'listingContext' => $ownerRole === 'school' ? 'schools' : 'institutes',
            'activeNav' => 'home',
            'engagement' => $engagement,
        ]);
    }

    public function schoolEnquiry(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        return $this->enquiry($request, $slug, 'school');
    }

    public function instituteEnquiry(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        return $this->enquiry($request, $slug, 'institute');
    }

    public function enquiry(Request $request, string $slug, string $ownerRole = 'school'): RedirectResponse|JsonResponse
    {
        $institute = $this->findApprovedProfile($slug, $ownerRole);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $enquiry = InstituteEnquiry::create([
            'institute_id' => $institute->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'] ?? auth()->user()?->email,
            'phone' => $validated['phone'] ?? auth()->user()?->phone_number,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        $owner = $institute->user;
        $fromName = $enquiry->name ?: 'Someone';
        $portalPrefix = $ownerRole === 'school' ? 'school' : 'institute';
        $enquiriesPortalUrl = $owner?->portalRoute('enquiries.index')
            ?? SchoolInstituteHelper::routeForPrefix($portalPrefix, 'enquiries.index');

        PortalNotificationService::notifyUser(
            $owner,
            'New enquiry received',
            $fromName.' sent you an enquiry'.($enquiry->subject ? ': '.$enquiry->subject : '.'),
            $enquiriesPortalUrl,
            'engagement'
        );

        $emailSent = false;
        $recipient = $institute->email ?: $owner?->email;
        if ($recipient) {
            try {
                Mail::to($recipient)->send(InstituteEnquiryReceivedMail::forEnquiry($enquiry, $enquiriesPortalUrl));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error('Failed to send institute enquiry mail', [
                    'enquiry_id' => $enquiry->id,
                    'email' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = 'Enquiry sent successfully.'
            .($emailSent ? ' The institute has been notified by email and portal.' : ' The institute has been notified in the portal.');

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    /** @return array<string, mixed> */
    private function engagementState(Institute $institute, ?\App\Models\User $user): array
    {
        $listingContext = $institute->user?->isSchool() ? 'schools' : 'institutes';
        $compareUrl = $listingContext === 'schools' ? route('schools.compare') : route('institutes.compare');
        $helpfulVote = null;

        if ($user) {
            $helpfulVote = InstituteProfileFeedback::query()
                ->where('institute_id', $institute->id)
                ->where('user_id', $user->id)
                ->value('vote');
        }

        if (! $user) {
            return [
                'is_following' => false,
                'is_bookmarked' => false,
                'in_compare' => false,
                'has_brochure' => filled($institute->brochure_path),
                'followers_count' => $institute->followers()->count(),
                'compare_count' => 0,
                'compare_url' => $compareUrl,
                'helpful_vote' => null,
                'can_report' => false,
            ];
        }

        return [
            'is_following' => $institute->followers()->where('user_id', $user->id)->exists(),
            'is_bookmarked' => $institute->bookmarks()->where('user_id', $user->id)->exists(),
            'in_compare' => InstituteCompareItem::query()
                ->where('user_id', $user->id)
                ->where('institute_id', $institute->id)
                ->exists(),
            'has_brochure' => filled($institute->brochure_path),
            'followers_count' => $institute->followers()->count(),
            'compare_count' => InstituteCompareItem::query()->where('user_id', $user->id)->count(),
            'compare_url' => $compareUrl,
            'helpful_vote' => $helpfulVote,
            'can_report' => (int) $institute->user_id !== (int) $user->id,
        ];
    }

    private function findApprovedProfile(string $slug, string $ownerRole): Institute
    {
        return Institute::query()
            ->approved()
            ->forOwnerRole($ownerRole)
            ->where('slug', $slug)
            ->with([
                'user:id,name,profile_image,role',
                'activeNotices',
                'achievements',
                'topPerformers',
                'schoolClasses',
                'books',
            ])
            ->firstOrFail();
    }
}
