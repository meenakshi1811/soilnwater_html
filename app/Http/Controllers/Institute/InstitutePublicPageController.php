<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutePublicPageController extends Controller
{
    public function edit(Request $request): View
    {
        $institute = $request->user()->institute;
        $institute->load([
            'notices',
            'achievements',
            'topPerformers',
            'schoolClasses',
            'books',
        ]);

        return view('backend.institute.public-page', compact('institute'));
    }
}
