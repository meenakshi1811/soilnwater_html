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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="child_full_name" class="form-label">Full name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="child_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="child_email" class="form-label">Email (login) <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="child_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="child_phone_number" class="form-label">Phone number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="child_phone_number" name="phone_number" required maxlength="15">
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
                        <div class="col-md-6">
                            <label for="child_password" class="form-label">Login password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="child_password" name="password" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label for="child_password_confirmation" class="form-label">Confirm password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="child_password_confirmation" name="password_confirmation" required minlength="8">
                        </div>
                        <div class="col-md-4">
                            <label for="child_class_grade" class="form-label">Class / Grade</label>
                            <input type="text" class="form-control" id="child_class_grade" name="class_grade" placeholder="Class 10">
                        </div>
                        <div class="col-md-4">
                            <label for="child_board" class="form-label">Board</label>
                            <input type="text" class="form-control" id="child_board" name="board" placeholder="CBSE">
                        </div>
                        <div class="col-md-4">
                            <label for="child_school_name" class="form-label">School</label>
                            <input type="text" class="form-control" id="child_school_name" name="school_name">
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
