<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\EducatorEnquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducatorEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $educatorId = $request->user()->educator?->id;

        $enquiries = EducatorEnquiry::query()
            ->where('educator_id', $educatorId)
            ->latest()
            ->get();

        return view('backend.educator.enquiries.index', compact('enquiries'));
    }
}
