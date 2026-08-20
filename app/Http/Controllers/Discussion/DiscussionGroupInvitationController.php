<?php

namespace App\Http\Controllers\Discussion;

use App\Http\Controllers\Controller;
use App\Models\DiscussionGroupInvitation;
use App\Services\DiscussionGroupInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionGroupInvitationController extends Controller
{
    public function __construct(private DiscussionGroupInvitationService $invitationService) {}

    public function index(Request $request): View|JsonResponse
    {
        $invitations = DiscussionGroupInvitation::query()
            ->with(['topic', 'inviter', 'invitee'])
            ->pending()
            ->where('invitee_id', $request->user()->id)
            ->latest()
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'invitations' => $invitations->map->toBroadcastArray()->values(),
            ]);
        }

        return view('discussions.invitations.index', [
            'invitations' => $invitations,
        ]);
    }

    public function show(Request $request, DiscussionGroupInvitation $invitation): View
    {
        abort_unless($invitation->isInvitee($request->user()), 403);

        $invitation->load(['topic', 'inviter', 'invitee']);

        return view('discussions.invitations.show', [
            'invitation' => $invitation,
        ]);
    }

    public function accept(Request $request, DiscussionGroupInvitation $invitation): JsonResponse|RedirectResponse
    {
        abort_unless($invitation->isInvitee($request->user()), 403);

        if (! $invitation->isPending()) {
            return $this->alreadyResponded($request, $invitation);
        }

        $invitation = $this->invitationService->accept($invitation);

        return $this->responded($request, $invitation, 'You joined the group.');
    }

    public function reject(Request $request, DiscussionGroupInvitation $invitation): JsonResponse|RedirectResponse
    {
        abort_unless($invitation->isInvitee($request->user()), 403);

        if (! $invitation->isPending()) {
            return $this->alreadyResponded($request, $invitation);
        }

        $invitation = $this->invitationService->reject($invitation);

        return $this->responded($request, $invitation, 'You declined the group invitation.');
    }

    private function alreadyResponded(Request $request, DiscussionGroupInvitation $invitation): JsonResponse|RedirectResponse
    {
        $message = 'This invitation has already been '.$invitation->status.'.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'invitation' => $invitation->fresh(['topic', 'inviter', 'invitee'])->toBroadcastArray(),
            ], 422);
        }

        return redirect()
            ->route('discussions.invitations.show', $invitation)
            ->with('status', $message);
    }

    private function responded(Request $request, DiscussionGroupInvitation $invitation, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'invitation' => $invitation->toBroadcastArray(),
            ]);
        }

        $redirect = $invitation->status === DiscussionGroupInvitation::STATUS_ACCEPTED && $invitation->topic
            ? route('discussions.messenger', $invitation->topic)
            : route('discussions.invitations.index');

        return redirect()->to($redirect)->with('success', $message);
    }
}
