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
        if (auth()->user()?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('home');
    }
}
