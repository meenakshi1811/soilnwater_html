<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CommunityPostReportController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.community-posts.reports');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $reports = CommunityPostReport::query()
            ->with(['post:id,slug,title,status', 'reporter:id,name,full_name'])
            ->select(['id', 'community_post_id', 'reported_by', 'reason', 'created_at'])
            ->latest();

        return DataTables::of($reports)
            ->addColumn('post_title', fn (CommunityPostReport $report): string => e($report->post?->title ?? 'Deleted post'))
            ->addColumn('reporter_name', fn (CommunityPostReport $report): string => e($report->reporter?->full_name ?: ($report->reporter?->name ?? 'Guest')))
            ->editColumn('reason', fn (CommunityPostReport $report): string => e(\Illuminate\Support\Str::limit($report->reason, 120)))
            ->editColumn('created_at', fn (CommunityPostReport $report): string => $report->created_at?->format('d M Y H:i') ?? '-')
            ->addColumn('actions', function (CommunityPostReport $report): string {
                if (! $report->post) {
                    return '<span class="text-muted">Post deleted</span>';
                }

                return '<div class="d-flex gap-2 justify-content-end flex-wrap">'
                    .'<a href="'.route('admin.community-posts.show', $report->post).'" class="btn btn-sm btn-outline-secondary">Review post</a>'
                    .'<form method="POST" action="'.route('admin.community-posts.reports.delete-post', $report->post).'" class="d-inline js-delete-reported-post">'
                    .csrf_field()
                    .method_field('DELETE')
                    .'<button type="submit" class="btn btn-sm btn-outline-danger">Delete post</button>'
                    .'</form>'
                    .'</div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function deletePost(CommunityPost $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.community-posts.reports.index')
            ->with('success', 'Reported community post deleted successfully.');
    }
}
