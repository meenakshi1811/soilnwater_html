<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class InstituteEnquiryController extends Controller
{
    public function index(): View
    {
        $institute = auth()->user()->institute;
        abort_unless($institute, 404);

        $institute->enquiries()->where('status', 'new')->update(['status' => 'seen']);

        $enquiries = $institute->enquiries()->with('user:id,name,email')->paginate(20);

        return view('backend.institute.enquiries.index', compact('institute', 'enquiries'));
    }
}
