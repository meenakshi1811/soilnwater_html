<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use App\Support\VendorFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class VendorController extends Controller
{
    public function index(): View
    {
        return view('backend.vendors.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = Vendor::query()
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
                'approved_at',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('owner_name', fn (Vendor $vendor) => e($vendor->user?->name ?? '—'))
            ->addColumn('owner_email', fn (Vendor $vendor) => e($vendor->user?->email ?? '—'))
            ->addColumn('contact_numbers', function (Vendor $vendor): string {
                $whatsapp = $vendor->whatsapp ? '<span class="text-success">WA: '.e($vendor->whatsapp).'</span>' : '<span class="text-muted">WA: —</span>';

                return '<div class="d-flex flex-column"><span>'.e($vendor->phone ?? '—').'</span>'.$whatsapp.'</div>';
            })
            ->addColumn('location', function (Vendor $vendor): string {
                $parts = array_filter([$vendor->city, $vendor->state]);
                $location = $parts ? implode(', ', $parts) : '';
                if ($vendor->pincode) {
                    $location = trim($location.' '.$vendor->pincode);
                }

                return e($location ?: '—');
            })
            ->addColumn('status_badge', function (Vendor $vendor): string {
                $badge = match ($vendor->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                };

                return '<span class="badge text-bg-'.$badge.'">'.ucfirst($vendor->status).'</span>';
            })
            ->addColumn('premium_toggle', function (Vendor $vendor): string {
                $checked = $vendor->is_premium ? 'checked' : '';

                return '<div class="form-check form-switch mb-0 d-inline-flex">'
                    .'<input class="form-check-input js-premium-toggle" type="checkbox" role="switch" data-id="'.$vendor->id.'" '.$checked.'>'
                    .'</div>';
            })
            ->editColumn('created_at', fn (Vendor $vendor) => $vendor->created_at?->format('Y-m-d') ?? '')
            ->addColumn('actions', function (Vendor $vendor): string {
                $approveBtn = $vendor->status !== 'approved'
                    ? '<button type="button" class="btn btn-sm btn-success js-approve-vendor" data-id="'.$vendor->id.'" title="Approve"><i class="fa-solid fa-check"></i></button>'
                    : '';
                $rejectBtn = $vendor->status !== 'rejected'
                    ? '<button type="button" class="btn btn-sm btn-outline-warning js-reject-vendor" data-id="'.$vendor->id.'" title="Reject"><i class="fa-solid fa-ban"></i></button>'
                    : '';

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.vendors.show', $vendor).'" class="btn btn-sm btn-outline-secondary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .'<button type="button" class="btn btn-sm btn-outline-primary js-edit-vendor" data-id="'.$vendor->id.'" title="Edit"><i class="fa-solid fa-pen"></i></button>'
                    .$approveBtn
                    .$rejectBtn
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-vendor" data-id="'.$vendor->id.'" title="Delete"><i class="fa-solid fa-trash"></i></button>'
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
            ->rawColumns(['contact_numbers', 'status_badge', 'premium_toggle', 'actions'])
            ->make(true);
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load(['user', 'branches', 'bannerSlides', 'pageSections', 'approver:id,name']);

        return view('backend.vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor): JsonResponse
    {
        $vendor->load('user:id,name,email,phone_number');

        return response()->json([
            'vendor' => [
                'id' => $vendor->id,
                'company_name' => $vendor->company_name,
                'contact_person' => $vendor->contact_person,
                'slug' => $vendor->slug,
                'display_name' => $vendor->display_name,
                'phone' => $vendor->phone,
                'whatsapp' => $vendor->whatsapp,
                'email' => $vendor->email,
                'address' => $vendor->address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'pincode' => $vendor->pincode,
                'pan_number' => $vendor->pan_number,
                'gst_number' => $vendor->gst_number,
                'description' => $vendor->description,
                'status' => $vendor->status,
                'owner_name' => $vendor->user?->name,
                'owner_email' => $vendor->user?->email,
                'owner_phone' => $vendor->user?->phone_number,
            ],
        ]);
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('vendors', 'slug')->ignore($vendor->id)],
            'display_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        if ($validated['status'] === 'approved' && $vendor->status !== 'approved') {
            $validated['approved_at'] = now();
            $validated['approved_by'] = $request->user()->id;
        }

        if ($validated['status'] !== 'approved') {
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;
        }

        $vendor->update($validated);

        return response()->json(['message' => 'Vendor updated successfully.']);
    }

    public function approve(Request $request, Vendor $vendor): JsonResponse
    {
        $vendor->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Vendor approved. They can now log in to the vendor portal.']);
    }

    public function reject(Vendor $vendor): JsonResponse
    {
        $vendor->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return response()->json(['message' => 'Vendor application rejected.']);
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        DB::transaction(function () use ($vendor): void {
            $userId = $vendor->user_id;
            foreach ($vendor->bannerSlides as $slide) {
                VendorFileUploader::deleteIfExists($slide->image_path);
            }
            foreach ($vendor->pageSections as $section) {
                VendorFileUploader::deleteIfExists($section->image_path);
            }
            VendorFileUploader::deleteIfExists($vendor->logo);
            if (is_array($vendor->gallery)) {
                foreach ($vendor->gallery as $path) {
                    VendorFileUploader::deleteIfExists($path);
                }
            }
            $vendor->delete();
            User::whereKey($userId)->where('role', 'vendor')->delete();
        });

        return response()->json(['message' => 'Vendor deleted successfully.']);
    }

    public function togglePremium(Vendor $vendor): JsonResponse
    {
        $vendor->update([
            'is_premium' => ! $vendor->is_premium,
        ]);

        return response()->json([
            'message' => $vendor->is_premium ? 'Vendor marked as premium.' : 'Vendor removed from premium.',
            'is_premium' => $vendor->is_premium,
        ]);
    }
}
