<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityMyAreaController extends Controller
{
    public function index(Request $request): View
    {
        $query = CommunityPost::query()
            ->with('user')
            ->withCount(['reactions', 'comments', 'reportSupports', 'reportFollows'])
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->where('content_type', 'my-area')
            ->when($request->filled('activity'), function ($builder) use ($request): void {
                $builder->where('meta->my_area_activity_type', $request->string('activity')->toString());
            })
            ->when($request->filled('status'), function ($builder) use ($request): void {
                $builder->where('meta->my_area_status_tracker', $request->string('status')->toString());
            })
            ->when($request->filled('state'), function ($builder) use ($request): void {
                $builder->where('meta->location_state', $request->string('state')->toString());
            })
            ->when($request->filled('district'), function ($builder) use ($request): void {
                $builder->where('meta->location_district', $request->string('district')->toString());
            })
            ->when($request->filled('city'), function ($builder) use ($request): void {
                $builder->where('meta->location_city', $request->string('city')->toString());
            })
            ->when($request->filled('topic'), function ($builder) use ($request): void {
                $topic = $request->string('topic')->toString();
                $builder->where(function ($nested) use ($topic): void {
                    $nested->where('category', $topic)
                        ->orWhere('meta->my_area_topic_category', $topic);
                });
            })
            ->latest('published_at');

        $posts = $query->paginate(12)->withQueryString();

        return view('community.my-area.index', [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::formTypes(),
            'activityTypes' => CommunityContentTaxonomy::myAreaActivityTypes(),
            'topicCategories' => CommunityContentTaxonomy::myAreaTopicCategories(),
            'statusSteps' => CommunityContentTaxonomy::myAreaStatusTrackerSteps(),
            'filters' => [
                'activity' => $request->string('activity')->toString(),
                'status' => $request->string('status')->toString(),
                'state' => $request->string('state')->toString(),
                'district' => $request->string('district')->toString(),
                'city' => $request->string('city')->toString(),
                'topic' => $request->string('topic')->toString(),
            ],
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ]);
    }
}
