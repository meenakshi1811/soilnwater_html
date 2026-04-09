<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $roleOrder = ['user', 'vendor', 'builder', 'developer', 'consultant', 'employee'];

        $countsByRole = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->whereIn('role', $roleOrder)
            ->groupBy('role')
            ->pluck('total', 'role');

        $roleStats = collect($roleOrder)->map(function (string $role) use ($countsByRole) {
            return [
                'role' => $role,
                'label' => ucfirst($role),
                'count' => (int) ($countsByRole[$role] ?? 0),
            ];
        });

        $roleTotal = (int) $roleStats->sum('count');

        $roleStats = $roleStats->map(function (array $row) use ($roleTotal) {
            $row['percentage'] = $roleTotal > 0 ? (int) round(($row['count'] / $roleTotal) * 100) : 0;

            return $row;
        });

        $adsSeries = [16, 24, 30, 41, 46, 58, 63];
        $offersSeries = [9, 15, 19, 28, 33, 39, 44];

        return view('backend.dashboard', [
            'roleStats' => $roleStats,
            'roleLabels' => $roleStats->pluck('label')->all(),
            'roleCounts' => $roleStats->pluck('count')->all(),
            'totalAds' => array_sum($adsSeries),
            'totalOffers' => array_sum($offersSeries),
            'adsSeries' => $adsSeries,
            'offersSeries' => $offersSeries,
        ]);
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
