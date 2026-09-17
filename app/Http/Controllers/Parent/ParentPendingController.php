<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ParentPendingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user?->isParent()) {
            return redirect()->route('login');
        }

        if ($user->hasParentProfileEnabled()) {
            return redirect()->route('parent.dashboard');
        }

        return view('backend.parent.pending', [
            'parentProfile' => $user->parentProfile,
        ]);
    }
}
