<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSize;
use App\Support\AdSizes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdSizeController extends Controller
{
    public function index(): View
    {
        return view('backend.ads.admin.sizes.index', [
            'sizes' => AdSizes::all(),
            'customSizes' => AdSize::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'width' => 'required|integer|min:1|max:5000',
            'height' => 'required|integer|min:1|max:5000',
            'admin_only' => 'nullable|boolean',
        ]);

        $baseKey = Str::slug($validated['name'], '_');
        $sizeKey = $baseKey;
        $counter = 1;

        while (AdSizes::exists($sizeKey)) {
            $counter++;
            $sizeKey = $baseKey.'_'.$counter;
        }

        AdSize::create([
            'size_key' => $sizeKey,
            'name' => $validated['name'],
            'width' => (int) $validated['width'],
            'height' => (int) $validated['height'],
            'admin_only' => $request->boolean('admin_only'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Ad size added successfully.');
    }
}
