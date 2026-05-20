@extends('backend.layouts.app')

@section('title', 'Ad Size Details')

@section('content')
@php
    $formatAmount = fn ($amount) => '₹'.number_format((float) $amount, 2);
@endphp
@php
    $modules = \App\Support\ModulePermissions::modules();
    $modulePrices = $size->modulePrices;

    if ($modulePrices->isEmpty() && $size->module_key && $size->module_price !== null) {
        $modulePrices = collect([
            (object) [
                'module_key' => $size->module_key,
                'amount' => $size->module_price,
                'created_at' => $size->created_at,
                'updated_at' => $size->updated_at,
            ],
        ]);
    }
@endphp

<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">{{ $size->name }}</h2>
            <p class="mb-0 text-secondary">View complete size details, category names, and prices.</p>
        </div>
        <a href="{{ route('admin.ads.sizes.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Back to Sizes
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1">Basic Details</h4>
                        <p class="text-secondary mb-0">Information saved for this ad size.</p>
                    </div>
                    @if($size->is_paid)
                        <span class="badge text-bg-primary">Paid</span>
                    @else
                        <span class="badge text-bg-secondary">Free</span>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light" style="width: 40%;">Name</th>
                                <td>{{ $size->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Key</th>
                                <td><code>{{ $size->size_key }}</code></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Width</th>
                                <td>{{ number_format((int) $size->width) }} px</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Height</th>
                                <td>{{ number_format((int) $size->height) }} px</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Dimensions</th>
                                <td>{{ $size->width }}×{{ $size->height }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Placement</th>
                                <td>
                                    @if($size->admin_only)
                                        <span class="badge text-bg-warning">Admin</span>
                                    @else
                                        <span class="badge text-bg-success">User</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Status</th>
                                <td>
                                    @if($size->is_active)
                                        <span class="badge text-bg-success">Active</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Base Amount</th>
                                <td>{{ $size->amount !== null ? $formatAmount($size->amount) : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Created</th>
                                <td>{{ $size->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Updated</th>
                                <td>{{ $size->updated_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1">Category Pricing</h4>
                        <p class="text-secondary mb-0">All category-specific prices configured for this ad size.</p>
                    </div>
                    <span class="badge text-bg-light border">{{ $size->categoryPrices->count() }} {{ \Illuminate\Support\Str::plural('category', $size->categoryPrices->count()) }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th class="text-end">Price</th>
                                <th>Added</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($size->categoryPrices as $price)
                                <tr>
                                    <td>{{ $price->category?->name ?? 'Deleted category' }}</td>
                                    <td class="text-end fw-semibold">{{ $formatAmount($price->amount) }}</td>
                                    <td>{{ $price->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td>{{ $price->updated_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No category pricing has been added for this size.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1">Module Pricing</h4>
                        <p class="text-secondary mb-0">All module-specific prices configured for this ad size.</p>
                    </div>
                    <span class="badge text-bg-light border">{{ $modulePrices->count() }} {{ \Illuminate\Support\Str::plural('module', $modulePrices->count()) }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Module Name</th>
                                <th class="text-end">Price</th>
                                <th>Added</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modulePrices as $price)
                                <tr>
                                    <td>{{ $modules[$price->module_key] ?? $price->module_key }}</td>
                                    <td class="text-end fw-semibold">{{ $formatAmount($price->amount) }}</td>
                                    <td>{{ $price->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td>{{ $price->updated_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No module pricing has been added for this size.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>
</div>
@endsection
