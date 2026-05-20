@extends('backend.layouts.app')

@section('title', 'View Ad')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">View Ad</h2>
            <p class="mb-0 text-secondary">
                <strong>{{ $ad->title }}</strong> ·
                {{ $size['name'] ?? $ad->size_type }}
            </p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="chart-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="mb-0">Rendered Preview</h5>
                    <span class="badge bg-{{ $ad->status === 'approved' ? 'success' : ($ad->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($ad->status) }}</span>
                </div>

                @if($ad->final_image)
                    <div class="mb-3">
                        <img src="{{ asset($ad->final_image) }}" alt="Final ad image" class="img-fluid rounded border">
                    </div>
                @endif

                <!-- <div class="ads-live-preview" style="aspect-ratio: {{ $size['ratio'] ?? '1 / 1' }};">
                    <div class="ads-live-preview-inner">
                        {!! $ad->rendered_html ?: '<div class="text-secondary p-3">No rendered HTML saved.</div>' !!}
                    </div>
                </div> -->
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="chart-card">
                <h5 class="mb-3">Submission Details</h5>
                <div class="mb-2"><span class="text-secondary">Short Description:</span> <strong>{{ $ad->short_description ?: '-' }}</strong></div>
                <div class="mb-2"><span class="text-secondary">Category:</span> <strong>{{ $ad->category?->name ?? '-' }}</strong></div>
                <div class="mb-2"><span class="text-secondary">Sub Category:</span> <strong>{{ $ad->subcategory?->name ?? '-' }}</strong></div>
                <div class="mb-2">
                    <span class="text-secondary">Selected Modules:</span>
                    @if(!empty($selectedModuleLabels))
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            @foreach($selectedModuleLabels as $moduleLabel)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $moduleLabel }}</span>
                            @endforeach
                        </div>
                    @else
                        <strong>-</strong>
                    @endif
                </div>
                <div class="mb-2"><span class="text-secondary">Location:</span> <strong>{{ $ad->location ?? '-' }}</strong></div>
                <div class="mb-2"><span class="text-secondary">Valid Upto:</span> {{ $ad->valid_until?->format('Y-m-d') ?? '-' }}</div>
                <div class="mb-2"><span class="text-secondary">Submitted:</span> {{ $ad->submitted_at?->format('Y-m-d H:i') ?? '-' }}</div>
                <div class="mb-2"><span class="text-secondary">Reviewed:</span> {{ $ad->reviewed_at?->format('Y-m-d H:i') ?? '-' }}</div>
                <div class="mb-2"><span class="text-secondary">Status:</span> <strong>{{ ucfirst($ad->status) }}</strong></div>

                @if($ad->grand_total !== null)
                    <hr>
                    <h6 class="mb-3">Pricing Details</h6>
                    <div class="mb-2"><span class="text-secondary">Base price / day:</span> <strong>₹{{ number_format((float) $ad->base_price_per_day, 2) }}</strong></div>
                    <div class="mb-2"><span class="text-secondary">Total days:</span> <strong>{{ $ad->total_days }}</strong></div>
                    <div class="mb-2"><span class="text-secondary">Subtotal (Base × Days):</span> <strong>₹{{ number_format((float) $ad->subtotal, 2) }}</strong></div>
                    <div class="mb-2"><span class="text-secondary">GST ({{ rtrim(rtrim(number_format((float) $ad->gst_rate, 2), '0'), '.') }}%):</span> <strong>₹{{ number_format((float) $ad->gst_amount, 2) }}</strong></div>
                    <div class="mb-2"><span class="text-secondary">Grand Total:</span> <strong class="fs-5">₹{{ number_format((float) $ad->grand_total, 2) }}</strong></div>
                @endif

                <hr>

                <div class="alert {{ $ad->review_note ? 'alert-secondary' : 'alert-light' }} mb-0">
                    <div class="fw-semibold mb-1">Review reason/note</div>
                    <div>{{ $ad->review_note ?: 'No reason added yet.' }}</div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('ads.index') }}" class="btn btn-light px-4">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
