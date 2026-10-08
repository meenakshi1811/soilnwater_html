<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSectionHeroImage;
use App\Support\HomepageDesktopShowcase;
use App\Support\HomepageSectionHero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomepageSectionHeroImageController extends Controller
{
    public function index(): View
    {
        $labels = HomepageSectionHeroImage::sectionLabels();
        $rows = HomepageSectionHeroImage::query()
            ->whereIn('section_key', array_keys($labels))
            ->get()
            ->keyBy('section_key');

        $sections = [];
        foreach ($labels as $key => $label) {
            $record = $rows->get($key);
            $sections[] = [
                'key' => $key,
                'label' => $label,
                'image_url' => HomepageDesktopShowcase::section($key)['hero_bg'] ?? '',
                'has_custom' => $record !== null,
            ];
        }

        return view('backend.admin.homepage-section-hero-images.index', [
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, string $sectionKey): JsonResponse
    {
        abort_unless(in_array($sectionKey, HomepageSectionHeroImage::sectionKeys(), true), 404);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $record = HomepageSectionHeroImage::query()->firstOrNew(['section_key' => $sectionKey]);
        if ($record->exists) {
            $this->deleteStoredImage($record->image_path);
        }

        $record->image_path = $this->storeImage($request);
        $record->save();

        HomepageSectionHero::forgetCache();

        return response()->json([
            'message' => 'Section banner updated.',
            'image_url' => $record->imageUrl(),
        ]);
    }

    public function destroy(string $sectionKey): JsonResponse
    {
        abort_unless(in_array($sectionKey, HomepageSectionHeroImage::sectionKeys(), true), 404);

        $record = HomepageSectionHeroImage::query()->where('section_key', $sectionKey)->first();
        if ($record) {
            $this->deleteStoredImage($record->image_path);
            $record->delete();
        }

        HomepageSectionHero::forgetCache();

        return response()->json(['message' => 'Reverted to default banner.']);
    }

    private function storeImage(Request $request): string
    {
        $directory = public_path('uploads/homepage/section-heroes');
        File::ensureDirectoryExists($directory);

        $file = $request->file('image');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/homepage/section-heroes/'.$filename;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! filled($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $full = public_path($path);
        if (File::isFile($full)) {
            File::delete($full);
        }
    }
}
