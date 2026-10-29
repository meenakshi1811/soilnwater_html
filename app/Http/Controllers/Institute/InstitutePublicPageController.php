<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Support\ManagedInstitute;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutePublicPageController extends Controller
{
    public function edit(Request $request): View
    {
        $institute = ManagedInstitute::current($request);
        abort_unless($institute, 404);
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
