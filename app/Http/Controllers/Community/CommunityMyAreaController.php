<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityMyAreaController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $posts = $this->paginateMyAreaPosts($request);

        if ($request->ajax()) {
            return $this->myAreaPostsAjaxResponse($posts);
        }

        return view('community.my-area.index', $this->viewData($request, $posts));
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(Request $request, LengthAwarePaginator $posts): array
    {
        return [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::formTypes(),
            'activityTypes' => CommunityContentTaxonomy::myAreaActivityTypes(),
            'topicCategories' => CommunityContentTaxonomy::myAreaTopicCategories(),
            'statusSteps' => CommunityContentTaxonomy::myAreaStatusTrackerSteps(),
            'filters' => $this->filtersFromRequest($request),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ];
    }

    private function paginateMyAreaPosts(Request $request): LengthAwarePaginator
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
                $builder->where('meta->location_state', 'like', '%'.$request->string('state')->toString().'%');
            })
            ->when($request->filled('district'), function ($builder) use ($request): void {
                $builder->where('meta->location_district', 'like', '%'.$request->string('district')->toString().'%');
            })
            ->when($request->filled('city'), function ($builder) use ($request): void {
                $builder->where('meta->location_city', 'like', '%'.$request->string('city')->toString().'%');
            })
            ->when($request->filled('topic'), function ($builder) use ($request): void {
                $topic = $request->string('topic')->toString();
                $builder->where(function ($nested) use ($topic): void {
                    $nested->where('category', $topic)
                        ->orWhere('meta->my_area_topic_category', $topic);
                });
            })
            ->latest('published_at');

        return $query->paginate(12)->withQueryString();
    }

    /**
     * @return array<string, string>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'activity' => $request->string('activity')->toString(),
            'status' => $request->string('status')->toString(),
            'state' => $request->string('state')->toString(),
            'district' => $request->string('district')->toString(),
            'city' => $request->string('city')->toString(),
            'topic' => $request->string('topic')->toString(),
        ];
    }

    private function myAreaPostsAjaxResponse(LengthAwarePaginator $posts): JsonResponse
    {
        return response()->json([
            'html' => view('community.partials.post-cards', [
                'posts' => $posts,
                'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
                'emptyMessage' => 'No My Area posts match your filters yet. Try adjusting filters or be the first to share with your neighbours.',
            ])->render(),
            'next_page_url' => $posts->nextPageUrl(),
            'loaded_to' => $posts->lastItem() ?? 0,
            'total' => $posts->total(),
        ]);
    }
}
