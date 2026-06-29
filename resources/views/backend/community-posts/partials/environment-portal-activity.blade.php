@php
    $environmentEngagement = $environmentEngagement ?? [
        'supports_count' => 0,
        'follows_count' => 0,
        'volunteers_count' => 0,
        'user_supported' => false,
        'user_following' => false,
        'user_volunteered' => false,
    ];
    $environmentEngagementActivity = $environmentEngagementActivity ?? null;
    $showVolunteerContacts = $showVolunteerContacts ?? false;
@endphp

@if($post->isEnvironmentPost() && ($environmentEngagement || $environmentEngagementActivity))
    <div class="chart-card p-3 p-lg-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Environment campaign activity</h5>
                <p class="text-muted small mb-0">Supporters, followers, and volunteers for this conservation post.</p>
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
                    <div class="fw-bold fs-5 text-success">{{ number_format($environmentEngagement['supports_count'] ?? 0) }}</div>
                    <div class="text-muted">Supporters</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2 bg-light h-100 text-center">
                    <div class="fw-bold fs-5 text-primary">{{ number_format($environmentEngagement['follows_count'] ?? 0) }}</div>
                    <div class="text-muted">Followers</div>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2 bg-light h-100 text-center">
                    <div class="fw-bold fs-5 text-info">{{ number_format($environmentEngagement['volunteers_count'] ?? 0) }}</div>
                    <div class="text-muted">Volunteers</div>
                </div>
            </div>
        </div>

        @if($environmentEngagementActivity)
            @foreach([
                'supports' => 'Recent supporters',
                'follows' => 'Recent followers',
            ] as $key => $label)
                @if($environmentEngagementActivity[$key]->isNotEmpty())
                    <h6 class="small text-uppercase text-muted mt-3 mb-2">{{ $label }}</h6>
                    <ul class="list-unstyled small mb-0">
                        @foreach($environmentEngagementActivity[$key] as $item)
                            <li class="mb-1">
                                {{ $item->user?->full_name ?: ($item->user?->name ?? 'Community member') }}
                                <span class="text-muted">· {{ $item->created_at?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach

            @if($environmentEngagementActivity['volunteers']->isNotEmpty())
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
                                    <th>Interest</th>
                                @endif
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($environmentEngagementActivity['volunteers'] as $volunteer)
                                <tr>
                                    <td>{{ $volunteer->name }}</td>
                                    <td>{{ $volunteer->mobile }}</td>
                                    @if($showVolunteerContacts)
                                        <td>{{ $volunteer->email ?: '—' }}</td>
                                        <td>{{ $volunteer->city ?: '—' }}</td>
                                        <td>{{ $volunteer->interest ?: '—' }}</td>
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
