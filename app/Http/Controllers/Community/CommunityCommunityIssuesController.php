<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Services\CommunityIssuesHubService;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityCommunityIssuesController extends Controller
{
    public function __construct(
        private readonly CommunityIssuesHubService $hubService,
    ) {}

    public function index(Request $request): View
    {
        $viewer = auth()->user();
        $preset = $request->string('map')->toString() ?: 'all';
        $presets = CommunityContentTaxonomy::communityIssueHeatMapPresets();

        if (! array_key_exists($preset, $presets)) {
            $preset = 'all';
        }

        $posts = $this->hubService->publishedIssuesQuery($viewer)
            ->with('user')
            ->withCount(['reactions', 'comments', 'reportSupports', 'reportFollows'])
            ->when($request->filled('category'), function ($builder) use ($request): void {
                $builder->where('meta->community_issue_category', $request->string('category')->toString());
            })
            ->when($request->filled('severity'), function ($builder) use ($request): void {
                $builder->where('meta->community_issue_severity', $request->string('severity')->toString());
            })
            ->when($request->filled('status'), function ($builder) use ($request): void {
                $builder->where('meta->community_issue_status_tracker', $request->string('status')->toString());
            })
            ->when($request->filled('state'), function ($builder) use ($request): void {
                $builder->where('meta->location_state', $request->string('state')->toString());
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('community.community-issues.index', [
            'posts' => $posts,
            'types' => CommunityContentTaxonomy::formTypes(),
            'dashboard' => $this->hubService->dashboardStats($viewer),
            'heatMapPresets' => $presets,
            'activeMapPreset' => $preset,
            'heatMapMarkers' => $this->hubService->heatMapMarkers($preset, $viewer),
            'champions' => $this->hubService->communityChampions(8, $viewer),
            'championBadges' => CommunityContentTaxonomy::communityIssueChampionBadgeDefinitions(),
            'categories' => CommunityContentTaxonomy::communityIssueMainCategories(),
            'severityLevels' => CommunityContentTaxonomy::communityIssueSeverityLevels(),
            'statusSteps' => CommunityContentTaxonomy::communityIssueStatusSteps(),
            'filters' => [
                'category' => $request->string('category')->toString(),
                'severity' => $request->string('severity')->toString(),
                'status' => $request->string('status')->toString(),
                'state' => $request->string('state')->toString(),
                'map' => $preset,
            ],
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ]);
    }

    public function heatMapData(Request $request): JsonResponse
    {
        $preset = $request->string('preset')->toString() ?: 'all';
        $presets = CommunityContentTaxonomy::communityIssueHeatMapPresets();

        if (! array_key_exists($preset, $presets)) {
            $preset = 'all';
        }

        return response()->json([
            'preset' => $preset,
            'label' => $presets[$preset]['label'],
            'color' => $presets[$preset]['color'],
            'markers' => $this->hubService->heatMapMarkers($preset, auth()->user()),
        ]);
    }
}
