<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PortalNotificationService;
use App\Mail\ServiceProviderPublicPageApprovedMail;
use App\Mail\ServiceProviderStatusMail;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use App\Support\ServiceProviderFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ServiceProviderController extends Controller
{
    public function index(): View
    {
        return view('backend.service_providers.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ServiceProvider::query()
            ->with('user:id,name,email,phone_number,created_at')
            ->select([
                'id',
                'user_id',
                'company_name',
                'contact_person',
                'slug',
                'city',
                'state',
                'phone',
                'whatsapp',
                'pincode',
                'is_premium',
                'status',
                'public_page_status',
                'public_page_submitted_at',
                'published_page_data',
                'approved_at',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('owner_name', fn (ServiceProvider $service_provider) => e($service_provider->user?->name ?? '—'))
            ->addColumn('owner_email', fn (ServiceProvider $service_provider) => e($service_provider->user?->email ?? '—'))
            ->addColumn('contact_numbers', function (ServiceProvider $service_provider): string {
                $whatsapp = $service_provider->whatsapp ? '<span class="text-success">WA: '.e($service_provider->whatsapp).'</span>' : '<span class="text-muted">WA: —</span>';

                return '<div class="d-flex flex-column"><span>'.e($service_provider->phone ?? '—').'</span>'.$whatsapp.'</div>';
            })
            ->addColumn('location', function (ServiceProvider $service_provider): string {
                $parts = array_filter([$service_provider->city, $service_provider->state]);
                $location = $parts ? implode(', ', $parts) : '';
                if ($service_provider->pincode) {
                    $location = trim($location.' '.$service_provider->pincode);
                }

                return e($location ?: '—');
            })
            ->addColumn('status_badge', function (ServiceProvider $service_provider): string {
                $badge = match ($service_provider->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                };

                return '<span class="badge text-bg-'.$badge.'">'.ucfirst($service_provider->status).'</span>';
            })
            ->addColumn('public_page_link', function (ServiceProvider $service_provider): string {
                if ($service_provider->public_page_status === 'pending') {
                    return '<a class="service-page-link service-page-link--review" href="'.route('admin.service_providers.public-page.review', $service_provider).'" target="_blank" rel="noopener">'
                        .'<i class="fa-solid fa-up-right-from-square"></i><span>Review page</span></a>';
                }

                if (is_array($service_provider->published_page_data) || $service_provider->public_page_status === 'approved') {
                    $slug = data_get($service_provider->published_page_data, 'profile.slug', $service_provider->slug);

                    return '<a class="service-page-link" href="'.route('service_provider.show', $slug).'" target="_blank" rel="noopener">'
                        .'<i class="fa-solid fa-arrow-up-right-from-square"></i><span>View page</span></a>';
                }

                return '<span class="text-muted">—</span>';
            })
            ->addColumn('premium_toggle', function (ServiceProvider $service_provider): string {
                $checked = $service_provider->is_premium ? 'checked' : '';

                return '<div class="form-check form-switch mb-0 d-inline-flex">'
                    .'<input class="form-check-input js-premium-toggle" type="checkbox" role="switch" data-id="'.$service_provider->id.'" '.$checked.'>'
                    .'</div>';
            })
            ->editColumn('created_at', fn (ServiceProvider $service_provider) => $service_provider->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (ServiceProvider $service_provider): string {
                $approveBtn = $service_provider->status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve-service_provider" data-id="'.$service_provider->id.'" title="Approve"><i class="fa-solid fa-check"></i></button>'
                    : '';
                $rejectBtn = $service_provider->status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject-service_provider" data-id="'.$service_provider->id.'" title="Reject"><i class="fa-solid fa-ban"></i></button>'
                    : '';

                $pageApproveBtn = $service_provider->public_page_status === 'pending'
                    ? '<button type="button" class="btn btn-sm btn-primary js-approve-service-provider-page" data-id="'.$service_provider->id.'" title="Approve public page"><i class="fa-solid fa-globe"></i></button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.service_providers.show', $service_provider).'" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .$pageApproveBtn
                    .'<button type="button" class="btn btn-sm btn-outline-primary js-edit-service_provider" data-id="'.$service_provider->id.'" title="Edit"><i class="fa-solid fa-pen"></i></button>'
                    .$approveBtn
                    .$rejectBtn
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-service_provider" data-id="'.$service_provider->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if (str_contains($k, 'approve')) {
                    $query->where('status', 'approved');
                } elseif (str_contains($k, 'reject')) {
                    $query->where('status', 'rejected');
                } elseif (str_contains($k, 'pending')) {
                    $query->where('status', 'pending');
                }
            })
            ->rawColumns(['contact_numbers', 'status_badge', 'public_page_link', 'premium_toggle', 'actions'])
            ->make(true);
    }

    public function show(ServiceProvider $service_provider): View
    {
        $service_provider->load(['user', 'branches', 'bannerSlides', 'pageSections', 'approver:id,name']);

        return view('backend.service_providers.show', compact('service_provider'));
    }

    public function edit(ServiceProvider $service_provider): JsonResponse
    {
        $service_provider->load('user:id,name,email,phone_number');

        return response()->json([
            'service_provider' => [
                'id' => $service_provider->id,
                'company_name' => $service_provider->company_name,
                'contact_person' => $service_provider->contact_person,
                'slug' => $service_provider->slug,
                'display_name' => $service_provider->display_name,
                'phone' => $service_provider->phone,
                'whatsapp' => $service_provider->whatsapp,
                'email' => $service_provider->email,
                'address' => $service_provider->address,
                'city' => $service_provider->city,
                'state' => $service_provider->state,
                'pincode' => $service_provider->pincode,
                'pan_number' => $service_provider->pan_number,
                'has_gst' => $service_provider->gst_number ? '1' : '0',
                'gst_number' => $service_provider->gst_number,
                'government_certificate_number' => $service_provider->government_certificate_number,
                'description' => $service_provider->description,
                'status' => $service_provider->status,
                'owner_name' => $service_provider->user?->name,
                'owner_email' => $service_provider->user?->email,
                'owner_phone' => $service_provider->user?->phone_number,
            ],
        ]);
    }

    public function update(Request $request, ServiceProvider $service_provider): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('service_providers', 'slug')->ignore($service_provider->id)],
            'display_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'pan_number' => ['required', 'string', 'max:20'],
            'has_gst' => ['required', 'in:0,1'],
            'gst_number' => ['nullable', 'required_if:has_gst,1', 'string', 'max:20'],
            'government_certificate_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        if ($validated['has_gst'] !== '1') {
            $validated['gst_number'] = null;
        }
        unset($validated['has_gst']);

        $originalStatus = $service_provider->status;

        if ($validated['status'] === 'approved' && $service_provider->status !== 'approved') {
            $validated['approved_at'] = now();
            $validated['approved_by'] = $request->user()->id;
        }

        if ($validated['status'] !== 'approved') {
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;
        }

        $service_provider->update($validated);

        if ($service_provider->status !== $originalStatus && in_array($service_provider->status, ['approved', 'rejected'], true)) {
            $this->sendServiceProviderStatusMail($service_provider, $service_provider->status);
            PortalNotificationService::notifyOwnerOfReview($service_provider->user, 'Service account', $service_provider->company_name, $service_provider->status, route('service_provider.dashboard'));
        }

        return response()->json(['message' => 'Service updated successfully.']);
    }

    public function approve(Request $request, ServiceProvider $service_provider): JsonResponse
    {
        $service_provider->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $emailSent = $this->sendServiceProviderStatusMail($service_provider, 'approved');
        PortalNotificationService::notifyOwnerOfReview($service_provider->user, 'Service account', $service_provider->company_name, 'approved', route('service_provider.dashboard'));
        return response()->json([
            'message' => 'Service approved. They can now log in to the service portal.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function reviewPublicPage(ServiceProvider $service_provider): View
    {
        abort_unless($service_provider->public_page_status === 'pending' && is_array($service_provider->pending_page_data), 404);

        return view('backend.service_providers.public-page-review', compact('service_provider'));
    }

    public function previewPublicPage(ServiceProvider $service_provider): View
    {
        abort_unless($service_provider->public_page_status === 'pending' && is_array($service_provider->pending_page_data), 404);

        $service_provider->load(['branches', 'bannerSlides', 'pageSections'])->usePendingPage();
        $approvedServices = ServiceProviderService::query()
            ->where('service_provider_id', $service_provider->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->get();

        return view('frontend.service_provider.show', [
            'service_provider' => $service_provider,
            'preview' => true,
            'activeNav' => 'home',
            'approvedServices' => $approvedServices,
            'service_providerRecentAds' => collect(),
            'selectedCategoryNamesByServiceProviderAdId' => [],
            'fullPageAds' => collect(),
            'supportingAds' => collect(),
        ]);
    }

    public function approvePublicPage(Request $request, ServiceProvider $service_provider): JsonResponse
    {
        abort_unless($service_provider->public_page_status === 'pending' && is_array($service_provider->pending_page_data), 422, 'There is no public page submission awaiting approval.');

        $service_provider->update([
            'published_page_data' => $service_provider->pending_page_data,
            'pending_page_data' => null,
            'public_page_status' => 'approved',
            'public_page_approved_at' => now(),
            'public_page_approved_by' => $request->user()->id,
        ]);

        $recipient = $service_provider->user?->email ?: $service_provider->email;
        if ($recipient) {
            Mail::to($recipient)->send(new ServiceProviderPublicPageApprovedMail($service_provider->fresh('user')));
        }

        PortalNotificationService::notifyOwnerOfReview($service_provider->user, 'Public page', $service_provider->display_name ?: $service_provider->company_name, 'approved', route('service_provider.public-page.edit'));

        return response()->json([
            'message' => 'Public page approved and published.'.($recipient ? ' Email notification sent.' : ''),
            'redirect_url' => route('admin.service_providers.index'),
        ]);
    }

    public function declinePublicPage(ServiceProvider $service_provider): JsonResponse
    {
        abort_unless($service_provider->public_page_status === 'pending' && is_array($service_provider->pending_page_data), 422, 'There is no public page submission awaiting review.');

        $service_provider->update([
            'pending_page_data' => null,
            'public_page_status' => 'declined',
            'public_page_submitted_at' => null,
        ]);

        PortalNotificationService::notifyOwnerOfReview($service_provider->user, 'Public page', $service_provider->display_name ?: $service_provider->company_name, 'declined', route('service_provider.public-page.edit'));

        return response()->json([
            'message' => 'Public page changes declined. The previous approved page remains live.',
            'redirect_url' => route('admin.service_providers.index'),
        ]);
    }

    public function reject(ServiceProvider $service_provider): JsonResponse
    {
        $service_provider->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $emailSent = $this->sendServiceProviderStatusMail($service_provider, 'rejected');
        PortalNotificationService::notifyOwnerOfReview($service_provider->user, 'Service account', $service_provider->company_name, 'rejected', route('login'));

        return response()->json([
            'message' => 'Service application rejected.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function destroy(ServiceProvider $service_provider): JsonResponse
    {
        $service_provider->loadMissing('user');
        $recipient = $this->service_providerNotificationRecipient($service_provider);
        $mail = $recipient ? ServiceProviderStatusMail::forServiceProvider($service_provider, 'deleted') : null;

        DB::transaction(function () use ($service_provider): void {
            $userId = $service_provider->user_id;
            foreach ($service_provider->bannerSlides as $slide) {
                ServiceProviderFileUploader::deleteIfExists($slide->image_path);
            }
            foreach ($service_provider->pageSections as $section) {
                ServiceProviderFileUploader::deleteIfExists($section->image_path);
            }
            ServiceProviderFileUploader::deleteIfExists($service_provider->logo);
            if (is_array($service_provider->gallery)) {
                foreach ($service_provider->gallery as $path) {
                    ServiceProviderFileUploader::deleteIfExists($path);
                }
            }
            $service_provider->delete();
            User::whereKey($userId)->where('role', 'service_provider')->delete();
        });

        if ($recipient && $mail) {
            Mail::to($recipient)->send($mail);
        }

        return response()->json([
            'message' => 'Service deleted successfully.'.($recipient && $mail ? ' Email notification sent.' : ''),
        ]);
    }

    private function sendServiceProviderStatusMail(ServiceProvider $service_provider, string $action): bool
    {
        $recipient = $this->service_providerNotificationRecipient($service_provider);

        if (! $recipient) {
            return false;
        }

        Mail::to($recipient)->send(ServiceProviderStatusMail::forServiceProvider($service_provider, $action));
        return true;
    }

    private function service_providerNotificationRecipient(ServiceProvider $service_provider): ?string
    {
        $service_provider->loadMissing('user');

        return $service_provider->email ?: $service_provider->user?->email;
    }

    public function togglePremium(ServiceProvider $service_provider): JsonResponse
    {
        $service_provider->update([
            'is_premium' => ! $service_provider->is_premium,
        ]);

        return response()->json([
            'message' => $service_provider->is_premium ? 'Service marked as premium.' : 'Service removed from premium.',
            'is_premium' => $service_provider->is_premium,
        ]);
    }
}
