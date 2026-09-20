<div class="modal fade" id="diaryHolidayEditModal" tabindex="-1" aria-labelledby="diaryHolidayEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="diaryHolidayEditForm" novalidate>
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="diaryHolidayEditModalLabel">Edit holiday</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="diaryHolidayEditName">Holiday name</label>
              <input type="text" id="diaryHolidayEditName" name="name" class="form-control" maxlength="160" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="diaryHolidayEditType">Type</label>
              <select id="diaryHolidayEditType" name="holiday_type" class="form-select" required>
                @foreach($config['holidayTypes'] as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="diaryHolidayEditStart">Start date</label>
              <input type="date" id="diaryHolidayEditStart" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="diaryHolidayEditEnd">End date</label>
              <input type="date" id="diaryHolidayEditEnd" name="end_date" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="diaryHolidayEditYear">Academic year</label>
              <input type="text" id="diaryHolidayEditYear" name="academic_year" class="form-control" maxlength="20" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="diaryHolidayEditDesc">Description</label>
              <textarea id="diaryHolidayEditDesc" name="description" class="form-control" rows="2" maxlength="5000"></textarea>
            </div>
            <div class="col-md-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="diaryHolidayEditRecurring" name="is_recurring" value="1">
                <label class="form-check-label" for="diaryHolidayEditRecurring">Recurring</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="diaryHolidayEditActive" name="is_active" value="1">
                <label class="form-check-label" for="diaryHolidayEditActive">Active</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary js-diary-submit-btn">
            <span class="btn-text">Save changes</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="diaryLeaveEditModal" tabindex="-1" aria-labelledby="diaryLeaveEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="diaryLeaveEditForm" novalidate>
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="diaryLeaveEditModalLabel">Edit leave rule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="diaryLeaveEditType">Leave type</label>
              <select id="diaryLeaveEditType" name="leave_type" class="form-select" required>
                @foreach($config['leaveTypes'] as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="diaryLeaveEditDays">Allowed days</label>
              <input type="number" id="diaryLeaveEditDays" name="allowed_days" class="form-control" min="0" max="366" required>
            </div>
            <div class="col-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="diaryLeaveEditPaid" name="is_paid" value="1">
                <label class="form-check-label" for="diaryLeaveEditPaid">Paid leave</label>
              </div>
            </div>
            <div class="col-12">
              <span class="form-label d-block">Applicable to</span>
              <div class="d-flex flex-wrap gap-3" id="diaryLeaveEditAudiences">
                @foreach($config['audiences'] as $key => $label)
                  <div class="form-check">
                    <input class="form-check-input js-diary-leave-edit-audience" type="checkbox" name="applicable_to[]" value="{{ $key }}" id="diaryLeaveEditAudience{{ $key }}">
                    <label class="form-check-label" for="diaryLeaveEditAudience{{ $key }}">{{ $label }}</label>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="col-12">
              <label class="form-label" for="diaryLeaveEditDesc">Policy notes</label>
              <textarea id="diaryLeaveEditDesc" name="description" class="form-control" rows="2" maxlength="5000"></textarea>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="diaryLeaveEditActive" name="is_active" value="1">
                <label class="form-check-label" for="diaryLeaveEditActive">Active</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary js-diary-submit-btn">
            <span class="btn-text">Save changes</span>
            <span class="btn-loader d-none" aria-hidden="true"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
