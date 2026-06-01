<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ConsultantService;
use Illuminate\View\View;

class ConsultantStoreController extends Controller
{
    public function show(string $slug): View
    {
        $consultant = $this->resolveConsultant($slug);

        $approvedServices = ConsultantService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name'])
            ->where('consultant_id', $consultant->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->get();

        return view('frontend.consultant.show', [
            'consultant' => $consultant,
            'preview' => false,
            'activeNav' => 'home',
            'approvedServices' => $approvedServices,
            'consultantRecentAds' => collect(),
            'selectedCategoryNamesByConsultantAdId' => [],
        ]);
    }

    public function about(string $slug): View
    {
        return view('frontend.consultant.about', [
            'consultant' => $this->resolveConsultant($slug),
            'activeNav' => 'about',
        ]);
    }

    public function contact(string $slug): View
    {
        return view('frontend.consultant.contact', [
            'consultant' => $this->resolveConsultant($slug),
            'activeNav' => 'contact',
        ]);
    }

    private function resolveConsultant(string $slug): Consultant
    {
        return Consultant::query()
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->with(['branches', 'bannerSlides', 'pageSections'])
            ->firstOrFail();
    }
}
