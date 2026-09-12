<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Services\PortalNotificationService;
use App\Support\EducatorFileUploader;
use App\Support\StudyMaterialUploadConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class StudyMaterialController extends Controller
{
    /** @var list<string> */
    private const CUSTOM_CONTENT_TYPES = ['notes', 'sample_papers', 'worksheets', 'assignments'];

    public function index(): View
    {
        return view('backend.educator.materials.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $educatorId = auth()->user()->educator?->id;
        abort_unless($educatorId, 403);

        $query = StudyMaterial::query()
            ->where('educator_id', $educatorId)
            ->select([
                'id',
                'title',
                'material_type',
                'subject',
                'class_course',
                'status',
                'downloads_count',
                'updated_at',
            ])
            ->orderByDesc('updated_at');

        $status = $request->string('status')->toString();
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        return DataTables::of($query)
            ->addColumn('title_display', function (StudyMaterial $material): string {
                $subtitle = e($material->class_course ?: '—');

                return '<div class="fw-semibold">'.e($material->title).'</div><div class="small text-muted">'.$subtitle.'</div>';
            })
            ->addColumn('type_label', fn (StudyMaterial $material): string => e($material->materialTypeLabel()))
            ->addColumn('subject_display', fn (StudyMaterial $material): string => e($material->subject ?: '—'))
            ->addColumn('status_badge', function (StudyMaterial $material): string {
                $status = $material->status ?? 'pending';
                $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');

                return '<span class="badge bg-'.$badge.'">'.ucfirst($status).'</span>';
            })
            ->addColumn('downloads_display', fn (StudyMaterial $material): string => number_format((int) $material->downloads_count))
            ->addColumn('actions', function (StudyMaterial $material): string {
                $view = '<a href="'.route('educator.materials.show', $material).'" class="btn btn-sm btn-outline-secondary">View</a>';
                $edit = '<a href="'.route('educator.materials.edit', $material).'" class="btn btn-sm btn-outline-primary">Edit</a>';
                $delete = '<button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="'.$material->id.'">Delete</button>';

                return '<div class="d-flex gap-2 justify-content-end flex-wrap">'.$view.$edit.$delete.'</div>';
            })
            ->editColumn('updated_at', function (StudyMaterial $material): string {
                return optional($material->updated_at)
                    ? $material->updated_at->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '—';
            })
            ->rawColumns(['title_display', 'status_badge', 'actions'])
            ->make(true);
    }

    public function create(Request $request): View
    {
        $type = old('material_type', 'notes');
        if (! in_array($type, StudyMaterialUploadConfig::typeKeys(), true)) {
            $type = 'notes';
        }

        return view('backend.educator.materials.form', [
            'material' => new StudyMaterial(['material_type' => $type]),
            'uploadType' => $type,
        ]);
    }

    public function typeConfig(string $type): JsonResponse
    {
        abort_unless(in_array($type, StudyMaterialUploadConfig::typeKeys(), true), 404);

        $uploaderRole = StudyMaterialUploadConfig::resolveUploaderRole(auth()->user());
        $config = StudyMaterialUploadConfig::type($type);
        $config['options'] = StudyMaterialUploadConfig::filterOptionsForRole($config['options'], $uploaderRole);
        $config['short_title'] = str_replace('Upload ', '', $config['title']);
        $config['details_title'] = match ($type) {
            'reference_books' => 'Book Details',
            'assignments' => 'Assignment Details',
            'study_guides' => 'Study Guide Details',
            'videos' => 'Lesson Details',
            'sample_papers' => 'Solved Paper Details',
            'worksheets' => 'Worksheet Details',
            'question_papers' => 'Question Paper Details',
            default => 'Material Details',
        };
        $config['guidelines_title'] = match ($type) {
            'question_papers' => 'Types of Question Papers',
            'sample_papers' => 'Types of Solved Papers',
            'videos' => 'Types of Educational Videos & Audio',
            default => 'Content Guidelines',
        };
        $config['upload_title'] = $type === 'videos'
            ? 'Upload File or Provide Link'
            : 'Upload File(s)';

        return response()->json([
            'ok' => true,
            'type' => $type,
            'config' => $config,
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

    public function show(StudyMaterial $material): View
    {
        $this->authorizeOwner($material);

        return view('backend.educator.materials.show', compact('material'));
    }

    public function download(StudyMaterial $material): BinaryFileResponse
    {
        $this->authorizeOwner($material);

        abort_unless(filled($material->file_path) && is_file(public_path($material->file_path)), 404);

        return response()->download(
            public_path($material->file_path),
            $material->file_name ?: basename($material->file_path)
        );
    }

    public function downloadSolution(StudyMaterial $material): BinaryFileResponse
    {
        $this->authorizeOwner($material);
        abort_unless($material->hasBoardSolution(), 404);

        $solutionPath = data_get($material->meta, 'solution_file_path');
        abort_unless(filled($solutionPath) && is_file(public_path($solutionPath)), 404);

        return response()->download(
            public_path($solutionPath),
            $material->solutionFileName() ?: basename($solutionPath)
        );
    }

    public function downloadSolvedWorksheet(StudyMaterial $material): BinaryFileResponse
    {
        $this->authorizeOwner($material);
        abort_unless($material->hasSolvedWorksheet(), 404);

        $filePath = data_get($material->meta, 'solved_worksheet_file_path');
        abort_unless(filled($filePath) && is_file(public_path($filePath)), 404);

        return response()->download(
            public_path($filePath),
            $material->solvedWorksheetFileName() ?: basename($filePath)
        );
    }

    public function edit(StudyMaterial $material): View
    {
        $this->authorizeOwner($material);

        return view('backend.educator.materials.form', [
            'material' => $material,
            'uploadType' => $material->material_type ?: 'notes',
        ]);
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
        EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solution_file_path'));
        EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solved_worksheet_file_path'));
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
        $materialType = $request->string('material_type')->toString();
        $maxFileKb = $materialType === 'videos' ? 512000 : 51200;
        $hasExternalLink = filled($request->input('meta.external_url'));
        $isCustomContent = in_array($materialType, self::CUSTOM_CONTENT_TYPES, true)
            && $request->input('meta.content_mode') === 'custom';

        $fileRules = ['nullable', 'file', 'max:'.$maxFileKb];

        if (! $isCustomContent) {
            $fileRules = $material?->exists
                ? ['nullable', 'file', 'max:'.$maxFileKb]
                : ($hasExternalLink && $materialType === 'videos'
                    ? ['nullable', 'file', 'max:'.$maxFileKb]
                    : ['required', 'file', 'max:'.$maxFileKb]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'material_type' => ['required', 'string', 'in:'.implode(',', \App\Support\StudyMaterialUploadConfig::typeKeys())],
            'category' => ['nullable', 'string', 'max:80'],
            'class_course' => ['nullable', 'string', 'max:120'],
            'board_university' => ['nullable', 'string', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'topic_chapter' => ['nullable', 'string', 'max:255'],
            'exam_test' => ['nullable', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'max:50'],
            'difficulty' => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced,Easy,Medium,Hard'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'medium' => ['nullable', 'string', 'max:50'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'price' => ['nullable', 'numeric', 'min:1', 'max:999999'],
            'is_free' => ['nullable', 'boolean'],
            'meta.visibility' => ['nullable', 'string', 'in:public,personal'],
            'meta.pricing_mode' => ['nullable', 'string', 'in:free,paid'],
            'tags' => ['nullable', 'string', 'max:500'],
            'contents' => ['nullable', 'array'],
            'contents.*' => ['nullable', 'string', 'max:255'],
            'terms_accepted' => ['accepted'],
            'meta' => ['nullable', 'array'],
            'meta.uploader_role' => ['nullable', 'string', 'max:40'],
            'meta.child_profile_id' => ['nullable', 'integer', 'exists:child_profiles,id'],
            'meta.content_mode' => ['nullable', 'string', 'in:upload,custom'],
            'meta.custom_note_html' => ['nullable', 'string', 'max:500000'],
            'meta.editor_language' => ['nullable', 'string', 'max:20'],
            'meta.author' => ['nullable', 'string', 'max:255'],
            'meta.author_rights_confirmed' => ['nullable', 'boolean'],
            'meta.publisher' => ['nullable', 'string', 'max:255'],
            'meta.edition' => ['nullable', 'string', 'max:80'],
            'meta.isbn' => ['nullable', 'string', 'max:40'],
            'meta.book_type' => ['nullable', 'string', 'max:80'],
            'meta.assignment_type' => ['nullable', 'string', 'max:80'],
            'meta.due_date' => ['nullable', 'date'],
            'meta.marks' => ['nullable', 'string', 'max:20'],
            'meta.instructions' => ['nullable', 'string', 'max:5000'],
            'meta.worksheet_type' => ['nullable', 'string', 'max:80'],
            'meta.learning_objective' => ['nullable', 'string', 'max:500'],
            'meta.estimated_time' => ['nullable', 'string', 'max:80'],
            'meta.guide_type' => ['nullable', 'string', 'max:80'],
            'meta.exam_focus' => ['nullable', 'string', 'max:120'],
            'meta.solution_type' => ['nullable', 'string', 'max:80'],
            'meta.solution_format' => ['nullable', 'string', 'max:80'],
            'meta.marks_obtained' => ['nullable', 'string', 'max:20'],
            'meta.term' => ['nullable', 'string', 'max:80'],
            'meta.month' => ['nullable', 'string', 'max:40'],
            'meta.set_code' => ['nullable', 'string', 'max:40'],
            'meta.is_board' => ['nullable', 'boolean'],
            'meta.institution_type' => ['nullable', 'string', 'in:university,college,school'],
            'meta.solution_mode' => ['nullable', 'string', 'in:upload,custom'],
            'meta.custom_solution_html' => ['nullable', 'string', 'max:500000'],
            'meta.solution_editor_language' => ['nullable', 'string', 'max:20'],
            'solution_file' => ['nullable', 'file', 'max:51200'],
            'meta.solved_worksheet_mode' => ['nullable', 'string', 'in:upload,custom'],
            'meta.custom_solved_worksheet_html' => ['nullable', 'string', 'max:500000'],
            'meta.solved_worksheet_editor_language' => ['nullable', 'string', 'max:20'],
            'solved_worksheet_file' => ['nullable', 'file', 'max:51200'],
            'meta.content_type' => ['nullable', 'string', 'max:30'],
            'meta.lesson_type' => ['nullable', 'string', 'max:80'],
            'meta.duration' => ['nullable', 'string', 'max:40'],
            'meta.is_series' => ['nullable', 'boolean'],
            'meta.part_number' => ['nullable', 'string', 'max:20'],
            'meta.external_url' => ['nullable', 'url', 'max:500'],
            'meta.link_type' => ['nullable', 'string', 'max:30'],
            'meta.cover_url' => ['nullable', 'url', 'max:500'],
            'meta.generate_preview' => ['nullable', 'boolean'],
            'meta.options' => ['nullable', 'array'],
            'meta.options.*' => ['nullable', 'string', 'max:80'],
            'file' => $fileRules,
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'terms_accepted.accepted' => 'You must agree to the Terms & Conditions before submitting.',
            'file.required' => 'Please upload a file or provide a valid link.',
        ]);

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

        $meta = collect($validated['meta'] ?? [])
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        $meta['options'] = collect($request->input('meta.options', []))
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($request->filled('meta.is_series')) {
            $meta['is_series'] = $request->input('meta.is_series') === '1'
                || $request->boolean('meta.is_series');
        }

        if ($request->has('meta.generate_preview')) {
            $meta['generate_preview'] = $request->boolean('meta.generate_preview');
        }

        if ($materialType === 'question_papers') {
            $meta['is_board'] = $request->boolean('meta.is_board');

            if ($meta['is_board']) {
                unset($meta['institution_type']);
                abort_unless(filled($validated['board_university'] ?? null), 422, 'Please select board name.');
                $meta = $this->applyBoardQuestionPaperSolution($request, $meta, $material);
            } else {
                $institutionType = (string) ($request->input('meta.institution_type') ?? '');
                abort_unless(in_array($institutionType, ['university', 'college', 'school'], true), 422, 'Please select institution type.');
                $meta['institution_type'] = $institutionType;
                abort_unless(filled($validated['board_university'] ?? null), 422, 'Please enter institution name.');
                unset($meta['month']);
                $meta = $this->clearBoardQuestionPaperSolution($meta, $material);
            }
        }

        if ($isCustomContent) {
            $customContentHtml = trim((string) ($meta['custom_note_html'] ?? ''));
            $customContentMessage = match ($materialType) {
                'sample_papers' => 'Please write your solved paper content.',
                'worksheets' => 'Please write your worksheet content.',
                'assignments' => 'Please write your assignment content.',
                default => 'Please write your custom note content.',
            };
            abort_unless(filled(strip_tags($customContentHtml)), 422, $customContentMessage);
            $meta['content_mode'] = 'custom';
        } elseif (in_array($materialType, self::CUSTOM_CONTENT_TYPES, true)) {
            $meta['content_mode'] = $meta['content_mode'] ?? 'upload';
        }

        if ($materialType === 'worksheets') {
            $meta = $this->applyWorksheetSolved($request, $meta, $material);
        } else {
            $meta = $this->clearWorksheetSolved($meta, $material);
        }

        if ($materialType === 'reference_books') {
            abort_unless($request->boolean('meta.author_rights_confirmed'), 422, 'You must confirm that you are the author and hold all rights to this book.');
            $meta['author_rights_confirmed'] = true;
        } else {
            unset($meta['author_rights_confirmed']);
        }

        if ($materialType === 'notes') {
            $visibility = in_array($meta['visibility'] ?? 'public', ['public', 'personal'], true)
                ? ($meta['visibility'] ?? 'public')
                : 'public';
            $meta['visibility'] = $visibility;

            if ($visibility === 'personal') {
                $validated['is_free'] = true;
                $validated['price'] = null;
                $meta['pricing_mode'] = 'free';
            } else {
                $pricingMode = ($meta['pricing_mode'] ?? 'free') === 'paid' ? 'paid' : 'free';
                $meta['pricing_mode'] = $pricingMode;

                if ($pricingMode === 'paid') {
                    $price = (float) $request->input('price', 0);
                    abort_unless($price > 0, 422, 'Please enter a valid price for paid notes.');
                    $validated['is_free'] = false;
                    $validated['price'] = round($price, 2);
                } else {
                    $validated['is_free'] = true;
                    $validated['price'] = null;
                }
            }
        } else {
            $validated['is_free'] = $request->boolean('is_free', true);
            $validated['price'] = null;
        }

        if (StudyMaterialUploadConfig::shouldShowChildSelector(auth()->user())) {
            $childProfileId = (int) ($meta['child_profile_id'] ?? 0);
            abort_unless($childProfileId > 0, 422, 'Please select which child this material is for.');

            $ownsChild = auth()->user()->childProfiles()
                ->where('id', $childProfileId)
                ->where('status', 'approved')
                ->exists();

            abort_unless($ownsChild, 422, 'The selected child profile is invalid.');
            $meta['uploader_role'] = 'student';
        }

        $validated['meta'] = $meta ?: null;

        unset($validated['file'], $validated['thumbnail'], $validated['solution_file'], $validated['solved_worksheet_file'], $validated['terms_accepted']);

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function applyBoardQuestionPaperSolution(Request $request, array $meta, ?StudyMaterial $material = null): array
    {
        $solutionMode = $request->input('meta.solution_mode') === 'custom' ? 'custom' : 'upload';
        $meta['solution_mode'] = $solutionMode;

        if ($solutionMode === 'custom') {
            $html = trim((string) $request->input('meta.custom_solution_html', ''));
            if (filled(strip_tags($html))) {
                $meta['custom_solution_html'] = $html;
            } else {
                unset($meta['custom_solution_html']);
            }

            if ($material && filled(data_get($material->meta, 'solution_file_path'))) {
                EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solution_file_path'));
            }

            unset(
                $meta['solution_file_path'],
                $meta['solution_file_name'],
                $meta['solution_file_type'],
                $meta['solution_file_size']
            );

            return $meta;
        }

        unset($meta['custom_solution_html']);

        if ($request->hasFile('solution_file')) {
            if ($material && filled(data_get($material->meta, 'solution_file_path'))) {
                EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solution_file_path'));
            }

            $file = $request->file('solution_file');
            $meta['solution_file_path'] = EducatorFileUploader::storeDocument($file, 'material-solutions');
            $meta['solution_file_name'] = $file->getClientOriginalName();
            $meta['solution_file_type'] = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $meta['solution_file_size'] = $file->getSize();

            return $meta;
        }

        foreach (['solution_file_path', 'solution_file_name', 'solution_file_type', 'solution_file_size'] as $key) {
            $existing = data_get($material?->meta, $key);
            if (filled($existing)) {
                $meta[$key] = $existing;
            }
        }

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function clearBoardQuestionPaperSolution(array $meta, ?StudyMaterial $material = null): array
    {
        if ($material && filled(data_get($material->meta, 'solution_file_path'))) {
            EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solution_file_path'));
        }

        unset(
            $meta['solution_mode'],
            $meta['custom_solution_html'],
            $meta['solution_editor_language'],
            $meta['solution_file_path'],
            $meta['solution_file_name'],
            $meta['solution_file_type'],
            $meta['solution_file_size']
        );

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function applyWorksheetSolved(Request $request, array $meta, ?StudyMaterial $material = null): array
    {
        $solvedMode = $request->input('meta.solved_worksheet_mode') === 'custom' ? 'custom' : 'upload';
        $meta['solved_worksheet_mode'] = $solvedMode;

        if ($solvedMode === 'custom') {
            $html = trim((string) $request->input('meta.custom_solved_worksheet_html', ''));
            if (filled(strip_tags($html))) {
                $meta['custom_solved_worksheet_html'] = $html;
            } else {
                unset($meta['custom_solved_worksheet_html']);
            }

            if ($material && filled(data_get($material->meta, 'solved_worksheet_file_path'))) {
                EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solved_worksheet_file_path'));
            }

            unset(
                $meta['solved_worksheet_file_path'],
                $meta['solved_worksheet_file_name'],
                $meta['solved_worksheet_file_type'],
                $meta['solved_worksheet_file_size']
            );

            return $meta;
        }

        unset($meta['custom_solved_worksheet_html']);

        if ($request->hasFile('solved_worksheet_file')) {
            if ($material && filled(data_get($material->meta, 'solved_worksheet_file_path'))) {
                EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solved_worksheet_file_path'));
            }

            $file = $request->file('solved_worksheet_file');
            $meta['solved_worksheet_file_path'] = EducatorFileUploader::storeDocument($file, 'material-solutions');
            $meta['solved_worksheet_file_name'] = $file->getClientOriginalName();
            $meta['solved_worksheet_file_type'] = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $meta['solved_worksheet_file_size'] = $file->getSize();

            return $meta;
        }

        foreach (['solved_worksheet_file_path', 'solved_worksheet_file_name', 'solved_worksheet_file_type', 'solved_worksheet_file_size'] as $key) {
            $existing = data_get($material?->meta, $key);
            if (filled($existing)) {
                $meta[$key] = $existing;
            }
        }

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function clearWorksheetSolved(array $meta, ?StudyMaterial $material = null): array
    {
        if ($material && filled(data_get($material->meta, 'solved_worksheet_file_path'))) {
            EducatorFileUploader::deleteIfExists(data_get($material->meta, 'solved_worksheet_file_path'));
        }

        unset(
            $meta['solved_worksheet_mode'],
            $meta['custom_solved_worksheet_html'],
            $meta['solved_worksheet_editor_language'],
            $meta['solved_worksheet_file_path'],
            $meta['solved_worksheet_file_name'],
            $meta['solved_worksheet_file_type'],
            $meta['solved_worksheet_file_size']
        );

        return $meta;
    }
}
