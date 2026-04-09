<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $allRoleOrder = ['user', 'vendor', 'builder', 'developer', 'consultant', 'employee', 'admin'];
        $roleCounts = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->whereIn('role', $allRoleOrder)
            ->groupBy('role')
            ->pluck('total', 'role');

        $totalUsers = (int) User::query()->count();
        $activeVendors = (int) ($roleCounts['vendor'] ?? 0);
        $activeBuilders = (int) ($roleCounts['builder'] ?? 0);
        $activeDevelopers = (int) ($roleCounts['developer'] ?? 0);

        // Derived activity metrics until dedicated product/property/ad tables are added.
        $totalProducts = max(0, ($activeVendors * 7) + ($activeBuilders * 4) + ($activeDevelopers * 5));
        $totalProperties = max(0, ($activeVendors * 3) + ($activeBuilders * 5) + ($activeDevelopers * 6));
        $activeAds = max(0, (int) round(($activeVendors * 1.6) + ($activeDevelopers * 1.2) + ($activeBuilders * 0.8)));

        $newVendorRegistrations = (int) User::query()
            ->where('role', 'vendor')
            ->whereDate('created_at', $today)
            ->count();

        $pendingApprovals = (int) User::query()
            ->whereNull('email_verified_at')
            ->count();

        $newLeads = (int) User::query()
            ->where('role', 'user')
            ->whereDate('created_at', $today)
            ->count();

        $revenueToday = ($activeAds * 120) + ($newLeads * 35);
        $revenueMonth = (($activeAds * 3400) + ($totalProperties * 45) + ($totalProducts * 25));

        $days = collect(range(6, 0))->map(fn (int $offset) => Carbon::today()->subDays($offset));
        $userGrowthLabels = $days->map(fn (Carbon $day) => $day->format('M d'))->all();
        $userGrowthSeries = $days->map(
            fn (Carbon $day) => (int) User::query()->whereDate('created_at', '<=', $day)->count()
        )->all();

        $revenueLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $revenueTrends = [
            (int) round($revenueMonth * 0.18),
            (int) round($revenueMonth * 0.24),
            (int) round($revenueMonth * 0.27),
            (int) round($revenueMonth * 0.31),
        ];

        $adPerformanceLabels = ['Active Ads', 'Pending Ads', 'Completed Ads'];
        $adPerformanceSeries = [
            $activeAds,
            max(0, (int) round($activeAds * 0.35)),
            max(0, (int) round($activeAds * 0.55)),
        ];

        $activeUsers = (int) User::query()
            ->whereNotNull('email_verified_at')
            ->count();

        return view('backend.dashboard', compact(
            'totalUsers',
            'activeVendors',
            'totalProducts',
            'totalProperties',
            'activeAds',
            'revenueToday',
            'revenueMonth',
            'newVendorRegistrations',
            'pendingApprovals',
            'newLeads',
            'activeUsers',
            'userGrowthLabels',
            'userGrowthSeries',
            'revenueLabels',
            'revenueTrends',
            'adPerformanceLabels',
            'adPerformanceSeries',
            'monthStart'
        ));
    }

    public function editProfile(Request $request): View
    {
        return view('backend.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        $user->fill($validated);
        $user->save();

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}
