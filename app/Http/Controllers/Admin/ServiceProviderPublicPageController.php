<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ServiceProvider\ServiceProviderPublicPageController as OwnerServiceProviderPublicPageController;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderBannerSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceProviderPublicPageController extends OwnerServiceProviderPublicPageController
{
    protected ?ServiceProvider $editingServiceProvider = null;

    protected function isAdminEditor(): bool
    {
        return true;
    }

    protected function editorServiceProvider(?ServiceProvider $service_provider = null): ServiceProvider
    {
        if ($service_provider) {
            $this->editingServiceProvider = $service_provider;
        }

        abort_unless($this->editingServiceProvider, 404);

        return $this->editingServiceProvider;
    }

    protected function editorViewData(ServiceProvider $service_provider): array
    {
        return [
            'isAdmin' => true,
            'formAction' => route('admin.service_providers.public-page.update', $service_provider),
            'previewUrl' => route('admin.service_providers.public-page.editor-preview', $service_provider),
            'bannerDeleteBaseUrl' => url('admin/services/'.$service_provider->id.'/banner-slides').'/',
            'backUrl' => route('admin.service_providers.show', $service_provider),
            'editRedirectRoute' => 'admin.service_providers.public-page.edit',
            'editRedirectParams' => [$service_provider],
        ];
    }

    public function edit(?ServiceProvider $service_provider = null): View
    {
        abort_unless($service_provider, 404);

        return parent::edit($service_provider);
    }

    public function update(Request $request, ?ServiceProvider $service_provider = null): RedirectResponse|JsonResponse
    {
        abort_unless($service_provider, 404);

        return parent::update($request, $service_provider);
    }

    public function preview(?ServiceProvider $service_provider = null): View
    {
        abort_unless($service_provider, 404);

        return parent::preview($service_provider);
    }

    public function deleteBannerSlide(ServiceProviderBannerSlide $slide): JsonResponse
    {
        abort(404);
    }

    public function destroyBannerSlide(ServiceProvider $service_provider, ServiceProviderBannerSlide $slide): JsonResponse
    {
        abort_unless($slide->service_provider_id === $service_provider->id, 403);
        $slide->delete();

        return response()->json(['message' => 'Banner slide removed.']);
    }
}
