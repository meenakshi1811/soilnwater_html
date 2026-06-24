@php
    $isProjectSubmission = old(
        'student_corner_content_type',
        data_get($post->meta, 'student_corner_content_type')
    ) === \App\Support\CommunityContentTaxonomy::studentCornerProjectContentType();
@endphp

<div id="studentCornerProjectSection" class="student-corner-project-section" style="{{ $isProjectSubmission ? '' : 'display:none;' }}">
    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Project section</h5>
                <p class="text-muted mb-0 small">For project submissions — share your project details below.</p>
            </div>
            <span class="badge bg-primary text-white">Project submission</span>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="studentCornerProjectTitle">Project title <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="student_corner_project_title"
                    id="studentCornerProjectTitle"
                    class="form-control student-corner-project-field student-corner-project-required"
                    maxlength="200"
                    value="{{ old('student_corner_project_title', data_get($post->meta, 'student_corner_project_title')) }}"
                    placeholder="Enter your project title"
                >
            </div>
            <div class="col-md-6">
                <label class="form-label" for="studentCornerProjectCategory">Project category <span class="text-danger">*</span></label>
                <select
                    name="student_corner_project_category"
                    id="studentCornerProjectCategory"
                    class="form-select student-corner-project-field student-corner-project-required"
                >
                    <option value="">Select project category</option>
                    @foreach(\App\Support\CommunityContentTaxonomy::studentCornerProjectCategories() as $projectCategory)
                        <option value="{{ $projectCategory }}" @selected(old('student_corner_project_category', data_get($post->meta, 'student_corner_project_category')) === $projectCategory)>{{ $projectCategory }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="studentCornerProjectDescription">Project description <span class="text-danger">*</span></label>
                <textarea
                    name="student_corner_project_description"
                    id="studentCornerProjectDescription"
                    class="form-control student-corner-project-field student-corner-project-required"
                    rows="4"
                    maxlength="5000"
                    placeholder="Describe your project, approach, and key work"
                >{{ old('student_corner_project_description', data_get($post->meta, 'student_corner_project_description')) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="studentCornerProjectOutcome">Project outcome</label>
                <textarea
                    name="student_corner_project_outcome"
                    id="studentCornerProjectOutcome"
                    class="form-control student-corner-project-field"
                    rows="3"
                    maxlength="3000"
                    placeholder="Results, findings, or impact of your project (optional)"
                >{{ old('student_corner_project_outcome', data_get($post->meta, 'student_corner_project_outcome')) }}</textarea>
            </div>
        </div>
    </div>

    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Document upload</h5>
                <p class="text-muted mb-0 small">Upload supporting files for assignments, research papers, notes, or presentations.</p>
            </div>
            <span class="badge bg-light text-dark border">Optional</span>
        </div>
        <label class="form-label" for="studentCornerDocuments">Upload documents</label>
        <input
            type="file"
            name="student_corner_documents[]"
            id="studentCornerDocuments"
            class="form-control student-corner-project-field"
            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip"
            multiple
        >
        <small class="text-muted d-block mt-2">
            Allowed: PDF, DOC, DOCX, PPT, PPTX, XLS, ZIP.
            Useful for assignments, research papers, notes, and presentations. Up to 6 files, max 20 MB each.
        </small>
        @if(!empty(data_get($post->meta, 'student_corner_documents')))
            <div class="mt-3 d-flex flex-column gap-2">
                @foreach(data_get($post->meta, 'student_corner_documents', []) as $document)
                    <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                        <input type="checkbox" name="removed_student_corner_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input student-corner-project-field">
                        <span class="form-check-label d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                            Remove {{ data_get($document, 'name', 'document') }}
                        </span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>
</div>
