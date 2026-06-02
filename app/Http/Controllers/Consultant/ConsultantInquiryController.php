<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\ConsultantServiceInquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultantInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $consultantId = $request->user()->consultant?->id;

        $inquiries = ConsultantServiceInquiry::query()
            ->with(['service:id,name,category_id,subcategory_id', 'service.categoryModel:id,name', 'service.subcategoryModel:id,name'])
            ->where('consultant_id', $consultantId)
            ->latest()
            ->get();

        return view('backend.consultant.inquiries.index', compact('inquiries'));
    }
}
