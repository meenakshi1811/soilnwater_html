<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceProviderPendingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user?->isServiceProvider()) {
            return redirect()->route('login');
        }

        if ($user->serviceProvider?->isApproved()) {
            return redirect()->route('service_provider.dashboard');
        }

        return view('backend.service_provider.pending', [
            'service_provider' => $user->serviceProvider,
        ]);
    }
}
