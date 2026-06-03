<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProviderServiceInquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceProviderInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $service_providerId = $request->user()->serviceProvider?->id;

        $inquiries = ServiceProviderServiceInquiry::query()
            ->with(['service:id,name,category_id,subcategory_id', 'service.categoryModel:id,name', 'service.subcategoryModel:id,name'])
            ->where('service_provider_id', $service_providerId)
            ->latest()
            ->get();

        return view('backend.service_provider.inquiries.index', compact('inquiries'));
    }
}
