<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\ConsultantServiceInquiry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        return view('frontend.consultant.show', [
            'consultant' => $consultant,
            'preview' => false,
            'activeNav' => 'home',
            'approvedServices' => $approvedServices,
            'consultantRecentAds' => collect(),
            'selectedCategoryNamesByConsultantAdId' => [],
        ]);
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
