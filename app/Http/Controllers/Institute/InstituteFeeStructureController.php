<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Support\InstituteFeeStructure;
use App\Support\InstituteFileUploader;
use App\Support\ManagedInstitute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A school's fee structure page in its portal (and the admin's "manage" pages): tables, sections and
 * an optional PDF or image, kept in profile_meta['fee_structure'] (InstituteFeeStructure).
 */
class InstituteFeeStructureController extends Controller
{
    public function edit(Request $request): View
    {
        $institute = ManagedInstitute::current($request);
        abort_unless($institute, 404);

        return view('backend.institute.fee-structure', [
            'institute' => $institute,
            'fee' => InstituteFeeStructure::fromMeta(data_get($institute->profile_meta, 'fee_structure'))
                ?? InstituteFeeStructure::clean([]),
            'presets' => InstituteFeeStructure::presets($institute),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var Institute $institute */
        $institute = ManagedInstitute::current($request);
        abort_unless($institute, 404);

        // (the table editor sends its tables as JSON)
        $tables = json_decode((string) $request->input('tables_json', '[]'), true);
        $request->merge(['tables' => is_array($tables) ? $tables : []]);

        $validated = $request->validate(InstituteFeeStructure::validationRules(), InstituteFeeStructure::validationMessages());

        $meta = (array) ($institute->profile_meta ?? []);
        $saved = (array) ($meta['fee_structure'] ?? []);
        $file = $saved['file'] ?? null;

        if ($request->boolean('remove_fee_file') || $request->hasFile('fee_file')) {
            InstituteFileUploader::deleteIfExists($file);
            $file = null;
        }
        if ($request->hasFile('fee_file')) {
            $upload = $request->file('fee_file');
            $file = strtolower($upload->getClientOriginalExtension()) === 'pdf'
                ? InstituteFileUploader::storeDocument($upload, 'fee-structures')
                : InstituteFileUploader::storeImage($upload, 'fee-structures');
        }

        $fee = InstituteFeeStructure::clean([
            'title' => $validated['title'] ?? '',
            'session' => $validated['session'] ?? '',
            'effective_from' => $validated['effective_from'] ?? '',
            'intro' => $validated['intro'] ?? '',
            'tables' => $validated['tables'] ?? [],
            'sections' => $validated['sections'] ?? [],
            'file' => $file,
        ]);

        // (other settings stored there stay)
        if (InstituteFeeStructure::isEmpty($fee)) {
            unset($meta['fee_structure']);
        } else {
            $meta['fee_structure'] = $fee;
        }
        $institute->update(['profile_meta' => $meta !== [] ? $meta : null]);

        return response()->json([
            'message' => InstituteFeeStructure::isEmpty($fee) ? 'Fee structure removed from your page.' : 'Fee structure saved.',
            'reload' => $request->hasFile('fee_file') || $request->boolean('remove_fee_file'),
        ]);
    }
}
