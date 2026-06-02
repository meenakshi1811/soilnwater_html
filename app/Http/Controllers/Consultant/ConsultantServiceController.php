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

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Consultation service submitted successfully and sent for admin approval.',
                'redirect' => route('consultant.services.index'),
            ]);
        }

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
            'consultation_type' => ['required', Rule::in(['online', 'offline', 'both'])],
            'business_type' => ['required', Rule::in(['Architect', 'Lawyer', 'Landscaper', 'Software Consultant', 'Business'])],
            'service_area' => ['nullable', 'string', 'max:1000'],
            'consultation_charges' => ['required', 'array'],
            'consultation_charges.minute' => ['nullable', 'numeric', 'min:0', 'required_without_all:consultation_charges.hour,consultation_charges.day,consultation_charges.month,consultation_charges.contractual'],
            'consultation_charges.hour' => ['nullable', 'numeric', 'min:0'],
            'consultation_charges.day' => ['nullable', 'numeric', 'min:0'],
            'consultation_charges.month' => ['nullable', 'numeric', 'min:0'],
            'consultation_charges.contractual' => ['nullable', 'numeric', 'min:0'],
            'charges_detail' => ['nullable', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_online' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'accept_terms' => [$service?->exists ? 'nullable' : 'accepted'],
        ]);

        $validated['category'] = Category::find($validated['category_id'])?->name;
        $validated['consultation_charges'] = collect($validated['consultation_charges'])
            ->only(['minute', 'hour', 'day', 'month', 'contractual'])
            ->filter(fn ($amount): bool => $amount !== null && $amount !== '')
            ->map(fn ($amount): float => (float) $amount)
            ->all();
        $validated['price'] = collect($validated['consultation_charges'])->first() ?? 0;
        $validated['duration'] = collect(array_keys($validated['consultation_charges']))
            ->map(fn (string $unit): string => $unit === 'contractual' ? 'contractual' : Str::plural($unit))
            ->implode(', ');
        $validated['is_online'] = in_array($validated['consultation_type'], ['online', 'both'], true);

        if ($request->hasFile('image')) {
            $directory = public_path('uploads/consultant-services/images');
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $file = $request->file('image');
            $filename = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $file->move($directory, $filename);
            $validated['image_path'] = 'uploads/consultant-services/images/'.$filename;
        } elseif ($service?->exists) {
            unset($validated['image_path']);
        } else {
            $validated['image_path'] = null;
        }

        unset($validated['image']);

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
