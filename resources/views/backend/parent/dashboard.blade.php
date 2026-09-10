@extends('backend.layouts.app')

@section('title', 'Parent Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/parent-profile.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
    $displayName = $user->full_name ?: $user->name;
    $location = $parentProfile->location ?: trim(collect([$user->city, $user->pincode])->filter()->implode(', '));
    $languages = is_array($parentProfile->languages) ? $parentProfile->languages : [];
    $memberSince = $user->created_at?->format('M Y');
    $avatarUrl = filled($user->profile_image) ? asset($user->profile_image) : null;

    $quickActions = [
        ['icon' => 'fa-chalkboard-user', 'tone' => 'orange', 'title' => 'Find Teachers / Tutors', 'subtitle' => 'Find the best teachers for your child', 'url' => route('educator.index')],
        ['icon' => 'fa-school', 'tone' => 'purple', 'title' => 'Find Schools & Institutes', 'subtitle' => 'Discover schools and learning centres', 'url' => route('frontend.index')],
        ['icon' => 'fa-book-open', 'tone' => 'blue', 'title' => 'Explore Study Materials', 'subtitle' => 'Notes, papers, videos and more', 'url' => route('study-materials.library')],
        ['icon' => 'fa-circle-question', 'tone' => 'green', 'title' => 'Ask a Question', 'subtitle' => 'Get answers from educators & community', 'url' => route('community.posts.create')],
        ['icon' => 'fa-graduation-cap', 'tone' => 'violet', 'title' => 'Find Courses', 'subtitle' => 'Browse courses for your child', 'url' => route('frontend.index')],
        ['icon' => 'fa-compass', 'tone' => 'teal', 'title' => 'Career Guidance', 'subtitle' => 'Plan your child\'s future path', 'url' => route('frontend.index')],
    ];
@endphp

<div class="parent-dashboard">
    {{-- Profile header --}}
    <section class="parent-hero">
        <div class="parent-hero__content">
            <div class="parent-hero__profile">
                <div class="parent-avatar-wrap">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="parent-avatar">
                    @else
                        <div class="parent-avatar parent-avatar--placeholder">{{ $user->authorInitials() }}</div>
                    @endif
                    <button type="button" class="parent-avatar-camera" data-bs-toggle="modal" data-bs-target="#editParentProfileModal" aria-label="Edit profile photo">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>

                <div class="parent-hero__info">
                    <div class="parent-hero__name-row">
                        <h1 class="parent-hero__name">{{ $displayName }}</h1>
                        <span class="parent-verified-badge"><i class="fa-solid fa-circle-check"></i> Verified Parent</span>
                    </div>

                    <p class="parent-hero__meta">
                        @if($location){{ $location }}@else Location not set @endif
                        @if($memberSince)<span class="parent-hero__dot">&bull;</span> Member since {{ $memberSince }}@endif
                    </p>

                    @if(count($languages))
                        <p class="parent-hero__languages"><i class="fa-solid fa-globe"></i> {{ implode(', ', $languages) }}</p>
                    @endif

                    <div class="parent-stats-row">
                        <div class="parent-stat"><strong>{{ $stats['children'] }}</strong> Children</div>
                        <div class="parent-stat"><strong>{{ $stats['materials_saved'] }}</strong> Materials Saved</div>
                        <div class="parent-stat"><strong>{{ $stats['enquiries'] }}</strong> Enquiries</div>
                        <div class="parent-stat"><strong>{{ $stats['discussions'] }}</strong> Discussions</div>
                        <div class="parent-stat"><strong>{{ $stats['following'] }}</strong> Following</div>
                    </div>
                </div>
            </div>

            <div class="parent-hero__actions">
                <button type="button" class="parent-btn parent-btn--outline" data-bs-toggle="modal" data-bs-target="#editParentProfileModal">
                    <i class="fa-solid fa-pen"></i> Edit Profile
                </button>
            </div>
        </div>

        <div class="parent-hero__illustration" aria-hidden="true">
            <svg viewBox="0 0 280 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="140" cy="165" rx="90" ry="12" fill="#BFDBFE" opacity="0.45"/>
                <rect x="95" y="95" width="90" height="55" rx="8" fill="#93C5FD"/>
                <rect x="100" y="100" width="80" height="40" rx="4" fill="#EFF6FF"/>
                <circle cx="70" cy="75" r="22" fill="#FDE68A"/>
                <circle cx="210" cy="80" r="18" fill="#FCA5A5"/>
                <path d="M55 115 Q70 90 85 115" stroke="#64748B" stroke-width="3" fill="none"/>
                <path d="M195 112 Q210 88 225 112" stroke="#64748B" stroke-width="3" fill="none"/>
                <rect x="118" y="128" width="44" height="6" rx="3" fill="#2563EB" opacity="0.35"/>
            </svg>
        </div>
    </section>

    {{-- Lower section: children + quick actions --}}
    <div class="parent-lower">
        <section class="parent-children-panel">
            <div class="parent-panel-head">
                <h2>My Children</h2>
                <button type="button" class="parent-link-btn js-open-add-child">Manage Children</button>
            </div>

            @if($children->isEmpty())
                <div class="parent-child-grid parent-child-grid--single">
                    <div class="parent-add-child-card js-open-add-child" role="button" tabindex="0">
                        <span class="parent-add-child-card__icon"><i class="fa-solid fa-user-plus"></i></span>
                        <h3>Add Another Child</h3>
                        <p>Add your child to get personalized recommendations and tracking.</p>
                        <span class="parent-btn parent-btn--outline parent-btn--sm"><i class="fa-solid fa-plus"></i> Add Child</span>
                    </div>
                </div>
            @else
                <div class="parent-child-grid">
                    @foreach($children as $child)
                        @php
                            $childAvatar = filled($child->profile_image) ? asset($child->profile_image) : null;
                            $childInitials = collect(preg_split('/\s+/', trim($child->full_name)) ?: [])->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'CH';
                            $genderClass = match ($child->gender) {
                                'female' => 'parent-child-card--female',
                                'male' => 'parent-child-card--male',
                                default => 'parent-child-card--male',
                            };
                            $subjects = $child->displaySubjects();
                            $visibleSubjects = array_slice($subjects, 0, 3);
                            $extraSubjects = max(count($subjects) - 3, 0);
                            $classBoard = collect([$child->class_grade, $child->board])->filter()->implode(' • ');
                        @endphp
                        <article class="parent-child-card {{ $genderClass }}" data-child-id="{{ $child->id }}">
                            <div class="parent-child-card__top">
                                @if($childAvatar)
                                    <img src="{{ $childAvatar }}" alt="{{ $child->full_name }}" class="parent-child-card__avatar">
                                @else
                                    <div class="parent-child-card__avatar parent-child-card__avatar--placeholder">{{ $childInitials }}</div>
                                @endif
                                <div class="parent-child-card__info">
                                    <div class="parent-child-card__name-row">
                                        <div class="parent-child-card__name">
                                            {{ $child->full_name }}
                                            <i class="fa-solid {{ $child->gender === 'female' ? 'fa-venus' : ($child->gender === 'male' ? 'fa-mars' : 'fa-user') }}"></i>
                                        </div>
                                        @if($child->isPending())
                                            <span class="parent-status-pill parent-status-pill--pending">Pending</span>
                                        @elseif($child->isRejected())
                                            <span class="parent-status-pill parent-status-pill--rejected">Declined</span>
                                        @endif
                                    </div>
                                    <div class="parent-child-card__class">{{ $classBoard ?: 'Class details pending' }}</div>
                                    @if($child->school_name)
                                        <div class="parent-child-card__school">{{ $child->school_name }}</div>
                                    @endif
                                </div>
                            </div>

                            @if(count($visibleSubjects))
                                <div class="parent-child-card__tags">
                                    @foreach($visibleSubjects as $subject)
                                        <span class="parent-tag">{{ $subject }}</span>
                                    @endforeach
                                    @if($extraSubjects > 0)
                                        <span class="parent-tag parent-tag--more">+{{ $extraSubjects }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="parent-child-card__footer">
                                @if($child->isApproved())
                                    <a href="{{ route('parent.children.dashboard', $child) }}" class="parent-child-btn">
                                        <i class="fa-solid fa-chart-simple"></i> View Dashboard
                                    </a>
                                @else
                                    <button type="button" class="parent-child-btn" disabled>Awaiting approval</button>
                                @endif
                                <button type="button" class="parent-child-delete js-delete-child" data-id="{{ $child->id }}" data-name="{{ $child->full_name }}" title="Delete child profile">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </article>
                    @endforeach

                    <div class="parent-add-child-card js-open-add-child" role="button" tabindex="0">
                        <span class="parent-add-child-card__icon"><i class="fa-solid fa-user-plus"></i></span>
                        <h3>Add Another Child</h3>
                        <p>Add your child to get personalized recommendations and tracking.</p>
                        <span class="parent-btn parent-btn--outline parent-btn--sm"><i class="fa-solid fa-plus"></i> Add Child</span>
                    </div>
                </div>
            @endif
        </section>

        <aside class="parent-quick-actions">
            <h2>Quick Actions</h2>
            <ul class="parent-quick-actions__list">
                @foreach($quickActions as $action)
                    <li>
                        <a href="{{ $action['url'] }}" class="parent-quick-action parent-quick-action--{{ $action['tone'] }}">
                            <span class="parent-quick-action__icon"><i class="fa-solid {{ $action['icon'] }}"></i></span>
                            <span class="parent-quick-action__copy">
                                <strong>{{ $action['title'] }}</strong>
                                <small>{{ $action['subtitle'] }}</small>
                            </span>
                            <i class="fa-solid fa-chevron-right parent-quick-action__arrow"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</div>

<div class="modal fade" id="editParentProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
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
