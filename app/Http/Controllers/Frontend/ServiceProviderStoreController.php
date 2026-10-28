<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use App\Models\ServiceProviderServiceInquiry;
use App\Models\User;
use App\Models\UserAd;
use App\Services\MarketplaceAdsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ServiceProviderStoreController extends Controller
{
    public function show(string $slug): View
    {
        $service_provider = $this->resolveServiceProvider($slug);

        $approvedServices = ServiceProviderService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name'])
            ->where('service_provider_id', $service_provider->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->get();

        $adsContext = $this->loadStoreAds($service_provider);
        $service_providerRecentAds = $service_provider->is_premium ? collect() : $this->nearestServiceProviderModuleAds();

        return view('frontend.service_provider.show', [
            'service_provider' => $service_provider,
            'preview' => false,
            'activeNav' => 'home',
            'approvedServices' => $approvedServices,
            'service_providerRecentAds' => $service_providerRecentAds,
            'selectedCategoryNamesByServiceProviderAdId' => $this->resolveSelectedCategoryNamesByAdId($service_providerRecentAds),
            'fullPageAds' => $adsContext['fullPageAds'],
            'supportingAds' => $adsContext['supportingAds'],
        ]);
    }



    // The profile is one page now: its old services, about and contact pages lead to its sections

    public function services(string $slug): RedirectResponse
    {
        return $this->redirectToSection($slug, 'biz-services');
    }

    public function categoryServices(string $slug, Category $category): RedirectResponse
    {
        return $this->redirectToSection($slug, 'biz-services', $category->id);
    }

    public function subcategoryServices(string $slug, Category $category, Category $subcategory): RedirectResponse
    {
        return $this->redirectToSection($slug, 'biz-services', $category->id);
    }

    public function sendServiceInquiry(Request $request, string $slug, ServiceProviderService $service): JsonResponse
    {
        $service_provider = $this->resolveServiceProvider($slug);
        abort_unless($service->service_provider_id === $service_provider->id && $service->status === 'approved', 404);

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
            $path = $request->file('image')->store('uploads/service_provider-inquiries', 'public');
            $data['image_path'] = 'storage/'.$path;
        }

        $inquiry = ServiceProviderServiceInquiry::query()->create([
            'service_provider_id' => $service_provider->id,
            'service_provider_service_id' => $service->id,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        if ($service_provider->email) {
            $subjectLine = 'New services enquiry: '.$service->name;
            $body = view('emails.service_provider.new-inquiry', compact('inquiry', 'service_provider', 'service', 'subjectLine'))->render();
            Mail::send([], [], function ($message) use ($service_provider, $subjectLine, $body) {
                $message->to($service_provider->email)->subject($subjectLine)->html($body);
            });
        }

        $this->sendServiceProviderInquirySms($service_provider, $service);

        return response()->json(['message' => 'Enquiry submitted successfully.']);
    }

    public function sendGeneralInquiry(Request $request, string $slug): JsonResponse
    {
        $service_provider = $this->resolveServiceProvider($slug);

        if (! $request->user()) {
            return response()->json(['message' => 'Please login to send an enquiry.'], 403);
        }

        $data = $request->validate([
            'service_provider_service_id' => ['required', 'integer'],
            'client_name' => ['required', 'string', 'max:120'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'question' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $service = ServiceProviderService::query()
            ->where('id', $data['service_provider_service_id'])
            ->where('service_provider_id', $service_provider->id)
            ->where('status', 'approved')
            ->firstOrFail();

        unset($data['service_provider_service_id']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/service_provider-inquiries', 'public');
            $data['image_path'] = 'storage/'.$path;
        }

        $inquiry = ServiceProviderServiceInquiry::query()->create([
            'service_provider_id' => $service_provider->id,
            'service_provider_service_id' => $service->id,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        if ($service_provider->email) {
            $subjectLine = 'New services enquiry: '.$service->name;
            $body = view('emails.service_provider.new-inquiry', compact('inquiry', 'service_provider', 'service', 'subjectLine'))->render();
            Mail::send([], [], function ($message) use ($service_provider, $subjectLine, $body) {
                $message->to($service_provider->email)->subject($subjectLine)->html($body);
            });
        }

        $this->sendServiceProviderInquirySms($service_provider, $service);

        return response()->json(['message' => 'Enquiry submitted successfully.']);
    }

    public function about(string $slug): RedirectResponse
    {
        return $this->redirectToSection($slug, 'biz-about');
    }

    public function contact(string $slug): RedirectResponse
    {
        return $this->redirectToSection($slug, 'biz-contact');
    }

    private function redirectToSection(string $slug, string $section, ?int $categoryId = null): RedirectResponse
    {
        $service_provider = $this->resolveServiceProvider($slug);

        return redirect()->to(route('service_provider.show', array_filter([
            'slug' => $service_provider->slug,
            'category' => $categoryId,
        ])).'#'.$section);
    }

    private function nearestServiceProviderModuleAds(int $limit = 20): Collection
    {
        [$lat, $lng] = $this->frontendCoordinates();

        $adsQuery = UserAd::query()
            ->with(['category:id,name'])
            ->where('status', 'approved')
            ->selectedForModule('service_providers')
            ->whereDoesntHave('adSize', fn ($query) => $query->where('admin_only', true))
            ->where('size_type', '!=', 'square')
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
     * @return array{fullPageAds: Collection, supportingAds: Collection}
     */
    private function loadStoreAds(ServiceProvider $service_provider, ?Category $category = null, ?Category $subcategory = null): array
    {
        if ($service_provider->is_premium) {
            return [
                'fullPageAds' => collect(),
                'supportingAds' => collect(),
            ];
        }

        [$lat, $lng] = $this->frontendCoordinates();

        $adsService = app(MarketplaceAdsService::class);
        $storeAds = $adsService->getDisplayAds(24, $lat, $lng, ['service_providers'], true);

        $requestedCategoryIds = collect([
            $category?->id,
            $subcategory?->id,
        ])->filter()->map(fn ($id) => (int) $id)->values();

        $serviceProviderModuleAds = $storeAds->values();
        $categoryMatchedAds = $serviceProviderModuleAds;

        if ($requestedCategoryIds->isNotEmpty()) {
            $categoryMatchedAds = $serviceProviderModuleAds
                ->filter(function (UserAd $ad) use ($requestedCategoryIds): bool {
                    $selectedCategoryIds = collect($ad->selected_category_ids ?? [])->map(fn ($id) => (int) $id);
                    $selectedSubcategoryIds = collect($ad->selected_subcategory_ids ?? [])->map(fn ($id) => (int) $id);

                    return $selectedCategoryIds->intersect($requestedCategoryIds)->isNotEmpty()
                        || $selectedSubcategoryIds->intersect($requestedCategoryIds)->isNotEmpty();
                })
                ->values();
        }

        $effectiveAds = ($categoryMatchedAds->isNotEmpty() ? $categoryMatchedAds : $serviceProviderModuleAds)->values();
        $placements = $adsService->splitServicePageAds($effectiveAds);

        return [
            'fullPageAds' => $placements['full_page'],
            'supportingAds' => $placements['supporting'],
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

    private function sendServiceProviderInquirySms(ServiceProvider $service_provider, ServiceProviderService $service): void
    {
        try {
            $user = User::select('phone_number')->where('id', $service_provider->user_id)->first();
            $phoneNumber = $service_provider->phone ?: $user?->phone_number;

            if (! $phoneNumber) {
                return;
            }

            $apikey = config('services.message.api_key');
            $username = config('services.message.username');
            $sender = config('services.message.sender', 'ANNUVE');
            $smstype = config('services.message.smstype');
            $peid = config('services.message.peid');

            $message = sprintf(
                'Hello %s, A new inquiry has been submitted for %s. Please log in to your Services account to check and respond to the inquiry. Thank you - Annuvedant Team',
                $service_provider->publicDisplayName(),
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
                'templateid' => 1707178066078642986,
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
                Log::error('ServiceProvider inquiry SMS failed', [
                    'phone' => $phoneNumber,
                    'error' => curl_error($curl),
                ]);

                curl_close($curl);

                return;
            }

            curl_close($curl);

            Log::info('ServiceProvider inquiry SMS sent successfully', [
                'phone' => $phoneNumber,
                'response' => $response,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Exception while sending service_provider inquiry SMS', [
                'service_provider_id' => $service_provider->id,
                'service_id' => $service->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveServiceProvider(string $slug): ServiceProvider
    {
        return ServiceProvider::query()
            ->where(function ($query) use ($slug): void {
                $query->where('slug', $slug)
                    ->orWhere('published_page_data->profile->slug', $slug);
            })
            ->where('status', 'approved')
            ->publiclyVisible()
            ->with(['branches', 'bannerSlides', 'pageSections'])
            ->firstOrFail()
            ->usePublishedPage();
    }
}
