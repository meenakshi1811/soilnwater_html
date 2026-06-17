<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Category;
use App\Models\ConsultantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait ValidatesConsultantServiceRequest
{
    protected function consultantCategories()
    {
        return Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'consultants')
            ->with(['children' => fn ($q) => $q->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function validatedConsultantService(Request $request, ?ConsultantService $service = null): array
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
            'charge_duration' => ['nullable', 'array'],
            'charge_duration.*' => ['nullable', Rule::in(['minute', 'hour', 'day', 'month', 'contractual'])],
            'charge_price' => ['nullable', 'array'],
            'charge_price.*' => ['nullable', 'numeric', 'min:0'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_online' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'accept_terms' => ['nullable', 'accepted'],
        ]);

        $validated['category'] = Category::find($validated['category_id'])?->name;
        $chargeRows = collect($request->input('charge_duration', []))
            ->map(fn ($duration, $idx) => [
                'duration' => (string) $duration,
                'price' => $request->input('charge_price.'.$idx),
            ]);

        if ($chargeRows->contains(fn (array $row): bool => ($row['duration'] === '' && $row['price'] !== null && $row['price'] !== '') || ($row['duration'] !== '' && ($row['price'] === null || $row['price'] === '')))) {
            throw ValidationException::withMessages([
                'charge_duration.0' => 'Each consultation charge row must include both duration and price.',
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
                'charge_duration.0' => 'Please add at least one consultation duration and price.',
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

        unset($validated['image'], $validated['accept_terms']);
        $validated['status'] = 'pending';
        $validated['approved_at'] = null;
        $validated['approved_by'] = null;

        return $validated;
    }

    protected function uniqueConsultantServiceSlug(int $consultantId, string $name, ?int $exceptId = null): string
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
