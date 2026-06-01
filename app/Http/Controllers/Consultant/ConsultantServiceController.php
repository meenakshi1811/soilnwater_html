<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ConsultantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsultantServiceController extends Controller
{
    public function index(): View
    {
        $services = ConsultantService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name'])
            ->where('consultant_id', auth()->user()->consultant->id)
            ->latest()
            ->get();

        return view('backend.consultant.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('backend.consultant.services.form', [
            'service' => new ConsultantService(),
            'categories' => $this->consultantCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['consultant_id'] = auth()->user()->consultant->id;
        $data['slug'] = $this->uniqueSlug($data['consultant_id'], $data['name']);

        ConsultantService::create($data);

        return redirect()->route('consultant.services.index')
            ->with('success', 'Consultation service submitted successfully and sent for admin approval.');
    }

    public function show(ConsultantService $service): View
    {
        $this->authorizeOwner($service);
        $service->load(['categoryModel:id,name', 'subcategoryModel:id,name']);

        return view('backend.consultant.services.show', compact('service'));
    }

    public function edit(ConsultantService $service): View
    {
        $this->authorizeOwner($service);

        return view('backend.consultant.services.form', [
            'service' => $service,
            'categories' => $this->consultantCategories(),
        ]);
    }

    public function update(Request $request, ConsultantService $service)
    {
        $this->authorizeOwner($service);
        $data = $this->validated($request, $service);
        $data['slug'] = $this->uniqueSlug($service->consultant_id, $data['name'], $service->id);

        $service->update($data);

        return redirect()->route('consultant.services.index')->with('success', 'Consultation service updated successfully.');
    }

    public function destroy(ConsultantService $service): JsonResponse
    {
        $this->authorizeOwner($service);
        $service->delete();

        return response()->json(['ok' => true]);
    }

    private function authorizeOwner(ConsultantService $service): void
    {
        abort_unless($service->consultant_id === auth()->user()->consultant?->id, 403);
    }

    private function consultantCategories()
    {
        return Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'consultants')
            ->with(['children' => fn ($q) => $q->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function validated(Request $request, ?ConsultantService $service = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('parent_id', $request->input('category_id')))],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_online' => ['nullable', 'boolean'],
            'images.*' => ['nullable', 'image', 'max:4096'],
            'accept_terms' => [$service?->exists ? 'nullable' : 'accepted'],
        ]);

        $validated['category'] = Category::find($validated['category_id'])?->name;
        $validated['is_online'] = $request->boolean('is_online');

        if ($request->hasFile('images')) {
            $directory = public_path('uploads/consultant-services/images');
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $validated['images'] = [];
            foreach ($request->file('images') as $file) {
                $filename = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
                $file->move($directory, $filename);
                $validated['images'][] = 'uploads/consultant-services/images/'.$filename;
            }
        } elseif ($service?->exists) {
            unset($validated['images']);
        } else {
            $validated['images'] = [];
        }

        unset($validated['accept_terms']);
        $validated['status'] = 'pending';
        $validated['approved_at'] = null;
        $validated['approved_by'] = null;

        return $validated;
    }

    private function uniqueSlug(int $consultantId, string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $counter = 1;

        while (ConsultantService::query()
            ->where('consultant_id', $consultantId)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
