<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Vendor\VendorPublicPageController as OwnerVendorPublicPageController;
use App\Models\Vendor;
use App\Models\VendorBannerSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorPublicPageController extends OwnerVendorPublicPageController
{
    protected ?Vendor $editingVendor = null;

    protected function isAdminEditor(): bool
    {
        return true;
    }

    protected function editorVendor(?Vendor $vendor = null): Vendor
    {
        if ($vendor) {
            $this->editingVendor = $vendor;
        }

        abort_unless($this->editingVendor, 404);

        return $this->editingVendor;
    }

    protected function editorViewData(Vendor $vendor): array
    {
        return [
            'isAdmin' => true,
            'formAction' => route('admin.vendors.public-page.update', $vendor),
            'previewUrl' => route('admin.vendors.public-page.editor-preview', $vendor),
            'bannerDeleteBaseUrl' => url('admin/vendors/'.$vendor->id.'/banner-slides').'/',
            'backUrl' => route('admin.vendors.show', $vendor),
            'editRedirectRoute' => 'admin.vendors.public-page.edit',
            'editRedirectParams' => [$vendor],
        ];
    }

    public function edit(?Vendor $vendor = null): View
    {
        abort_unless($vendor, 404);

        return parent::edit($vendor);
    }

    public function update(Request $request, ?Vendor $vendor = null): RedirectResponse|JsonResponse
    {
        abort_unless($vendor, 404);

        return parent::update($request, $vendor);
    }

    public function preview(?Vendor $vendor = null): View
    {
        abort_unless($vendor, 404);

        return parent::preview($vendor);
    }

    public function deleteBannerSlide(VendorBannerSlide $slide): JsonResponse
    {
        abort(404);
    }

    public function destroyBannerSlide(Vendor $vendor, VendorBannerSlide $slide): JsonResponse
    {
        abort_unless($slide->vendor_id === $vendor->id, 403);
        $slide->delete();

        return response()->json(['message' => 'Banner slide removed.']);
    }
}
