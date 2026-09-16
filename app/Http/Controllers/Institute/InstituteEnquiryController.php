<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class InstituteEnquiryController extends Controller
{
    public function index(): View
    {
        $institute = auth()->user()->institute;
        $enquiries = $institute->enquiries()->with('user:id,name,email')->paginate(20);

        return view('backend.institute.enquiries.index', compact('institute', 'enquiries'));
    }
}
