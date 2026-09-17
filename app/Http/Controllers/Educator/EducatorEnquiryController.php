<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Mail\EducatorEnquiryAnsweredMail;
use App\Models\EducatorEnquiry;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EducatorEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $educatorId = $request->user()->educator?->id;

        $enquiries = EducatorEnquiry::query()
            ->where('educator_id', $educatorId)
            ->with('user:id,name,email')
            ->latest()
            ->get();

        $pendingEnquiries = $enquiries->filter(fn (EducatorEnquiry $enquiry): bool => ! $enquiry->isAnswered());
        $answeredEnquiries = $enquiries->filter(fn (EducatorEnquiry $enquiry): bool => $enquiry->isAnswered());

        return view('backend.educator.enquiries.index', [
            'enquiries' => $enquiries,
            'pendingEnquiries' => $pendingEnquiries,
            'answeredEnquiries' => $answeredEnquiries,
            'pendingCount' => $pendingEnquiries->count(),
            'answeredCount' => $answeredEnquiries->count(),
        ]);
    }

    public function answer(Request $request, EducatorEnquiry $enquiry): JsonResponse|RedirectResponse
    {
        $educatorId = $request->user()->educator?->id;
        abort_unless($educatorId && (int) $enquiry->educator_id === (int) $educatorId, 403);

        $data = $request->validate([
            'answer' => ['required', 'string', 'max:3000'],
        ]);

        $enquiry->update([
            'answer' => $data['answer'],
            'answered_at' => now(),
            'status' => 'answered',
        ]);

        $enquiry->load(['educator', 'user']);

        $this->notifyAskerOfAnswer($enquiry);

        $message = 'Your answer has been sent to the student. They will be notified by email and portal.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function notifyAskerOfAnswer(EducatorEnquiry $enquiry): void
    {
        $educatorName = $enquiry->educator?->display_name ?: 'The educator';

        if ($enquiry->user) {
            PortalNotificationService::notifyUser(
                $enquiry->user,
                'Your question was answered',
                $educatorName.' answered your question on their Teacher / Tutor profile.',
                $enquiry->profileUrl(),
                'engagement'
            );
        }

        $recipient = $enquiry->user?->email ?: $enquiry->email;
        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send(EducatorEnquiryAnsweredMail::forEnquiry($enquiry));
        } catch (\Throwable $e) {
            Log::error('Failed to send educator enquiry answered mail', [
                'enquiry_id' => $enquiry->id,
                'email' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
