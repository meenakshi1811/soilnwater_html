<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        return view('backend.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);

        $notification->markAsRead();

        return redirect()->to($notification->data['url'] ?? url()->previous());
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function destroy(Request $request, DatabaseNotification $notification): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeNotification($request, $notification);

        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Notification deleted.']);
        }

        return back()->with('status', 'Notification deleted.');
    }

    private function authorizeNotification(Request $request, DatabaseNotification $notification): void
    {
        abort_unless(
            $notification->notifiable_type === $request->user()->getMorphClass()
            && (int) $notification->notifiable_id === (int) $request->user()->id,
            403
        );
    }
}
