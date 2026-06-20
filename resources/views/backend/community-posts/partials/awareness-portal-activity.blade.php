@php
    $awarenessEngagement = $awarenessEngagement ?? [
        'supports_count' => 0,
        'pledges_count' => 0,
        'volunteers_count' => 0,
        'user_supported' => false,
        'user_pledge' => null,
    ];
    $awarenessEngagementActivity = $awarenessEngagementActivity ?? null;
    $awarenessPledgeCounts = collect($awarenessPledgeCounts ?? []);
    $showVolunteerContacts = $showVolunteerContacts ?? false;
@endphp

@if($post->isAwarenessPost() && ($awarenessEngagement || $awarenessEngagementActivity))
    <div class="chart-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Awareness campaign activity</h5>
                <p class="text-muted small mb-0">Supporters, pledges, and volunteers for this campaign.</p>
            </div>
            @if($post->isPubliclyVisible())
                <a href="{{ route('community.show', $post) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                    View public page
                </a>
            @endif
        </div>

        <div class="row g-2 mb-3 small">
            <div class="col-4">
                <div class="border rounded p-2 bg-light h-100 text-center">
                    <div class="fw-bold fs-5 text-success">{{ number_format($awarenessEngagement['supports_count'] ?? 0) }}</div>
                    <div class="text-muted">Supporters</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2 bg-light h-100 text-center">
                    <div class="fw-bold fs-5 text-primary">{{ number_format($awarenessEngagement['pledges_count'] ?? 0) }}</div>
                    <div class="text-muted">Pledges</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2 bg-light h-100 text-center">
                    <div class="fw-bold fs-5 text-info">{{ number_format($awarenessEngagement['volunteers_count'] ?? 0) }}</div>
                    <div class="text-muted">Volunteers</div>
                </div>
            </div>
        </div>

        @if($awarenessPledgeCounts->isNotEmpty())
            <h6 class="small text-uppercase text-muted mt-2 mb-2">Pledge breakdown</h6>
            <div class="d-flex flex-column gap-1 small mb-3">
                @foreach($awarenessPledgeCounts as $pledgeRow)
                    <div class="d-flex justify-content-between gap-2 border-bottom pb-1">
                        <span>{{ $pledgeRow['pledge_text'] }}</span>
                        <strong>{{ number_format($pledgeRow['total']) }}</strong>
                    </div>
                @endforeach
            </div>
        @endif

        @if($awarenessEngagementActivity)
            @foreach([
                'supports' => 'Recent supporters',
                'pledges' => 'Recent pledges',
            ] as $key => $label)
                @if($awarenessEngagementActivity[$key]->isNotEmpty())
                    <h6 class="small text-uppercase text-muted mt-3 mb-2">{{ $label }}</h6>
                    <ul class="list-unstyled small mb-0">
                        @foreach($awarenessEngagementActivity[$key] as $item)
                            <li class="mb-1">
                                {{ $item->user?->full_name ?: ($item->user?->name ?? 'Community member') }}
                                @if($key === 'pledges')
                                    <span class="text-muted">· {{ $item->pledge_text }}</span>
                                @endif
                                <span class="text-muted">· {{ $item->created_at?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach

            @if($awarenessEngagementActivity['volunteers']->isNotEmpty())
                <h6 class="small text-uppercase text-muted mt-3 mb-2">Campaign volunteers</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                @if($showVolunteerContacts)
                                    <th>Email</th>
                                    <th>City</th>
                                @endif
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($awarenessEngagementActivity['volunteers'] as $volunteer)
                                <tr>
                                    <td>{{ $volunteer->name }}</td>
                                    <td>{{ $volunteer->mobile }}</td>
                                    @if($showVolunteerContacts)
                                        <td>{{ $volunteer->email ?: '—' }}</td>
                                        <td>{{ $volunteer->city ?: '—' }}</td>
                                    @endif
                                    <td class="text-muted">{{ $volunteer->created_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
@endif
