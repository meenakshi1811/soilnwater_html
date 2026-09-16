<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InstitutePendingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user?->isSchoolOrInstitute()) {
            return redirect()->route('login');
        }

        if ($user->institute?->isApproved()) {
            return redirect()->to($user->portalRoute('dashboard'));
        }

        return view('backend.institute.pending', [
            'institute' => $user->institute,
        ]);
    }
}
