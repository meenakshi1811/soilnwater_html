@php
    $initialAchievements = old('senior_citizens_forum_achievements', data_get($post->meta, 'senior_citizens_forum_achievements', []));
    if (! is_array($initialAchievements)) {
        $initialAchievements = [];
    }
    $communitySeniorCitizensForumAchievementsForJs = collect($initialAchievements)->map(function ($entry) {
        return [
            'award_name' => (string) data_get($entry, 'award_name', ''),
            'year' => (string) data_get($entry, 'year', ''),
            'description' => (string) data_get($entry, 'description', ''),
            'existing_photo_path' => (string) data_get($entry, 'photo.path', ''),
            'existing_photo_url' => (string) data_get($entry, 'photo.url', ''),
            'existing_certificate_path' => (string) data_get($entry, 'certificate.path', ''),
            'existing_certificate_name' => (string) data_get($entry, 'certificate.name', ''),
            'existing_certificate_url' => (string) data_get($entry, 'certificate.url', ''),
        ];
    })->values()->all();
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Achievements</h5>
            <p class="text-muted mb-0 small">Optional awards and recognitions from your life journey.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div id="seniorCitizensForumAchievementEntries" class="d-flex flex-column gap-3"></div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="addSeniorCitizensForumAchievementBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add achievement
    </button>
    <template id="seniorCitizensForumAchievementTemplate">
        <div class="autobiography-achievement-entry border rounded-3 p-3 bg-light" data-achievement-index="">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h6 class="mb-0 senior-citizens-forum-achievement-entry__title">Achievement</h6>
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-senior-citizens-forum-achievement-entry">Remove</button>
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Award name</label>
                    <input type="text" class="form-control js-scf-achievement-award-name" maxlength="160" placeholder="Community Service Award" data-name="senior_citizens_forum_achievements[__INDEX__][award_name]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <input type="text" class="form-control js-scf-achievement-year" maxlength="10" placeholder="2018" data-name="senior_citizens_forum_achievements[__INDEX__][year]">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control js-scf-achievement-description" rows="2" maxlength="1000" placeholder="Briefly describe this achievement." data-name="senior_citizens_forum_achievements[__INDEX__][description]"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Photo</label>
                    <input type="file" class="form-control js-scf-achievement-photo" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif" data-name="senior_citizens_forum_achievements[__INDEX__][photo]">
                    <input type="hidden" class="js-scf-achievement-existing-photo-path" data-name="senior_citizens_forum_achievements[__INDEX__][existing_photo_path]">
                    <div class="js-scf-achievement-photo-preview mt-2" style="display:none;">
                        <img src="" alt="Achievement photo preview" class="rounded border" style="max-width:120px;max-height:120px;object-fit:cover;">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Certificate upload</label>
                    <input type="file" class="form-control js-scf-achievement-certificate" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" data-name="senior_citizens_forum_achievements[__INDEX__][certificate]">
                    <input type="hidden" class="js-scf-achievement-existing-certificate-path" data-name="senior_citizens_forum_achievements[__INDEX__][existing_certificate_path]">
                    <div class="js-scf-achievement-certificate-preview mt-2 small text-muted" style="display:none;"></div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    window.communitySeniorCitizensForumAchievements = @json($communitySeniorCitizensForumAchievementsForJs);
</script>
