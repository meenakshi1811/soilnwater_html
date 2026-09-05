<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Services\PortalNotificationService;
use App\Support\EducatorFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudyMaterialController extends Controller
{
    public function index(): View
    {
        $materials = StudyMaterial::query()
            ->where('educator_id', auth()->user()->educator->id)
            ->latest()
            ->get();

        return view('backend.educator.materials.index', compact('materials'));
    }

    public function create(): View
    {
        return view('backend.educator.materials.form', [
            'material' => new StudyMaterial(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $educator = auth()->user()->educator;
        $data = $this->validated($request);
        $data['educator_id'] = $educator->id;
        $data['user_id'] = auth()->id();
        $data['slug'] = StudyMaterial::generateUniqueSlug($data['title']);
        $data['status'] = 'pending';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // Capture metadata before move() — getSize() fails after the temp file is relocated.
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $data['file_size'] = $file->getSize();
            $data['file_path'] = EducatorFileUploader::storeDocument($file, 'materials');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = EducatorFileUploader::storeImage($request->file('thumbnail'), 'thumbnails');
        }

        $material = StudyMaterial::create($data);

        PortalNotificationService::notifyAdminsOfApprovalRequest(
            'Study material',
            $material->title.' (by '.($educator->display_name ?: 'Teacher / Tutor').')',
            route('admin.approvals.index', ['module' => 'study-materials'])
        );

        $message = 'Study material submitted for admin approval.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'redirect' => route('educator.materials.index'),
            ]);
        }

        return redirect()
            ->route('educator.materials.index')
            ->with('success', $message);
    }

    public function edit(StudyMaterial $material): View
    {
        $this->authorizeOwner($material);

        return view('backend.educator.materials.form', compact('material'));
    }

    public function update(Request $request, StudyMaterial $material): RedirectResponse|JsonResponse
    {
        $this->authorizeOwner($material);
        $educator = auth()->user()->educator;
        $data = $this->validated($request, $material);

        if ($data['title'] !== $material->title) {
            $data['slug'] = StudyMaterial::generateUniqueSlug($data['title']);
        }

        $data['status'] = 'pending';
        $data['approved_at'] = null;
        $data['approved_by'] = null;
        $data['is_verified'] = false;
        $data['is_trending'] = false;

        if ($request->hasFile('file')) {
            EducatorFileUploader::deleteIfExists($material->file_path);
            $file = $request->file('file');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $data['file_size'] = $file->getSize();
            $data['file_path'] = EducatorFileUploader::storeDocument($file, 'materials');
        }

        if ($request->hasFile('thumbnail')) {
            EducatorFileUploader::deleteIfExists($material->thumbnail);
            $data['thumbnail'] = EducatorFileUploader::storeImage($request->file('thumbnail'), 'thumbnails');
        }

        $material->update($data);
        $material->refresh();

        PortalNotificationService::notifyAdminsOfApprovalRequest(
            'Updated study material',
            $material->title.' (by '.($educator->display_name ?: 'Teacher / Tutor').')',
            route('admin.approvals.index', ['module' => 'study-materials'])
        );

        $message = 'Study material updated and sent for admin approval.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'redirect' => route('educator.materials.index'),
            ]);
        }

        return redirect()
            ->route('educator.materials.index')
            ->with('success', $message);
    }

    public function destroy(StudyMaterial $material): JsonResponse
    {
        $this->authorizeOwner($material);
        EducatorFileUploader::deleteIfExists($material->thumbnail);
        EducatorFileUploader::deleteIfExists($material->file_path);
        $material->delete();

        return response()->json(['ok' => true, 'message' => 'Study material deleted.']);
    }

    private function authorizeOwner(StudyMaterial $material): void
    {
        abort_unless($material->educator_id === auth()->user()->educator?->id, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?StudyMaterial $material = null): array
    {
        $fileRules = $material?->exists
            ? ['nullable', 'file', 'max:20480']
            : ['required', 'file', 'max:20480'];

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'material_type' => ['required', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:80'],
            'class_course' => ['nullable', 'string', 'max:120'],
            'board_university' => ['nullable', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'topic_chapter' => ['nullable', 'string', 'max:255'],
            'exam_test' => ['nullable', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'max:50'],
            'difficulty' => ['nullable', 'string', 'max:30'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'medium' => ['nullable', 'string', 'max:50'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'is_free' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'string', 'max:500'],
            'contents' => ['nullable', 'array'],
            'contents.*' => ['nullable', 'string', 'max:255'],
            'file' => $fileRules,
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['is_free'] = $request->boolean('is_free', true);
        $validated['tags'] = collect(explode(',', (string) ($validated['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->take(10)
            ->values()
            ->all();

        $validated['contents'] = collect($validated['contents'] ?? [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all() ?: null;

        unset($validated['file'], $validated['thumbnail']);

        return $validated;
    }
}
