<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ServiceProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceProviderServiceController extends Controller
{
    public function index(): View
    {
        $services = ServiceProviderService::query()
            ->with(['categoryModel:id,name', 'subcategoryModel:id,name'])
            ->where('service_provider_id', auth()->user()->serviceProvider->id)
            ->latest()
            ->get();

        return view('backend.service_provider.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('backend.service_provider.services.form', [
            'service' => new ServiceProviderService(),
            'categories' => $this->service_providerCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['service_provider_id'] = auth()->user()->serviceProvider->id;
        $data['slug'] = $this->uniqueSlug($data['service_provider_id'], $data['name']);

        ServiceProviderService::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Service submitted successfully and sent for admin approval.',
                'redirect' => route('service_provider.services.index'),
            ]);
        }

        return redirect()->route('service_provider.services.index')
            ->with('success', 'Service submitted successfully and sent for admin approval.');
    }

    public function show(ServiceProviderService $service): View
    {
        $this->authorizeOwner($service);
        $service->load(['categoryModel:id,name', 'subcategoryModel:id,name']);

        return view('backend.service_provider.services.show', compact('service'));
    }

    public function edit(ServiceProviderService $service): View
    {
        $this->authorizeOwner($service);

        return view('backend.service_provider.services.form', [
            'service' => $service,
            'categories' => $this->service_providerCategories(),
        ]);
    }

    public function update(Request $request, ServiceProviderService $service)
    {
        $this->authorizeOwner($service);
        $data = $this->validated($request, $service);
        $data['slug'] = $this->uniqueSlug($service->service_provider_id, $data['name'], $service->id);

        $service->update($data);

        return redirect()->route('service_provider.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(ServiceProviderService $service): JsonResponse
    {
        $this->authorizeOwner($service);
        $service->delete();

        return response()->json(['ok' => true]);
    }

    private function authorizeOwner(ServiceProviderService $service): void
    {
        abort_unless($service->service_provider_id === auth()->user()->serviceProvider?->id, 403);
    }

    private function service_providerCategories()
    {
        return Category::query()
            ->whereNull('parent_id')
            ->forModule('service_providers')
            ->with(['children' => fn ($q) => $q->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function validated(Request $request, ?ServiceProviderService $service = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('parent_id', $request->input('category_id')))],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'consultation_type' => ['required', Rule::in(['online', 'offline', 'both'])],
            'business_type' => ['required', Rule::in(['Architect', 'Lawyer', 'Landscaper', 'Software Service', 'Business'])],
            'service_area' => ['nullable', 'string', 'max:1000'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'service_radius' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'working_hours' => ['nullable', 'string', 'max:1000'],
            'charge_duration' => ['nullable', 'array'],
            'charge_duration.*' => ['nullable', Rule::in(['minute', 'hour', 'day', 'month', 'contractual'])],
            'charge_price' => ['nullable', 'array'],
            'charge_price.*' => ['nullable', 'numeric', 'min:0'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_online' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'accept_terms' => [$service?->exists ? 'nullable' : 'accepted'],
        ]);

        $validated['category'] = Category::find($validated['category_id'])?->name;
        $chargeRows = collect($request->input('charge_duration', []))
            ->map(fn ($duration, $idx) => [
                'duration' => (string) $duration,
                'price' => $request->input('charge_price.'.$idx),
            ]);

        if ($chargeRows->contains(fn (array $row): bool => ($row['duration'] === '' && $row['price'] !== null && $row['price'] !== '') || ($row['duration'] !== '' && ($row['price'] === null || $row['price'] === '')))) {
            throw ValidationException::withMessages([
                'charge_duration.0' => 'Each charge row must include both duration and price.',
            ]);
        }

        $validChargeRows = $chargeRows
            ->filter(fn (array $row): bool => $row['duration'] !== '' && $row['price'] !== null && $row['price'] !== '')
            ->values();

        $validated['consultation_charges'] = $validChargeRows
            ->map(fn (array $row): array => [
                'duration' => $row['duration'],
                'price' => (float) $row['price'],
            ])
            ->all();
        $validated['consultation_charge_notes'] = [];

        if (empty($validated['consultation_charges'])) {
            throw ValidationException::withMessages([
                'charge_duration.0' => 'Please add at least one service duration and price.',
            ]);
        }

        $validated['price'] = collect($validated['consultation_charges'])->first()['price'] ?? 0;
        $validated['duration'] = collect($validated['consultation_charges'])
            ->pluck('duration')
            ->unique()
            ->map(fn (string $unit): string => $unit === 'contractual' ? 'contractual' : Str::plural($unit))
            ->implode(', ');
        $validated['is_online'] = in_array($validated['consultation_type'], ['online', 'both'], true);
        unset($validated['charge_duration'], $validated['charge_price']);

        if ($request->hasFile('image')) {
            $directory = public_path('uploads/service-provider-services/images');
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $file = $request->file('image');
            $filename = time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $file->move($directory, $filename);
            $validated['image_path'] = 'uploads/service-provider-services/images/'.$filename;
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

    private function uniqueSlug(int $service_providerId, string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $counter = 1;

        while (ServiceProviderService::query()
            ->where('service_provider_id', $service_providerId)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
