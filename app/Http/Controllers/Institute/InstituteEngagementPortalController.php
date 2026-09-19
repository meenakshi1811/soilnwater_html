<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\InstituteEngagement;
use Illuminate\View\View;

class InstituteEngagementPortalController extends Controller
{
    public function index(): View
    {
        $institute = auth()->user()->institute;
        abort_unless($institute, 404);

        $institute->loadCount(['followers', 'bookmarks']);

        $followers = $institute->followers()
            ->select('users.id', 'users.name', 'users.email', 'users.phone_number')
            ->latest('institute_followers.created_at')
            ->paginate(15, ['*'], 'followers_page');

        $recentActivity = $institute->engagements()
            ->with('user:id,name,email')
            ->latest()
            ->limit(30)
            ->get();

        $brochureDownloads = $institute->engagements()
            ->where('action', InstituteEngagement::ACTION_BROCHURE_DOWNLOAD)
            ->count();

        $helpfulYes = $institute->profileFeedbacks()->where('vote', 'yes')->count();
        $helpfulNo = $institute->profileFeedbacks()->where('vote', 'no')->count();

        return view('backend.institute.engagement.index', compact(
            'institute',
            'followers',
            'recentActivity',
            'brochureDownloads',
            'helpfulYes',
            'helpfulNo',
        ));
    }
}
