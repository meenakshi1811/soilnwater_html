<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ConsultantPublicPageApprovedMail;
use App\Mail\ConsultantStatusMail;
use App\Models\User;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Support\ConsultantFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ConsultantController extends Controller
{
    public function index(): View
    {
        return view('backend.consultants.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = Consultant::query()
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
            ->addColumn('owner_name', fn (Consultant $consultant) => e($consultant->user?->name ?? '—'))
            ->addColumn('owner_email', fn (Consultant $consultant) => e($consultant->user?->email ?? '—'))
            ->addColumn('contact_numbers', function (Consultant $consultant): string {
                $whatsapp = $consultant->whatsapp ? '<span class="text-success">WA: '.e($consultant->whatsapp).'</span>' : '<span class="text-muted">WA: —</span>';

                return '<div class="d-flex flex-column"><span>'.e($consultant->phone ?? '—').'</span>'.$whatsapp.'</div>';
            })
            ->addColumn('location', function (Consultant $consultant): string {
                $parts = array_filter([$consultant->city, $consultant->state]);
                $location = $parts ? implode(', ', $parts) : '';
                if ($consultant->pincode) {
                    $location = trim($location.' '.$consultant->pincode);
                }

                return e($location ?: '—');
            })
            ->addColumn('status_badge', function (Consultant $consultant): string {
                $badge = match ($consultant->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                };

                return '<span class="badge text-bg-'.$badge.'">'.ucfirst($consultant->status).'</span>';
            })
            ->addColumn('public_page_link', function (Consultant $consultant): string {
                if ($consultant->public_page_status === 'pending') {
                    return '<a class="service-page-link service-page-link--review" href="'.route('admin.consultants.public-page.review', $consultant).'" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i><span>Review page</span></a>';
                }

                if (is_array($consultant->published_page_data) || $consultant->public_page_status === 'approved') {
                    $slug = data_get($consultant->published_page_data, 'profile.slug', $consultant->slug);

                    return '<a class="service-page-link" href="'.route('consultant.show', $slug).'" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>View page</span></a>';
                }

                return '<span class="text-muted">—</span>';
            })
            ->addColumn('premium_toggle', function (Consultant $consultant): string {
                $checked = $consultant->is_premium ? 'checked' : '';

                return '<div class="form-check form-switch mb-0 d-inline-flex">'
                    .'<input class="form-check-input js-premium-toggle" type="checkbox" role="switch" data-id="'.$consultant->id.'" '.$checked.'>'
                    .'</div>';
            })
            ->editColumn('created_at', fn (Consultant $consultant) => $consultant->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (Consultant $consultant): string {
                $approveBtn = $consultant->status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve-consultant" data-id="'.$consultant->id.'" title="Approve"><i class="fa-solid fa-check"></i></button>'
                    : '';
                $rejectBtn = $consultant->status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject-consultant" data-id="'.$consultant->id.'" title="Reject"><i class="fa-solid fa-ban"></i></button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.consultants.show', $consultant).'" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .'<button type="button" class="btn btn-sm btn-outline-primary js-edit-consultant" data-id="'.$consultant->id.'" title="Edit"><i class="fa-solid fa-pen"></i></button>'
                    .$approveBtn
                    .$rejectBtn
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-consultant" data-id="'.$consultant->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
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

    public function show(Consultant $consultant): View
    {
        $consultant->load(['user', 'branches', 'bannerSlides', 'pageSections', 'approver:id,name']);

        return view('backend.consultants.show', compact('consultant'));
    }

    public function edit(Consultant $consultant): JsonResponse
    {
        $consultant->load('user:id,name,email,phone_number');

        return response()->json([
            'consultant' => [
                'id' => $consultant->id,
                'company_name' => $consultant->company_name,
                'contact_person' => $consultant->contact_person,
                'slug' => $consultant->slug,
                'display_name' => $consultant->display_name,
                'phone' => $consultant->phone,
                'whatsapp' => $consultant->whatsapp,
                'email' => $consultant->email,
                'address' => $consultant->address,
                'city' => $consultant->city,
                'state' => $consultant->state,
                'pincode' => $consultant->pincode,
                'pan_number' => $consultant->pan_number,
                'has_gst' => $consultant->gst_number ? '1' : '0',
                'gst_number' => $consultant->gst_number,
                'government_certificate_number' => $consultant->government_certificate_number,
                'description' => $consultant->description,
                'status' => $consultant->status,
                'owner_name' => $consultant->user?->name,
                'owner_email' => $consultant->user?->email,
                'owner_phone' => $consultant->user?->phone_number,
            ],
        ]);
    }

    public function update(Request $request, Consultant $consultant): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('consultants', 'slug')->ignore($consultant->id)],
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

        $originalStatus = $consultant->status;

        if ($validated['status'] === 'approved' && $consultant->status !== 'approved') {
            $validated['approved_at'] = now();
            $validated['approved_by'] = $request->user()->id;
        }

        if ($validated['status'] !== 'approved') {
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;
        }

        $consultant->update($validated);

        if ($consultant->status !== $originalStatus && in_array($consultant->status, ['approved', 'rejected'], true)) {
            $this->sendConsultantStatusMail($consultant, $consultant->status);
        }

        return response()->json(['message' => 'Consultant updated successfully.']);
    }

    public function approve(Request $request, Consultant $consultant): JsonResponse
    {
        $consultant->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $emailSent = $this->sendConsultantStatusMail($consultant, 'approved');
        return response()->json([
            'message' => 'Consultant approved. They can now log in to the consultant portal.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function reviewPublicPage(Consultant $consultant): View
    {
        abort_unless($consultant->public_page_status === 'pending' && is_array($consultant->pending_page_data), 404);

        return view('backend.consultants.public-page-review', compact('consultant'));
    }

    public function previewPublicPage(Consultant $consultant): View
    {
        abort_unless($consultant->public_page_status === 'pending' && is_array($consultant->pending_page_data), 404);

        $consultant->load(['branches', 'bannerSlides', 'pageSections'])->usePendingPage();
        $approvedServices = ConsultantService::query()
            ->where('consultant_id', $consultant->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->get();

        return view('frontend.consultant.show', [
            'consultant' => $consultant,
            'preview' => true,
            'activeNav' => 'home',
            'approvedServices' => $approvedServices,
            'consultantRecentAds' => collect(),
            'selectedCategoryNamesByConsultantAdId' => [],
            'fullPageAds' => collect(),
            'supportingAds' => collect(),


        ]);
    }

    public function approvePublicPage(Request $request, Consultant $consultant): JsonResponse
    {
        abort_unless($consultant->public_page_status === 'pending' && is_array($consultant->pending_page_data), 422, 'There is no public page submission awaiting approval.');

        $consultant->update([
            'published_page_data' => $consultant->pending_page_data,
            'pending_page_data' => null,
            'public_page_status' => 'approved',
            'public_page_approved_at' => now(),
            'public_page_approved_by' => $request->user()->id,
        ]);

        $recipient = $consultant->user?->email ?: $consultant->email;
        if ($recipient) {
            Mail::to($recipient)->send(new ConsultantPublicPageApprovedMail($consultant->fresh('user')));
        }

        return response()->json([
            'message' => 'Public page approved and published.'.($recipient ? ' Email notification sent.' : ''),
            'redirect_url' => route('admin.consultants.index'),
        ]);
    }

    public function declinePublicPage(Consultant $consultant): JsonResponse
    {
        abort_unless($consultant->public_page_status === 'pending' && is_array($consultant->pending_page_data), 422, 'There is no public page submission awaiting review.');

        $consultant->update([
            'pending_page_data' => null,
            'public_page_status' => 'declined',
            'public_page_submitted_at' => null,
        ]);

        return response()->json([
            'message' => 'Public page changes declined. The previous approved page remains live.',
            'redirect_url' => route('admin.consultants.index'),
        ]);
    }

    public function reject(Consultant $consultant): JsonResponse
    {
        $consultant->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $emailSent = $this->sendConsultantStatusMail($consultant, 'rejected');

        return response()->json([
            'message' => 'Consultant application rejected.'.($emailSent ? ' Email notification sent.' : ''),
        ]);
    }

    public function destroy(Consultant $consultant): JsonResponse
    {
        $consultant->loadMissing('user');
        $recipient = $this->consultantNotificationRecipient($consultant);
        $mail = $recipient ? ConsultantStatusMail::forConsultant($consultant, 'deleted') : null;

        DB::transaction(function () use ($consultant): void {
            $userId = $consultant->user_id;
            foreach ($consultant->bannerSlides as $slide) {
                ConsultantFileUploader::deleteIfExists($slide->image_path);
            }
            foreach ($consultant->pageSections as $section) {
                ConsultantFileUploader::deleteIfExists($section->image_path);
            }
            ConsultantFileUploader::deleteIfExists($consultant->logo);
            if (is_array($consultant->gallery)) {
                foreach ($consultant->gallery as $path) {
                    ConsultantFileUploader::deleteIfExists($path);
                }
            }
            $consultant->delete();
            User::whereKey($userId)->where('role', 'consultant')->delete();
        });

        if ($recipient && $mail) {
            Mail::to($recipient)->send($mail);
        }

        return response()->json([
            'message' => 'Consultant deleted successfully.'.($recipient && $mail ? ' Email notification sent.' : ''),
        ]);
    }

    private function sendConsultantStatusMail(Consultant $consultant, string $action): bool
    {
        $recipient = $this->consultantNotificationRecipient($consultant);

        if (! $recipient) {
            return false;
        }

        Mail::to($recipient)->send(ConsultantStatusMail::forConsultant($consultant, $action));
        return true;
    }

    private function consultantNotificationRecipient(Consultant $consultant): ?string
    {
        $consultant->loadMissing('user');

        return $consultant->email ?: $consultant->user?->email;
    }

    public function togglePremium(Consultant $consultant): JsonResponse
    {
        $consultant->update([
            'is_premium' => ! $consultant->is_premium,
        ]);

        return response()->json([
            'message' => $consultant->is_premium ? 'Consultant marked as premium.' : 'Consultant removed from premium.',
            'is_premium' => $consultant->is_premium,
        ]);
    }
}
