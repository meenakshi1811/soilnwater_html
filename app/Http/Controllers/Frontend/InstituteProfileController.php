<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\InstituteEnquiryReceivedMail;
use App\Models\Institute;
use App\Models\InstituteEnquiry;
use App\Services\PortalNotificationService;
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

        return view('frontend.institutes.show', [
            'institute' => $institute,
            'ownerRole' => $ownerRole,
            'listingContext' => $ownerRole === 'school' ? 'schools' : 'institutes',
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

        PortalNotificationService::notifyUser(
            $owner,
            'New enquiry received',
            $fromName.' sent you an enquiry'.($enquiry->subject ? ': '.$enquiry->subject : '.'),
            $institute->user?->portalRoute('enquiries.index') ?? \App\Support\SchoolInstituteHelper::routeForPrefix('school', 'enquiries.index'),
            'engagement'
        );

        $emailSent = false;
        $recipient = $institute->email ?: $owner?->email;
        if ($recipient) {
            try {
                Mail::to($recipient)->send(InstituteEnquiryReceivedMail::forEnquiry($enquiry));
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
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
