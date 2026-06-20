@php
    $businessEngagement = $businessEngagement ?? ['queries_count' => 0, 'user_submitted' => false];
    $businessEngagementActivity = $businessEngagementActivity ?? null;
    $showQueryContacts = $showQueryContacts ?? false;
@endphp

@if($post->isBusinessPost() && ($businessEngagement || $businessEngagementActivity))
    <div class="chart-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Business inquiry activity</h5>
                <p class="text-muted small mb-0">Contact requests and business queries from readers.</p>
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
                    <div class="fw-bold fs-5 text-warning">{{ number_format($businessEngagement['queries_count'] ?? 0) }}</div>
                    <div class="text-muted">Inquiries</div>
                </div>
            </div>
        </div>

        @if($businessEngagementActivity && $businessEngagementActivity['queries']->isNotEmpty())
            <h6 class="small text-uppercase text-muted mt-2 mb-2">Recent inquiries</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            @if($showQueryContacts)
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Message</th>
                            @endif
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($businessEngagementActivity['queries'] as $query)
                            <tr>
                                <td>{{ $query->query_type }}</td>
                                <td>{{ $query->name }}</td>
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
        @else
            <p class="text-muted small mb-0">No inquiries yet.</p>
        @endif
    </div>
@endif
