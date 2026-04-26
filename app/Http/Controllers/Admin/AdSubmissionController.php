<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAd;
use App\Support\AdSizes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AdSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        return view('backend.ads.admin.submissions.index', [
            'sizes' => AdSizes::all(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = UserAd::query()
            ->with(['user:id,name,full_name', 'template:id,name,size_type'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('size_type') && AdSizes::exists($request->string('size_type')->toString())) {
            $query->where('size_type', $request->string('size_type')->toString());
        }

        $sizes = AdSizes::all();

        return DataTables::of($query)
            ->addColumn('user_name', fn (UserAd $ad) => $ad->user?->full_name ?: ($ad->user?->name ?? '-'))
            ->addColumn('size_label', fn (UserAd $ad) => $sizes[$ad->size_type]['name'] ?? $ad->size_type)
            ->addColumn('template_name', fn (UserAd $ad) => $ad->template?->name ?? '-')
            ->addColumn('status_badge', function (UserAd $ad) {
                $badge = match ($ad->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'pending' => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$badge.'">'.ucfirst($ad->status).'</span>';
            })
            ->editColumn('submitted_at', fn (UserAd $ad) => $ad->submitted_at?->format('Y-m-d H:i') ?? '-')
            ->addColumn('actions', fn (UserAd $ad) => '<div class="d-flex justify-content-end"><a href="'.route('admin.ads.submissions.show', $ad).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a></div>')
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(UserAd $ad): View
    {
        $ad->load(['user:id,name,full_name,email', 'template:id,name,size_type,layout_html']);

        return view('backend.ads.admin.submissions.show', [
            'ad' => $ad,
            'size' => AdSizes::all()[$ad->size_type] ?? null,
        ]);
    }

    public function approve(Request $request, UserAd $ad): RedirectResponse|JsonResponse
    {
        $ad->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $request->string('review_note')->toString() ?: null,
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Ad approved.']);
        }

        return back()->with('success', 'Ad approved.');
    }

    public function reject(Request $request, UserAd $ad): RedirectResponse|JsonResponse
    {
        $request->validate([
            'review_note' => 'required|string|max:400',
        ]);

        $ad->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $request->string('review_note')->toString(),
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Ad rejected.']);
        }

        return back()->with('success', 'Ad rejected.');
    }
}
