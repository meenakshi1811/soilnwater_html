<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(): RedirectResponse|\Illuminate\Contracts\Support\Renderable
    {
        $user = auth()->user();

        if ($user?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user?->role === 'employee') {
            $slug = $user->firstReadableModuleSlug();
            if ($slug) {
                return redirect()->route('modules.show', ['module' => $slug]);
            }

            return view('home');
        }

        if ($user?->isGeneralUser()) {
            return redirect()->route('user.dashboard');
        }

        if ($user?->isVendor()) {
            if ($user->vendor?->isApproved()) {
                return redirect()->route('vendor.dashboard');
            }

            return redirect()->route('vendor.pending');
        }

        if ($user?->isConsultant()) {
            if ($user->consultant?->isApproved()) {
                return redirect()->route('consultant.dashboard');
            }

            return redirect()->route('consultant.pending');
        }

        if ($user?->isServiceProvider()) {
            if ($user->serviceProvider?->isApproved()) {
                return redirect()->route('service_provider.dashboard');
            }

            return redirect()->route('service_provider.pending');
        }

        return view('home');
    }
}
