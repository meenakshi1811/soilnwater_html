@php
    $astroConsultancyEngagement = $astroConsultancyEngagement ?? [
        'queries_count' => 0,
        'user_submitted' => false,
    ];
    $astroConsultancyEngagementActivity = $astroConsultancyEngagementActivity ?? null;
    $showQueryContacts = $showQueryContacts ?? false;
@endphp

@if($post->isAstroConsultancyPost() && ($astroConsultancyEngagement || $astroConsultancyEngagementActivity))
    <div class="chart-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Astro consultancy engagement</h5>
                <p class="text-muted small mb-0">Private consultation requests and community interest for this post.</p>
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
                    <div class="fw-bold fs-5 text-primary">{{ number_format($astroConsultancyEngagement['queries_count'] ?? 0) }}</div>
                    <div class="text-muted">Private requests</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2 bg-light h-100 text-center">
                    <div class="fw-bold fs-5 text-warning">{{ $post->astroEnablesLiveQa() ? 'Yes' : 'No' }}</div>
                    <div class="text-muted">Live Q&amp;A</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2 bg-light h-100 text-center">
                    <div class="fw-bold fs-5 text-info">{{ $post->astroEnablesConsultantLinking() ? 'Yes' : 'No' }}</div>
                    <div class="text-muted">Consultant link</div>
                </div>
            </div>
        </div>

        @if($astroConsultancyEngagementActivity && $astroConsultancyEngagementActivity['queries']->isNotEmpty())
            <h6 class="small text-uppercase text-muted mt-3 mb-2">Recent private requests</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            @if($showQueryContacts)
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Message</th>
                            @endif
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($astroConsultancyEngagementActivity['queries'] as $query)
                            <tr>
                                <td>{{ $query->name }}</td>
                                <td>{{ $query->query_type }}</td>
                                @if($showQueryContacts)
                                    <td>{{ $query->email ?: '—' }}</td>
                                    <td>{{ $query->mobile ?: '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($query->message, 120) }}</td>
                                @endif
                                <td class="text-muted">{{ $query->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
