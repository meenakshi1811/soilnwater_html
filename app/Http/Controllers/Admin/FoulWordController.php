<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoulWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class FoulWordController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.foul-words.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $words = FoulWord::query()
            ->select(['id', 'word', 'is_active', 'created_at', 'updated_at'])
            ->latest();

        return DataTables::of($words)
            ->addColumn('status_badge', function (FoulWord $word): string {
                return $word->is_active
                    ? '<span class="badge text-bg-success">Active</span>'
                    : '<span class="badge text-bg-secondary">Inactive</span>';
            })
            ->addColumn('status_toggle', function (FoulWord $word): string {
                $checked = $word->is_active ? 'checked' : '';

                return '<div class="form-check form-switch m-0 d-flex justify-content-center">'
                    .'<input class="form-check-input js-toggle-foul-word" type="checkbox" role="switch" data-id="'.$word->id.'" '.$checked.'>'
                    .'</div>';
            })
            ->editColumn('created_at', fn (FoulWord $word): string => $word->created_at?->format('d M Y H:i') ?? '—')
            ->addColumn('actions', function (FoulWord $word): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<button type="button" class="btn btn-sm btn-outline-primary js-edit-foul-word" data-id="'.$word->id.'"><i class="fa-solid fa-pen"></i></button>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-delete-foul-word" data-id="'.$word->id.'"><i class="fa-solid fa-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['status_badge', 'status_toggle', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateWord($request);

        FoulWord::query()->create([
            'word' => $validated['word'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['message' => 'Foul word added successfully.']);
    }

    public function show(FoulWord $foulWord): JsonResponse
    {
        return response()->json([
            'foul_word' => [
                'id' => $foulWord->id,
                'word' => $foulWord->word,
                'is_active' => $foulWord->is_active,
            ],
        ]);
    }

    public function update(Request $request, FoulWord $foulWord): JsonResponse
    {
        $validated = $this->validateWord($request, $foulWord);

        $foulWord->update([
            'word' => $validated['word'],
            'is_active' => $request->boolean('is_active', $foulWord->is_active),
        ]);

        return response()->json(['message' => 'Foul word updated successfully.']);
    }

    public function toggleStatus(FoulWord $foulWord): JsonResponse
    {
        $foulWord->is_active = ! $foulWord->is_active;
        $foulWord->save();

        return response()->json([
            'message' => $foulWord->is_active
                ? 'Foul word is now active.'
                : 'Foul word is now inactive.',
            'is_active' => $foulWord->is_active,
        ]);
    }

    public function destroy(FoulWord $foulWord): JsonResponse
    {
        $foulWord->delete();

        return response()->json(['message' => 'Foul word deleted successfully.']);
    }

    private function validateWord(Request $request, ?FoulWord $foulWord = null): array
    {
        $word = mb_strtolower(trim((string) $request->input('word')));
        $request->merge(['word' => $word]);

        return $request->validate([
            'word' => [
                'required',
                'string',
                'max:80',
                Rule::unique('foul_words', 'word')->ignore($foulWord?->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
