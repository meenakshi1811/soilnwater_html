@php
    $selectedStudentCornerCategory = old('student_corner_category', data_get($post->meta, 'student_corner_category', $post->category));
    $selectedStudentCornerAudiences = old('student_corner_target_audience', data_get($post->meta, 'student_corner_target_audience', []));
    $defaultStudentName = old(
        'student_corner_profile_name',
        data_get($post->meta, 'student_corner_profile_name', auth()->user()?->name ?: auth()->user()?->full_name)
    );
    $flowPlacement = $placement ?? 'all';
    $showStudentCornerSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showStudentCornerRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showStudentCornerSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Main category</h5>
            <p class="text-muted mb-0 small">Choose the primary topic for this Student Corner post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="studentCornerCategory">Main category <span class="text-danger">*</span></label>
    <select name="student_corner_category" id="studentCornerCategory" class="form-select student-corner-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedStudentCornerCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">
        Examples: Education, Career Guidance, Competitive Exams, Scholarships, Projects, Science &amp; Technology, Student Experiences, Study Tips, Internships, Higher Education, Skill Development, Innovation, Research, Entrepreneurship, Campus Life
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Content type</h5>
            <p class="text-muted mb-0 small">How this content should be classified for readers.</p>
        </div>
        <span class="badge bg-danger text-white">Very important</span>
    </div>
    <label class="form-label" for="studentCornerContentType">Content type <span class="text-danger">*</span></label>
    <select name="student_corner_content_type" id="studentCornerContentType" class="form-select student-corner-required" required>
        <option value="">Select content type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerContentTypes() as $contentType)
            <option value="{{ $contentType }}" @selected(old('student_corner_content_type', data_get($post->meta, 'student_corner_content_type')) === $contentType)>{{ $contentType }}</option>
        @endforeach
    </select>
</div>
@endif

@if($showStudentCornerRest)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Student profile</h5>
            <p class="text-muted mb-0 small">Optional context about the student author.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="studentCornerProfileName">Name</label>
            <input
                type="text"
                name="student_corner_profile_name"
                id="studentCornerProfileName"
                class="form-control"
                maxlength="120"
                value="{{ $defaultStudentName }}"
                placeholder="Auto-filled from your profile or enter a name"
            >
            <small class="text-muted d-block mt-1">Auto-filled from your account or optional.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="studentCornerClassCourse">Class / course</label>
            <select name="student_corner_class_course" id="studentCornerClassCourse" class="form-select">
                <option value="">Select class / course (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::studentCornerClassCourses() as $classCourse)
                    <option value="{{ $classCourse }}" @selected(old('student_corner_class_course', data_get($post->meta, 'student_corner_class_course')) === $classCourse)>{{ $classCourse }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="studentCornerStream">Stream</label>
            <select name="student_corner_stream" id="studentCornerStream" class="form-select">
                <option value="">Select stream (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::studentCornerStreams() as $stream)
                    <option value="{{ $stream }}" @selected(old('student_corner_stream', data_get($post->meta, 'student_corner_stream')) === $stream)>{{ $stream }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="studentCornerInstitutionName">Institution name</label>
            <input
                type="text"
                name="student_corner_institution_name"
                id="studentCornerInstitutionName"
                class="form-control"
                maxlength="200"
                value="{{ old('student_corner_institution_name', data_get($post->meta, 'student_corner_institution_name')) }}"
                placeholder="School, college, or institute (optional)"
            >
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--audience border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerTargetAudiences() as $audience)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="student_corner_target_audience[]"
                        value="{{ $audience }}"
                        class="form-check-input"
                        @checked(in_array($audience, (array) $selectedStudentCornerAudiences, true))
                    >
                    <span class="form-check-label">{{ $audience }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

@include('backend.community-posts.partials.student-corner-project-fields', ['post' => $post])

@include('backend.community-posts.partials.student-corner-media-fields', ['post' => $post])
@include('backend.community-posts.partials.student-corner-topic-fields', ['post' => $post])
@include('backend.community-posts.partials.student-corner-engagement-fields', ['post' => $post])
@include('backend.community-posts.partials.student-corner-achievements-fields', ['post' => $post])
@include('backend.community-posts.partials.student-corner-privacy-fields', ['post' => $post])
@endif
