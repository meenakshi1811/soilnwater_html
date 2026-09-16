<div class="modal fade parent-child-modal" id="addChildModal" tabindex="-1" aria-labelledby="addChildModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addChildModalLabel">Add Child Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addChildForm" class="parent-child-modal__form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body parent-child-modal__body">
                    <div id="addChildAlert" class="alert d-none" role="alert"></div>
                    <p class="text-muted small mb-3">Children access their dashboard through your parent profile after admin approval. No separate login is required.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="child_full_name" class="form-label">Full name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="child_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="child_phone_number" class="form-label">Phone number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="child_phone_number" name="phone_number" required maxlength="15">
                        </div>
                        <div class="col-md-6">
                            <label for="child_age" class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control js-child-age" id="child_age" name="age" min="1" max="25" required>
                        </div>
                        <div class="col-md-6">
                            @include('backend.partials.date-of-birth-dropdown', ['prefix' => 'child'])
                        </div>
                        <div class="col-md-6">
                            <label for="child_gender" class="form-label">Gender</label>
                            <select class="form-select" id="child_gender" name="gender">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="child_class_grade" class="form-label">Class / Grade</label>
                            <input type="text" class="form-control" id="child_class_grade" name="class_grade" placeholder="Class 10">
                        </div>
                        <div class="col-md-4">
                            <label for="child_school_name" class="form-label">School</label>
                            <input type="text" class="form-control js-school-institute-search" id="child_school_name" name="school_name" autocomplete="off" placeholder="Search school name">
                            <small class="text-muted">Start typing to search schools via Google.</small>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch parent-board-toggle">
                                <input class="form-check-input js-child-has-board" type="checkbox" role="switch" value="1" id="child_has_board" name="has_board">
                                <label class="form-check-label" for="child_has_board">Follows a board</label>
                            </div>
                        </div>
                        <div class="col-md-4 js-child-board-wrap d-none">
                            <label for="child_board" class="form-label">Board <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="child_board" name="board" placeholder="CBSE, ICSE, State Board">
                        </div>
                        <div class="col-12">
                            <label for="child_subjects" class="form-label">Subjects</label>
                            <input type="text" class="form-control" id="child_subjects" name="subjects" placeholder="Maths, Science, English">
                            <small class="text-muted">Separate subjects with commas.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="child_profile_image" class="form-label">Profile photo</label>
                            <input type="file" class="form-control" id="child_profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="child_is_primary" name="is_primary">
                                <label class="form-check-label" for="child_is_primary">Set as primary child</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addChildSubmitBtn">Submit for approval</button>
                </div>
            </form>
        </div>
    </div>
</div>
