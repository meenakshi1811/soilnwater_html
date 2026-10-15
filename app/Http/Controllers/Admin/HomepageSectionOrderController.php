<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Support\HomepageSectionOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Homepage Section Order: drag-and-drop order of the homepage panel sections.
 * The list is loaded and saved over AJAX.
 */
class HomepageSectionOrderController extends Controller
{
    public function index(): View
    {
        return view('backend.homepage-section-order.index');
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'sections' => $this->sectionsPayload($this->setting()),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $keys = array_keys(HomepageSectionOrder::sections());

        $validated = $request->validate([
            'order' => ['required', 'array', 'size:'.count($keys)],
            'order.*' => ['required', 'string', 'distinct', Rule::in($keys)],
        ]);

        $setting = $this->setting();
        $setting->section_order = array_values($validated['order']);
        $setting->save();

        return response()->json([
            'message' => 'Homepage section order saved.',
            'sections' => $this->sectionsPayload($setting),
        ]);
    }

    public function reset(): JsonResponse
    {
        $setting = $this->setting();
        $setting->section_order = null;
        $setting->save();

        return response()->json([
            'message' => 'Homepage section order reset to the default.',
            'sections' => $this->sectionsPayload($setting),
        ]);
    }

    private function setting(): HomepageSetting
    {
        return HomepageSetting::query()->firstOrCreate(['id' => 1]);
    }

    /**
     * @return list<array{key: string, label: string, icon: string, enabled: bool}>
     */
    private function sectionsPayload(HomepageSetting $setting): array
    {
        $sections = HomepageSectionOrder::sections();

        return array_map(fn (string $key): array => [
            'key' => $key,
            'label' => $sections[$key]['label'],
            'icon' => $sections[$key]['icon'],
            'enabled' => HomepageSectionOrder::isEnabled($key, $setting->section_toggles),
        ], HomepageSectionOrder::keys($setting->section_order));
    }
}
