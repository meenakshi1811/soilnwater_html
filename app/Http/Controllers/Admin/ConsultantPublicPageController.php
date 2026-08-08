<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Consultant\ConsultantPublicPageController as OwnerConsultantPublicPageController;
use App\Models\Consultant;
use App\Models\ConsultantBannerSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultantPublicPageController extends OwnerConsultantPublicPageController
{
    protected ?Consultant $editingConsultant = null;

    protected function isAdminEditor(): bool
    {
        return true;
    }

    protected function editorConsultant(?Consultant $consultant = null): Consultant
    {
        if ($consultant) {
            $this->editingConsultant = $consultant;
        }

        abort_unless($this->editingConsultant, 404);

        return $this->editingConsultant;
    }

    protected function editorViewData(Consultant $consultant): array
    {
        return [
            'isAdmin' => true,
            'formAction' => route('admin.consultants.public-page.update', $consultant),
            'previewUrl' => route('admin.consultants.public-page.editor-preview', $consultant),
            'bannerDeleteBaseUrl' => url('admin/consultants/'.$consultant->id.'/banner-slides').'/',
            'backUrl' => route('admin.consultants.show', $consultant),
            'editRedirectRoute' => 'admin.consultants.public-page.edit',
            'editRedirectParams' => [$consultant],
        ];
    }

    public function edit(?Consultant $consultant = null): View
    {
        abort_unless($consultant, 404);

        return parent::edit($consultant);
    }

    public function update(Request $request, ?Consultant $consultant = null): RedirectResponse|JsonResponse
    {
        abort_unless($consultant, 404);

        return parent::update($request, $consultant);
    }

    public function preview(?Consultant $consultant = null): View
    {
        abort_unless($consultant, 404);

        return parent::preview($consultant);
    }

    public function deleteBannerSlide(ConsultantBannerSlide $slide): JsonResponse
    {
        abort(404);
    }

    public function destroyBannerSlide(Consultant $consultant, ConsultantBannerSlide $slide): JsonResponse
    {
        abort_unless($slide->consultant_id === $consultant->id, 403);
        $slide->delete();

        return response()->json(['message' => 'Banner slide removed.']);
    }
}
