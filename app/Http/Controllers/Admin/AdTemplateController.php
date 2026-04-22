<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdTemplate;
use App\Support\AdSizes;
use App\Support\AdTemplatePreview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AdTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdTemplate::query()->latest();

        if ($request->filled('size_type') && AdSizes::exists($request->string('size_type')->toString())) {
            $query->where('size_type', $request->string('size_type')->toString());
        }

        return view('backend.ads.admin.templates.index', [
            'sizes' => AdSizes::all(),
            'activeSizeType' => $request->string('size_type')->toString(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = AdTemplate::query()->latest();

        if ($request->filled('size_type') && AdSizes::exists($request->string('size_type')->toString())) {
            $query->where('size_type', $request->string('size_type')->toString());
        }

        $sizes = AdSizes::all();
        $placeholder = asset('assets/images/ad-sample.png');

        return DataTables::of($query)
            ->addColumn('preview_html', function (AdTemplate $template) use ($sizes, $placeholder) {
                $size = $sizes[$template->size_type] ?? ['ratio' => '1 / 1', 'w' => 1, 'h' => 1];
                $html = AdTemplatePreview::render($template->layout_html, AdTemplatePreview::sampleFieldsForTemplateName($template->name), $placeholder);

                return '<div class="ads-dt-preview js-ads-scaled-preview" data-source-width="'.(int) $size['w'].'" data-source-height="'.(int) $size['h'].'" style="aspect-ratio: '.$size['ratio'].';"><div class="ads-mini-preview-inner">'.$html.'</div></div>';
            })
            ->addColumn('size_label', fn (AdTemplate $t) => $sizes[$t->size_type]['name'] ?? $t->size_type)
            ->addColumn('status_badge', fn (AdTemplate $t) => '<span class="badge bg-'.($t->is_active ? 'success' : 'secondary').'">'.($t->is_active ? 'Active' : 'Inactive').'</span>')
            ->editColumn('updated_at', fn (AdTemplate $t) => $t->updated_at?->format('Y-m-d H:i') ?? '-')
            ->addColumn('actions', function (AdTemplate $t) {
                return '<div class="d-flex justify-content-end gap-2">'
                    .'<a href="'.route('admin.ads.templates.edit', $t).'" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-solid fa-pen"></i></a>'
                    .'</div>';
            })
            ->rawColumns(['preview_html', 'status_badge', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        return view('backend.ads.admin.templates.form', [
            'template' => null,
            'sizes' => AdSizes::all(),
        ]);
    }

    public function edit(AdTemplate $template): View
    {
        return view('backend.ads.admin.templates.form', [
            'template' => $template,
            'sizes' => AdSizes::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $validated['created_by'] = $request->user()->id;
        $validated['preview_image'] = $this->storePreviewIfPresent($request) ?? null;

        AdTemplate::create($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Template created successfully.',
                'redirect_url' => route('admin.ads.templates.index'),
            ]);
        }

        return redirect()
            ->route('admin.ads.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function update(Request $request, AdTemplate $template): RedirectResponse
    {
        $validated = $this->validated($request);

        $preview = $this->storePreviewIfPresent($request);
        if ($preview) {
            $validated['preview_image'] = $preview;
        }

        $template->update($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Template updated successfully.',
                'redirect_url' => route('admin.ads.templates.index'),
            ]);
        }

        return redirect()
            ->route('admin.ads.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'size_type' => 'required|string|max:40',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'layout_html' => 'required|string',
            'schema_json' => 'required|string',
            'is_active' => 'nullable|boolean',
            'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        abort_unless(AdSizes::exists($validated['size_type']), 422);

        $schema = json_decode($validated['schema_json'], true);
        if (!is_array($schema)) {
            abort(422, 'Schema JSON must be valid JSON.');
        }

        $validated['schema_json'] = $schema;
        $validated['is_active'] = (bool) ($request->boolean('is_active'));

        return $validated;
    }

    private function storePreviewIfPresent(Request $request): ?string
    {
        if (!$request->hasFile('preview_image')) {
            return null;
        }

        $file = $request->file('preview_image');
        $ext = $file->getClientOriginalExtension() ?: $file->extension();
        $fileName = 'tpl-'.Str::uuid().'.'.$ext;
        $relativeDirectory = 'uploads/ads/templates';
        $absoluteDirectory = public_path($relativeDirectory);

        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0755, true);
        }

        $file->move($absoluteDirectory, $fileName);

        return $relativeDirectory.'/'.$fileName;
    }
}
