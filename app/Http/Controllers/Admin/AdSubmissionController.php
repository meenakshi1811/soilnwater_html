<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Models\Category;
use App\Models\UserAd;
use App\Support\AdSizes;
use App\Support\ModulePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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

        if ($request->filled('posted_by')) {
            $postedBy = $request->string('posted_by')->toString();

            if ($postedBy === 'admin') {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('role', 'admin'));
            } elseif ($postedBy === 'user') {
                $query->whereHas('user', fn ($userQuery) => $userQuery->whereNotIn('role', ['admin', 'employee']));
            }
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
            ->addColumn('banner_preview', function (UserAd $ad) {
                if (! $ad->final_image) {
                    return '-';
                }

                $imageUrl = asset($ad->final_image);

                return '<a href="'.$imageUrl.'" target="_blank" rel="noopener noreferrer">'
                    .'<img src="'.$imageUrl.'" alt="'.$ad->title.' banner" style="width: 96px; height: 56px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">'
                    .'</a>';
            })
            ->editColumn('submitted_at', fn (UserAd $ad) => $ad->submitted_at?->timezone('Asia/Kolkata')->format('Y-m-d h:i A') ?? '-')
            ->addColumn('valid_until', fn (UserAd $ad) => $ad->valid_until?->format('Y-m-d') ?? 'No Expiry')
            ->addColumn('actions', fn (UserAd $ad) => '<div class="d-flex justify-content-end gap-2"><a href="'.route('admin.ads.submissions.show', $ad).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a><button type="button" class="btn btn-sm btn-outline-danger js-delete-submission" data-id="'.$ad->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button></div>')
            ->rawColumns(['status_badge', 'banner_preview', 'actions'])
            ->make(true);
    }

    public function show(UserAd $ad): View
    {
        $ad->load(['user:id,name,full_name,email', 'template:id,name,size_type,layout_html', 'category:id,name', 'subcategory:id,name']);

        $moduleLabels = ModulePermissions::modules();
        $selectedModuleLabels = collect($ad->selected_modules ?? [])
            ->filter(fn ($key) => is_string($key) && isset($moduleLabels[$key]))
            ->map(fn (string $key) => $moduleLabels[$key])
            ->values()
            ->all();

        $selectedCategoryLabels = Category::query()
            ->whereIn('id', array_map('intval', $ad->selected_category_ids ?? []))
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $selectedSubcategoryLabels = Category::query()
            ->whereIn('id', array_map('intval', $ad->selected_subcategory_ids ?? []))
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        if ($selectedCategoryLabels === [] && $ad->category?->name) {
            $selectedCategoryLabels = [$ad->category->name];
        }

        if ($selectedSubcategoryLabels === [] && $ad->subcategory?->name) {
            $selectedSubcategoryLabels = [$ad->subcategory->name];
        }

        return view('backend.ads.admin.submissions.show', [
            'ad' => $ad,
            'size' => AdSizes::all()[$ad->size_type] ?? null,
            'selectedModuleLabels' => $selectedModuleLabels,
            'selectedCategoryLabels' => $selectedCategoryLabels,
            'selectedSubcategoryLabels' => $selectedSubcategoryLabels,
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

        PortalNotificationService::notifyOwnerOfReview($ad->user, 'Ad', $ad->title, 'approved', route('ads.show', $ad));

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

        PortalNotificationService::notifyOwnerOfReview($ad->user, 'Ad', $ad->title, 'rejected', route('ads.show', $ad));

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Ad rejected.']);
        }

        return back()->with('success', 'Ad rejected.');
    }

    public function destroy(Request $request, UserAd $ad): RedirectResponse|JsonResponse
    {
        if ($ad->final_image) {
            File::delete(public_path($ad->final_image));
        }

        $ad->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => 'Ad deleted successfully.']);
        }

        return back()->with('success', 'Ad deleted successfully.');
    }
}
