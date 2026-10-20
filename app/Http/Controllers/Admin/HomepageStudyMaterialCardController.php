<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageStudyMaterialCard;
use App\Support\ImageThumbnail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Homepage → Study Material Cards: the cards of the homepage "Study Material Library"
 * section. Listing, add/edit/delete and drag-and-drop order all run over AJAX.
 */
class HomepageStudyMaterialCardController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.homepage-study-material-cards.index', [
            'tones' => HomepageStudyMaterialCard::TONES,
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'cards' => HomepageStudyMaterialCard::query()->ordered()->get()->map(fn (HomepageStudyMaterialCard $card): array => $this->payload($card))->values(),
        ]);
    }

    public function show(HomepageStudyMaterialCard $homepageStudyMaterialCard): JsonResponse
    {
        return response()->json(['card' => $this->payload($homepageStudyMaterialCard)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateCard($request, null);
        $validated['image_path'] = $this->storeImage($request);
        // New cards go to the end of the list
        $validated['sort_order'] = (int) HomepageStudyMaterialCard::query()->max('sort_order') + 1;

        $card = HomepageStudyMaterialCard::query()->create($validated);

        return response()->json([
            'message' => 'Study material card added.',
            'card' => $this->payload($card),
        ]);
    }

    public function update(Request $request, HomepageStudyMaterialCard $homepageStudyMaterialCard): JsonResponse
    {
        $validated = $this->validateCard($request, $homepageStudyMaterialCard);

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($homepageStudyMaterialCard->image_path);
            $validated['image_path'] = $this->storeImage($request);
        }

        $homepageStudyMaterialCard->fill($validated)->save();

        return response()->json([
            'message' => 'Study material card updated.',
            'card' => $this->payload($homepageStudyMaterialCard),
        ]);
    }

    public function destroy(HomepageStudyMaterialCard $homepageStudyMaterialCard): JsonResponse
    {
        $this->deleteStoredImage($homepageStudyMaterialCard->image_path);
        $homepageStudyMaterialCard->delete();

        return response()->json(['message' => 'Study material card deleted.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'distinct', Rule::exists('homepage_study_material_cards', 'id')],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach (array_values($validated['order']) as $index => $id) {
                HomepageStudyMaterialCard::query()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json(['message' => 'Card order saved.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCard(Request $request, ?HomepageStudyMaterialCard $card): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link_url' => ['required', 'string', 'max:500'],
            'tone' => ['required', Rule::in(HomepageStudyMaterialCard::TONES)],
            'icon' => ['nullable', 'string', 'max:64'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [$card ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [], [
            'link_url' => 'link',
        ]);

        unset($validated['image']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['icon'] = $this->normalizeIcon($validated['icon'] ?? null);

        return $validated;
    }

    private function normalizeIcon(?string $icon): string
    {
        $icon = trim((string) $icon);
        if ($icon === '') {
            return 'fa-book-open';
        }

        // Accept "fa-solid fa-award" or "fa-award"; the card adds "fa-solid" itself
        $parts = preg_split('/\s+/', $icon) ?: [];
        foreach ($parts as $part) {
            if (str_starts_with($part, 'fa-') && ! in_array($part, ['fa-solid', 'fa-regular', 'fa-brands'], true)) {
                return $part;
            }
        }

        return 'fa-book-open';
    }

    private function storeImage(Request $request): string
    {
        $directory = public_path('uploads/homepage/study-material');
        File::ensureDirectoryExists($directory);

        $file = $request->file('image');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        // The homepage shows a small copy: make it now rather than on someone's visit
        ImageThumbnail::prepare('uploads/homepage/study-material/'.$filename);

        return 'uploads/homepage/study-material/'.$filename;
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

    /**
     * @return array<string, mixed>
     */
    private function payload(HomepageStudyMaterialCard $card): array
    {
        return [
            'id' => $card->id,
            'title' => $card->title,
            'subtitle' => $card->subtitle,
            'link_url' => $card->link_url,
            'tone' => $card->tone,
            'icon' => $card->icon,
            'is_active' => $card->is_active,
            'image_url' => $card->imageUrl(),
        ];
    }
}
