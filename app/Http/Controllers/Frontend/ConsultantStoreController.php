<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\ConsultantServiceInquiry;
use App\Models\User;
use App\Models\UserAd;
use App\Services\MarketplaceAdsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $adsContext = $this->loadStoreAds($consultant, $consultant->pageSections->count());
        $consultantRecentAds = $this->nearestConsultantModuleAds();

        return view('frontend.consultant.show', [
            'consultant' => $consultant,
            'preview' => false,
            'activeNav' => 'home',
            'approvedServices' => $approvedServices,
            'consultantRecentAds' => $consultantRecentAds,
            'selectedCategoryNamesByConsultantAdId' => $this->resolveSelectedCategoryNamesByAdId($consultantRecentAds),
            'randomFullPagePlacements' => $adsContext['randomFullPagePlacements'],
            'sponsoredFillers' => $adsContext['sponsoredFillers'],
        ]);
    }



    public function services(string $slug): View
    {
        return $this->renderServiceCatalog($slug);
    }

    public function categoryServices(string $slug, Category $category): View
    {
        $this->assertConsultantCategory($category);

        return $this->renderServiceCatalog($slug, $category);
    }

    public function subcategoryServices(string $slug, Category $category, Category $subcategory): View
    {
        $this->assertConsultantCategory($category);
        abort_unless((int) $subcategory->parent_id === (int) $category->id, 404);
        $this->assertConsultantCategory($subcategory, isSubcategory: true);

        return $this->renderServiceCatalog($slug, $category, $subcategory);
    }

    public function sendServiceInquiry(Request $request, string $slug, ConsultantService $service): JsonResponse
    {
        $consultant = $this->resolveConsultant($slug);
        abort_unless($service->consultant_id === $consultant->id && $service->status === 'approved', 404);

        if (! $request->user()) {
            return response()->json(['message' => 'Please login to send an enquiry.'], 403);
        }

        $data = $request->validate([
            'client_name' => ['required', 'string', 'max:120'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'question' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/consultant-inquiries', 'public');
            $data['image_path'] = 'storage/'.$path;
        }

        $inquiry = ConsultantServiceInquiry::query()->create([
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        if ($consultant->email) {
            $body = view('emails.consultant.new-inquiry', compact('inquiry', 'consultant', 'service'))->render();
            Mail::send([], [], function ($message) use ($consultant, $service, $body) {
                $message->to($consultant->email)->subject('New consultation enquiry: '.$service->name)->html($body);
            });
        }

        $this->sendConsultantInquirySms($consultant, $service);

        return response()->json(['message' => 'Enquiry submitted successfully.']);
    }

    public function sendGeneralInquiry(Request $request, string $slug): JsonResponse
    {
        $consultant = $this->resolveConsultant($slug);

        if (! $request->user()) {
            return response()->json(['message' => 'Please login to send an enquiry.'], 403);
        }

        $data = $request->validate([
            'consultant_service_id' => ['required', 'integer'],
            'client_name' => ['required', 'string', 'max:120'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'question' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $service = ConsultantService::query()
            ->where('id', $data['consultant_service_id'])
            ->where('consultant_id', $consultant->id)
            ->where('status', 'approved')
            ->firstOrFail();

        unset($data['consultant_service_id']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/consultant-inquiries', 'public');
            $data['image_path'] = 'storage/'.$path;
        }

        $inquiry = ConsultantServiceInquiry::query()->create([
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        if ($consultant->email) {
            $body = view('emails.consultant.new-inquiry', compact('inquiry', 'consultant', 'service'))->render();
            Mail::send([], [], function ($message) use ($consultant, $service, $body) {
                $message->to($consultant->email)->subject('New consultation enquiry: '.$service->name)->html($body);
            });
        }

        $this->sendConsultantInquirySms($consultant, $service);

        return response()->json(['message' => 'Enquiry submitted successfully.']);
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
        $consultant = $this->resolveConsultant($slug);
        $approvedServices = ConsultantService::query()
            ->where('consultant_id', $consultant->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->get(['id', 'name']);

        return view('frontend.consultant.contact', [
            'consultant' => $consultant,
            'activeNav' => 'contact',
            'approvedServices' => $approvedServices,
        ]);
    }

    private function renderServiceCatalog(string $slug, ?Category $category = null, ?Category $subcategory = null): View
    {
        $consultant = $this->resolveConsultant($slug);
        $consultantCategories = $this->consultantCategories($consultant);

        $servicesQuery = ConsultantService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name'])
            ->where('consultant_id', $consultant->id)
            ->where('status', 'approved');

        if ($subcategory) {
            $servicesQuery->where('subcategory_id', $subcategory->id);
        } elseif ($category) {
            $servicesQuery->where('category_id', $category->id);
        }

        $approvedServices = $servicesQuery->latest('updated_at')->paginate(12)->withQueryString();
        $consultantRecentAds = $this->nearestConsultantModuleAds();

        if ($subcategory) {
            $pageTitle = $subcategory->name;
            $pageSubtitle = 'Consultation services in '.$subcategory->name.' · '.$category->name;
            $activeNav = 'subcategory';
        } elseif ($category) {
            $pageTitle = $category->name;
            $pageSubtitle = 'All consultation services listed under '.$category->name;
            $activeNav = 'category';
        } else {
            $pageTitle = 'All consultation services';
            $pageSubtitle = 'Browse the complete service catalog from '.$consultant->publicDisplayName();
            $activeNav = 'services';
        }

        return view('frontend.consultant.services', [
            'consultant' => $consultant,
            'preview' => false,
            'activeNav' => $activeNav,
            'pageTitle' => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'approvedServices' => $approvedServices,
            'consultantCategories' => $consultantCategories,
            'activeCategory' => $category,
            'activeSubcategory' => $subcategory,
            'consultantRecentAds' => $consultantRecentAds,
            'selectedCategoryNamesByConsultantAdId' => $this->resolveSelectedCategoryNamesByAdId($consultantRecentAds),
        ]);
    }

    private function consultantCategories(Consultant $consultant): Collection
    {
        $categoryIds = ConsultantService::query()
            ->where('consultant_id', $consultant->id)
            ->where('status', 'approved')
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique()
            ->filter();

        if ($categoryIds->isEmpty()) {
            return Category::query()
                ->whereNull('parent_id')
                ->whereJsonContains('modules', 'consultants')
                ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return Category::query()
            ->whereIn('id', $categoryIds)
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'consultants')
            ->with(['children' => function ($query) use ($consultant) {
                $subcategoryIds = ConsultantService::query()
                    ->where('consultant_id', $consultant->id)
                    ->where('status', 'approved')
                    ->whereNotNull('subcategory_id')
                    ->pluck('subcategory_id')
                    ->unique()
                    ->filter();

                $query
                    ->when($subcategoryIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $subcategoryIds))
                    ->orderBy('name')
                    ->select(['id', 'name', 'parent_id']);
            }])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function nearestConsultantModuleAds(int $limit = 20): Collection
    {
        [$lat, $lng] = $this->frontendCoordinates();

        $adsQuery = UserAd::query()
            ->with(['category:id,name'])
            ->where('status', 'approved')
            ->selectedForModule('consultants')
            ->whereDoesntHave('adSize', fn ($query) => $query->where('admin_only', true))
            ->whereNotNull('final_image')
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            });

        if ($lat !== null && $lng !== null) {
            $adsQuery
                ->select('user_ads.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km');
        }

        return $adsQuery
            ->latest('created_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{sponsoredFillers: array, sidebarAds: Collection, sectionAdRails: array<int, Collection>, randomFullPagePlacements: array}
     */
    private function loadStoreAds(Consultant $consultant, int $sectionCount, ?Category $category = null, ?Category $subcategory = null): array
    {
        if ($consultant->is_premium) {
            return [
                'sponsoredFillers' => [],
                'sidebarAds' => collect(),
                'sectionAdRails' => [],
                'randomFullPagePlacements' => [],
            ];
        }

        [$lat, $lng] = $this->frontendCoordinates();

        $adsService = app(MarketplaceAdsService::class);
        $storeAds = $adsService->getDisplayAds(24, $lat, $lng, ['consultants'], true);

        $requestedCategoryIds = collect([
            $category?->id,
            $subcategory?->id,
        ])->filter()->map(fn ($id) => (int) $id)->values();

        $consultantModuleAds = $storeAds->values();
        $categoryMatchedAds = $consultantModuleAds;

        if ($requestedCategoryIds->isNotEmpty()) {
            $categoryMatchedAds = $consultantModuleAds
                ->filter(function (UserAd $ad) use ($requestedCategoryIds): bool {
                    $selectedCategoryIds = collect($ad->selected_category_ids ?? [])->map(fn ($id) => (int) $id);
                    $selectedSubcategoryIds = collect($ad->selected_subcategory_ids ?? [])->map(fn ($id) => (int) $id);

                    return $selectedCategoryIds->intersect($requestedCategoryIds)->isNotEmpty()
                        || $selectedSubcategoryIds->intersect($requestedCategoryIds)->isNotEmpty();
                })
                ->values();
        }

        $effectiveAds = ($categoryMatchedAds->isNotEmpty() ? $categoryMatchedAds : $consultantModuleAds)->values();
        $split = $adsService->splitAdsForStoreLayout($effectiveAds, $sectionCount);

        return [
            'sponsoredFillers' => $adsService->getSponsoredFillers($lat, $lng, ['consultants'], true),
            'sidebarAds' => $split['sidebar'],
            'sectionAdRails' => $split['section_rails'],
            'randomFullPagePlacements' => $adsService->buildRandomPlacements($effectiveAds, $sectionCount),
        ];
    }

    private function resolveSelectedCategoryNamesByAdId(Collection $ads): array
    {
        $ads = $ads->values();
        if ($ads->isEmpty()) {
            return [];
        }

        $selectedCategoryIds = $ads
            ->flatMap(fn (UserAd $ad) => array_map('intval', $ad->selected_category_ids ?? []))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $categoryNamesById = Category::query()
            ->whereIn('id', $selectedCategoryIds)
            ->pluck('name', 'id');

        return $ads
            ->mapWithKeys(function (UserAd $ad) use ($categoryNamesById) {
                $selectedNames = collect($ad->selected_category_ids ?? [])
                    ->map(fn ($id) => $categoryNamesById->get((int) $id))
                    ->filter(fn ($name) => is_string($name) && $name !== '')
                    ->values()
                    ->all();

                if ($selectedNames === [] && $ad->category?->name) {
                    $selectedNames = [$ad->category->name];
                }

                return [$ad->id => $selectedNames];
            })
            ->all();
    }

    /**
     * @return array{0:?float, 1:?float}
     */
    private function frontendCoordinates(): array
    {
        $lat = request()->query('lat', session('frontend_lat'));
        $lng = request()->query('lng', request()->query('lang', session('frontend_lng')));

        return [
            is_numeric($lat) ? (float) $lat : null,
            is_numeric($lng) ? (float) $lng : null,
        ];
    }

    private function assertConsultantCategory(Category $category, bool $isSubcategory = false): void
    {
        abort_unless(in_array('consultants', $category->modules ?? [], true), 404);

        if (! $isSubcategory) {
            abort_unless($category->parent_id === null, 404);
        }
    }

    private function sendConsultantInquirySms(Consultant $consultant, ConsultantService $service): void
    {
        try {
            $user = User::select('phone_number')->where('id', $consultant->user_id)->first();
            $phoneNumber = $consultant->phone ?: $user?->phone_number;

            if (! $phoneNumber) {
                return;
            }

            $apikey = config('services.message.api_key');
            $username = config('services.message.username');
            $sender = config('services.message.sender', 'ANNUVE');
            $smstype = config('services.message.smstype');
            $peid = config('services.message.peid');

            $message = sprintf(
                'Hello %s, A new inquiry has been submitted for %s. Please log in to your consultant account to check and respond to the inquiry. Thank you – Annuvedant Team',
                $consultant->publicDisplayName(),
                $service->name
            );

            $url = 'http://sms.messageindia.in/v2/sendSMS?' . http_build_query([
                'username' => $username,
                'message' => $message,
                'sendername' => $sender,
                'smstype' => $smstype,
                'numbers' => $phoneNumber,
                'apikey' => $apikey,
                'peid' => $peid,
                'templateid' => 1707177936224680013,
            ]);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                Log::error('Consultant inquiry SMS failed', [
                    'phone' => $phoneNumber,
                    'error' => curl_error($curl),
                ]);

                curl_close($curl);

                return;
            }

            curl_close($curl);

            Log::info('Consultant inquiry SMS sent successfully', [
                'phone' => $phoneNumber,
                'response' => $response,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Exception while sending consultant inquiry SMS', [
                'consultant_id' => $consultant->id,
                'service_id' => $service->id,
                'error' => $exception->getMessage(),
            ]);
        }
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
