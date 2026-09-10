@extends('backend.layouts.app')

@section('title', 'Parent Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/parent-profile.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
    $displayName = $user->full_name ?: $user->name;
    $location = $parentProfile->location ?: trim(($user->city ?? '').(($user->city && $user->pincode) ? ', ' : '').($user->pincode ?? ''));
    $languages = is_array($parentProfile->languages) ? $parentProfile->languages : [];
    $memberSince = $user->created_at?->format('M Y');
    $avatarUrl = filled($user->profile_image) ? asset($user->profile_image) : null;
@endphp

<div class="admin-panel parent-dashboard">
    {{-- Header profile summary --}}
    <div class="parent-hero-card mb-4">
        <div class="row g-4 align-items-center position-relative">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-start gap-3">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="parent-avatar">
                    @else
                        <div class="parent-avatar d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold">{{ $user->authorInitials() }}</div>
                    @endif
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h2 class="admin-title mb-0">{{ $displayName }}</h2>
                            <span class="parent-badge"><i class="fa-solid fa-circle-check"></i> Verified Parent</span>
                            <span class="parent-role-badge">Parent / Guardian</span>
                        </div>
                        <div class="text-secondary small d-flex flex-wrap gap-3 mb-2">
                            @if($location)
                                <span><i class="fa-solid fa-location-dot me-1"></i>{{ $location }}</span>
                            @endif
                            @if(count($languages))
                                <span><i class="fa-solid fa-language me-1"></i>{{ implode(', ', $languages) }}</span>
                            @endif
                            <span><i class="fa-solid fa-children me-1"></i>{{ $stats['children'] }} {{ Str::plural('Child', $stats['children']) }}</span>
                        </div>
                        <div class="small text-muted mb-2">
                            @if($memberSince)
                                Member since {{ $memberSince }}
                            @endif
                            · Profile completion {{ $parentProfile->profile_completion ?? 0 }}%
                        </div>
                        @if(filled($parentProfile->bio))
                            <p class="mb-0 text-secondary">{{ $parentProfile->bio }}</p>
                        @else
                            <p class="mb-0 text-secondary">Manage your children's learning journey, track progress, and connect with teachers and study resources.</p>
                        @endif
                    </div>
                </div>

                <div class="parent-stat-bar">
                    <div class="parent-stat-item">
                        <div class="parent-stat-value">{{ $stats['children'] }}</div>
                        <div class="parent-stat-label">Children</div>
                    </div>
                    <div class="parent-stat-item">
                        <div class="parent-stat-value">{{ $stats['materials_saved'] }}</div>
                        <div class="parent-stat-label">Materials Saved</div>
                    </div>
                    <div class="parent-stat-item">
                        <div class="parent-stat-value">{{ $stats['enquiries'] }}</div>
                        <div class="parent-stat-label">Enquiries</div>
                    </div>
                    <div class="parent-stat-item">
                        <div class="parent-stat-value">{{ $stats['discussions'] }}</div>
                        <div class="parent-stat-label">Discussions</div>
                    </div>
                    <div class="parent-stat-item">
                        <div class="parent-stat-value">{{ $stats['following'] }}</div>
                        <div class="parent-stat-label">Following</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editParentProfileModal">
                        <i class="fa-solid fa-pen me-1"></i>Edit Profile
                    </button>
                    <button type="button" class="btn btn-primary js-open-add-child">
                        <i class="fa-solid fa-plus me-1"></i>Add Child
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- My Children --}}
        <div class="col-xl-8">
            <div class="parent-section-card">
                <div class="parent-section-head">
                    <h3>My Children</h3>
                    @if($children->isNotEmpty())
                        <button type="button" class="btn btn-sm btn-link text-decoration-none js-open-add-child">+ Add Child</button>
                    @endif
                </div>

                @if($children->isEmpty())
                    <div class="children-empty-state js-open-add-child" role="button" tabindex="0" aria-label="Add your first child profile">
                        <span class="children-empty-state__icon">
                            <i class="fa-solid fa-user-graduate"></i>
                        </span>
                        <h4 class="children-empty-state__title">No child profiles yet</h4>
                        <p class="children-empty-state__text">Add your first child with email, phone number, and login credentials. Admin approval is required before they can sign in.</p>
                        <span class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus me-1"></i>Add Child
                        </span>
                    </div>
                @else
                    <div class="child-card-grid">
                        @foreach($children as $child)
                            @php
                                $childAvatar = filled($child->profile_image) ? asset($child->profile_image) : null;
                                $childInitials = collect(preg_split('/\s+/', trim($child->full_name)) ?: [])->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'CH';
                            @endphp
                            <div class="child-card {{ $child->is_primary ? 'is-primary' : '' }}" data-child-id="{{ $child->id }}">
                                <div class="child-card-head">
                                    @if($childAvatar)
                                        <img src="{{ $childAvatar }}" alt="{{ $child->full_name }}" class="child-avatar">
                                    @else
                                        <div class="child-avatar d-flex align-items-center justify-content-center bg-light text-secondary fw-semibold small">{{ $childInitials }}</div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <strong>{{ $child->full_name }}</strong>
                                            <i class="fa-solid {{ $child->genderIcon() }}"></i>
                                            @if($child->is_primary)
                                                <span class="badge text-bg-success">Primary Child</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            {{ trim(($child->class_grade ?: '').($child->board ? ' - '.$child->board : '')) ?: 'Class details pending' }}
                                        </div>
                                        @if($child->school_name)
                                            <div class="small text-secondary">{{ $child->school_name }}</div>
                                        @endif
                                    </div>
                                    <span class="status-pill {{ $child->status }}">{{ ucfirst($child->status) }}</span>
                                </div>

                                @if(count($child->displaySubjects()))
                                    <div>
                                        @foreach($child->displaySubjects() as $subject)
                                            <span class="child-subject-tag">{{ $subject }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="d-flex gap-2 mt-auto pt-2">
                                    @if($child->isApproved())
                                        <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1" disabled>View Profile</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" disabled>Education Dashboard</button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" disabled>Awaiting approval</button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger js-delete-child" data-id="{{ $child->id }}" data-name="{{ $child->full_name }}" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        <div class="child-add-card js-open-add-child" role="button" tabindex="0" aria-label="Add another child profile">
                            <i class="fa-solid fa-circle-plus fa-2x"></i>
                            <strong>Add Child</strong>
                            <span class="small">Create a child profile with email, phone & login</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Overview --}}
        <div class="col-xl-4">
            <div class="parent-dashboard-stack">
            <div class="parent-section-card">
                <div class="parent-section-head">
                    <h3>Quick Overview</h3>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="overview-tile blue">
                            <i class="fa-solid fa-book mb-2"></i>
                            <div class="fs-4 fw-bold">{{ $stats['materials_saved'] }}</div>
                            <div class="small">Study Materials</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="overview-tile green">
                            <i class="fa-solid fa-circle-question mb-2"></i>
                            <div class="fs-4 fw-bold">0</div>
                            <div class="small">Questions Asked</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="overview-tile purple">
                            <i class="fa-solid fa-graduation-cap mb-2"></i>
                            <div class="fs-4 fw-bold">0</div>
                            <div class="small">Courses Enrolled</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="overview-tile amber">
                            <i class="fa-solid fa-medal mb-2"></i>
                            <div class="fs-4 fw-bold">0</div>
                            <div class="small">Achievements</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="parent-section-card">
                <div class="parent-section-head">
                    <h3>Quick Actions</h3>
                </div>
                <div class="quick-action-item">
                    <span class="quick-action-icon bg-primary-subtle text-primary"><i class="fa-solid fa-chalkboard-user"></i></span>
                    <div>
                        <strong>Find Teachers / Tutors</strong>
                        <div class="small text-muted">Browse verified educators for your child</div>
                    </div>
                </div>
                <div class="quick-action-item">
                    <span class="quick-action-icon bg-success-subtle text-success"><i class="fa-solid fa-book-open"></i></span>
                    <div>
                        <strong>Explore Study Materials</strong>
                        <div class="small text-muted">Notes, papers, videos and more</div>
                    </div>
                </div>
                <div class="quick-action-item">
                    <span class="quick-action-icon bg-warning-subtle text-warning"><i class="fa-solid fa-calendar-days"></i></span>
                    <div>
                        <strong>Upcoming Events</strong>
                        <div class="small text-muted">Scholarships, workshops and deadlines</div>
                    </div>
                </div>
                <div class="quick-action-item">
                    <span class="quick-action-icon bg-info-subtle text-info"><i class="fa-solid fa-comments"></i></span>
                    <div>
                        <strong>Community Discussions</strong>
                        <div class="small text-muted">Ask questions and join conversations</div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- Bottom grid --}}
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="parent-section-card mb-4">
                <div class="parent-section-head">
                    <h3>Recommended for {{ $primaryChild?->full_name ?? 'your child' }}</h3>
                    <a href="{{ route('study-materials.library') }}" target="_blank" class="small">View All</a>
                </div>
                <div class="material-scroll">
                    <div class="material-card">
                        <div class="small text-muted mb-1"><span class="badge text-bg-light">Notes</span></div>
                        <div class="fw-semibold">Explore study library</div>
                        <div class="small text-secondary">Browse free materials by class & subject</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="parent-section-card parent-section-card--fill">
                        <div class="parent-section-head"><h3>My Enquiries</h3></div>
                        <div class="text-secondary small">No enquiries yet.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="parent-section-card parent-section-card--fill">
                        <div class="parent-section-head"><h3>Recent Questions</h3></div>
                        <div class="text-secondary small">No questions asked yet.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="parent-dashboard-stack">
                <div class="parent-section-card">
                    <div class="parent-section-head"><h3>Upcoming Deadlines</h3></div>
                    <div class="text-secondary small">No upcoming deadlines.</div>
                </div>

                <div class="parent-section-card">
                    <div class="parent-section-head"><h3>Recent Enquiries</h3></div>
                    @forelse($children->where('status', 'pending') as $pendingChild)
                        <div class="list-row">
                            <span class="quick-action-icon bg-warning-subtle text-warning"><i class="fa-solid fa-hourglass-half"></i></span>
                            <div class="flex-grow-1">
                                <strong>{{ $pendingChild->full_name }}</strong>
                                <div class="small text-muted">Child profile awaiting admin approval</div>
                            </div>
                            <span class="status-pill pending">Pending</span>
                        </div>
                    @empty
                        <div class="text-secondary small">No pending child requests.</div>
                    @endforelse
                </div>

                <div class="parent-section-card">
                    <div class="parent-section-head"><h3>Recent Activity</h3></div>
                    @forelse($children->take(3) as $activityChild)
                        <div class="list-row">
                            <span class="quick-action-icon bg-primary-subtle text-primary"><i class="fa-solid fa-user-graduate"></i></span>
                            <div>
                                <div><strong>{{ $activityChild->full_name }}</strong> profile {{ $activityChild->status }}</div>
                                <div class="small text-muted">{{ $activityChild->created_at?->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary small">Activity will appear here once you add children.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editParentProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Parent Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editParentProfileForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="parent_bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="parent_bio" name="bio" rows="4">{{ $parentProfile->bio }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="parent_location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="parent_location" name="location" value="{{ $parentProfile->location }}">
                    </div>
                    <div class="mb-3">
                        <label for="parent_languages" class="form-label">Languages</label>
                        <input type="text" class="form-control" id="parent_languages" name="languages" value="{{ implode(', ', $languages) }}" placeholder="English, Hindi">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="editParentProfileSubmitBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('backend.partials.add-child-modal')
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/parent-profile.js') }}?v={{ now()->timestamp }}"></script>
<script>
window.ParentProfileConfig = {
    storeChildUrl: @json(route('parent.children.store')),
    updateProfileUrl: @json(route('parent.profile.update')),
    deleteChildUrlBase: @json(url('/parent/children')),
    csrfToken: @json(csrf_token()),
};
</script>
@endpush
