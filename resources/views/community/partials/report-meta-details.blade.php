@php
    $orderedReportMeta = \App\Support\CommunityPostFormFields::orderedReportMetaForDisplay($post, $includeLocation ?? false);
    $reportMetaLabels = \App\Support\CommunityPostFormFields::reportDetailMetaOrder();
    if ($includeLocation ?? false) {
        $reportMetaLabels['location'] = 'GPS issue location';
    }
    $narrativeKeys = \App\Support\CommunityPostFormFields::narrativeReportMetaKeys();
    $issueAttachments = data_get($post->meta, 'issue_attachments', []);
@endphp

@if($orderedReportMeta->isNotEmpty() || !empty($issueAttachments))
    @php
        $sidebarLayout = $sidebarLayout ?? false;
    @endphp
    <div @class([
        'about-box mt-4' => ! $sidebarLayout,
        'community-news-sidebar__card community-news-sidebar__card--report-details' => $sidebarLayout,
    ])>
        @if($sidebarLayout)
            <p class="community-news-sidebar__label">{{ $heading ?? 'Report details' }}</p>
        @else
            <h4>{{ $heading ?? 'Report details' }}</h4>
        @endif
        @if($orderedReportMeta->isNotEmpty())
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
