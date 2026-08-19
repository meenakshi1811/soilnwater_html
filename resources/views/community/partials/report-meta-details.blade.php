@php
    $orderedReportMeta = \App\Support\CommunityPostFormFields::orderedReportMetaForDisplay($post, $includeLocation ?? false);
    $reportMetaLabels = \App\Support\CommunityPostFormFields::reportDetailMetaOrder();
    if ($includeLocation ?? false) {
        $reportMetaLabels['location'] = 'GPS issue location';
    }
    $narrativeKeys = \App\Support\CommunityPostFormFields::narrativeReportMetaKeys();
    $issueAttachments = data_get($post->meta, 'issue_attachments', []);
    $sidebarLayout = $sidebarLayout ?? false;
    $narrativeOnly = $narrativeOnly ?? false;

    if ($sidebarLayout) {
        $orderedReportMeta = $orderedReportMeta->reject(
            fn (mixed $value, string $key): bool => in_array($key, $narrativeKeys, true)
        );
    }

    if ($narrativeOnly) {
        $orderedReportMeta = $orderedReportMeta->only($narrativeKeys);
    }
@endphp

@if($orderedReportMeta->isNotEmpty() || (! $sidebarLayout && ! $narrativeOnly && !empty($issueAttachments)))
    <div @class([
        'about-box mt-4' => ! $sidebarLayout && ! $narrativeOnly,
        'community-news-sidebar__card community-news-sidebar__card--report-details' => $sidebarLayout,
        'community-report-narrative mt-4' => $narrativeOnly,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? 'Report details' }}</p>
        @elseif($narrativeOnly)
            <h4 class="community-report-narrative__title">{{ $heading ?? 'Report summary' }}</h4>
        @else
            <h4>{{ $heading ?? 'Report details' }}</h4>
        @endif
        @if($orderedReportMeta->isNotEmpty())
            @if($narrativeOnly)
                <div class="community-report-narrative__sections">
                    @foreach($orderedReportMeta as $key => $value)
                        @php
                            $displayValue = \App\Support\CommunityPostFormFields::formatReportMetaValue($key, $value);
                        @endphp
                        <section class="community-report-narrative__section">
                            <h5 class="community-report-narrative__heading">{{ $reportMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</h5>
                            <div class="community-report-narrative__body">{!! nl2br(e($displayValue)) !!}</div>
                        </section>
                    @endforeach
                </div>
            @else
            <div @class([
                'row g-3' => ! $sidebarLayout,
                'news-sidebar-meta-grid' => $sidebarLayout,
                'mb-3' => ! $sidebarLayout && ! empty($issueAttachments),
            ])>
                @foreach($orderedReportMeta as $key => $value)
                    @php
                        $displayValue = \App\Support\CommunityPostFormFields::formatReportMetaValue($key, $value);
                        $isNarrativeField = in_array($key, $narrativeKeys, true);
                    @endphp
                    <div @class([
                        $isNarrativeField ? 'col-12' : 'col-md-6' => ! $sidebarLayout,
                        'news-sidebar-meta-grid__item' => $sidebarLayout,
                        'news-sidebar-meta-grid__item--wide' => $sidebarLayout && $isNarrativeField,
                    ])>
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ $reportMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            <span>{!! nl2br(e($displayValue)) !!}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        @endif
        @if(!empty($issueAttachments) && ! ($hideAttachments ?? false))
            <h5 class="h6">Evidence files</h5>
            <div class="d-flex flex-wrap gap-2">
                @foreach($issueAttachments as $attachment)
                    <a href="{{ data_get($attachment, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-paperclip me-1"></i>{{ data_get($attachment, 'name', 'Attachment') }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif
