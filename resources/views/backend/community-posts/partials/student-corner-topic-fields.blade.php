@php
    $selectedStudyMaterialTypes = old('student_corner_study_material_types', data_get($post->meta, 'student_corner_study_material_types', []));
    $selectedCareerGuidanceTopics = old('student_corner_career_guidance_topics', data_get($post->meta, 'student_corner_career_guidance_topics', []));
    $selectedSkills = old('student_corner_skills', data_get($post->meta, 'student_corner_skills', []));
    $selectedSocialImpactCategories = old('student_corner_social_impact_categories', data_get($post->meta, 'student_corner_social_impact_categories', []));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Study material type</h5>
            <p class="text-muted mb-0 small">Tag the kinds of study resources shared in this post.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerStudyMaterialTypes() as $materialType)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="student_corner_study_material_types[]"
                        value="{{ $materialType }}"
                        class="form-check-input student-corner-flow-field"
                        @checked(in_array($materialType, (array) $selectedStudyMaterialTypes, true))
                    >
                    <span class="form-check-label">{{ $materialType }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Career guidance topics</h5>
            <p class="text-muted mb-0 small">Select topics covered in career guidance content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerCareerGuidanceTopics() as $topic)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="student_corner_career_guidance_topics[]"
                        value="{{ $topic }}"
                        class="form-check-input student-corner-flow-field"
                        @checked(in_array($topic, (array) $selectedCareerGuidanceTopics, true))
                    >
                    <span class="form-check-label">{{ $topic }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Scholarship details</h5>
            <p class="text-muted mb-0 small">Optional scholarship information for readers.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="studentCornerScholarshipName">Scholarship name</label>
            <input
                type="text"
                name="student_corner_scholarship_name"
                id="studentCornerScholarshipName"
                class="form-control student-corner-flow-field"
                maxlength="200"
                value="{{ old('student_corner_scholarship_name', data_get($post->meta, 'student_corner_scholarship_name')) }}"
                placeholder="e.g. National Merit Scholarship"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="studentCornerApplicationDeadline">Application deadline</label>
            <input
                type="text"
                name="student_corner_application_deadline"
                id="studentCornerApplicationDeadline"
                class="form-control student-corner-flow-field"
                maxlength="120"
                value="{{ old('student_corner_application_deadline', data_get($post->meta, 'student_corner_application_deadline')) }}"
                placeholder="e.g. 30 June 2026"
            >
        </div>
        <div class="col-12">
            <label class="form-label" for="studentCornerEligibility">Eligibility</label>
            <textarea
                name="student_corner_eligibility"
                id="studentCornerEligibility"
                class="form-control student-corner-flow-field"
                rows="3"
                maxlength="2000"
                placeholder="Who can apply and key eligibility criteria"
            >{{ old('student_corner_eligibility', data_get($post->meta, 'student_corner_eligibility')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="studentCornerOfficialWebsite">Official website</label>
            <input
                type="url"
                name="student_corner_official_website"
                id="studentCornerOfficialWebsite"
                class="form-control student-corner-flow-field"
                maxlength="255"
                value="{{ old('student_corner_official_website', data_get($post->meta, 'student_corner_official_website')) }}"
                placeholder="https://example.gov.in/scholarship"
            >
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Competitive exam section</h5>
            <p class="text-muted mb-0 small">Share exam preparation experience, strategy, and outcomes.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="studentCornerExamName">Exam name</label>
            <input
                type="text"
                name="student_corner_exam_name"
                id="studentCornerExamName"
                class="form-control student-corner-flow-field"
                maxlength="160"
                value="{{ old('student_corner_exam_name', data_get($post->meta, 'student_corner_exam_name')) }}"
                placeholder="e.g. JEE Main, NEET, UPSC"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="studentCornerMarksRank">Marks / rank</label>
            <input
                type="text"
                name="student_corner_marks_rank"
                id="studentCornerMarksRank"
                class="form-control student-corner-flow-field"
                maxlength="120"
                value="{{ old('student_corner_marks_rank', data_get($post->meta, 'student_corner_marks_rank')) }}"
                placeholder="e.g. AIR 450, 95 percentile"
            >
        </div>
        <div class="col-12">
            <label class="form-label" for="studentCornerPreparationStrategy">Preparation strategy</label>
            <textarea
                name="student_corner_preparation_strategy"
                id="studentCornerPreparationStrategy"
                class="form-control student-corner-flow-field"
                rows="3"
                maxlength="3000"
                placeholder="How you prepared — schedule, focus areas, mock tests"
            >{{ old('student_corner_preparation_strategy', data_get($post->meta, 'student_corner_preparation_strategy')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="studentCornerResourcesUsed">Resources used</label>
            <textarea
                name="student_corner_resources_used"
                id="studentCornerResourcesUsed"
                class="form-control student-corner-flow-field"
                rows="3"
                maxlength="3000"
                placeholder="Books, coaching, online platforms, notes"
            >{{ old('student_corner_resources_used', data_get($post->meta, 'student_corner_resources_used')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="studentCornerLessonsLearned">Lessons learned</label>
            <textarea
                name="student_corner_lessons_learned"
                id="studentCornerLessonsLearned"
                class="form-control student-corner-flow-field"
                rows="3"
                maxlength="3000"
                placeholder="Key takeaways for fellow aspirants"
            >{{ old('student_corner_lessons_learned', data_get($post->meta, 'student_corner_lessons_learned')) }}</textarea>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Skills</h5>
            <p class="text-muted mb-0 small">Select skills demonstrated or discussed in this post.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerSkills() as $skill)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="student_corner_skills[]"
                        value="{{ $skill }}"
                        class="form-check-input student-corner-flow-field"
                        @checked(in_array($skill, (array) $selectedSkills, true))
                    >
                    <span class="form-check-label">{{ $skill }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Social impact category</h5>
            <p class="text-muted mb-0 small">Tag social impact themes if your post relates to community change.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::studentCornerSocialImpactCategories() as $category)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="student_corner_social_impact_categories[]"
                        value="{{ $category }}"
                        class="form-check-input student-corner-flow-field"
                        @checked(in_array($category, (array) $selectedSocialImpactCategories, true))
                    >
                    <span class="form-check-label">{{ $category }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
