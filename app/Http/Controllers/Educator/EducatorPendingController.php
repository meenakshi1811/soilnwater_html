<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EducatorPendingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user?->isEducator()) {
            return redirect()->route('login');
        }

        if ($user->educator?->isApproved()) {
            return redirect()->route('educator.dashboard');
        }

        return view('backend.educator.pending', [
            'educator' => $user->educator,
        ]);
    }
}
