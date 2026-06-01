<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConsultantPendingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user?->isConsultant()) {
            return redirect()->route('login');
        }

        if ($user->consultant?->isApproved()) {
            return redirect()->route('consultant.dashboard');
        }

        return view('backend.consultant.pending', [
            'consultant' => $user->consultant,
        ]);
    }
}
