@php
    $initialAchievements = old('youth_corner_achievements', data_get($post->meta, 'youth_corner_achievements', []));
    if (! is_array($initialAchievements)) {
        $initialAchievements = [];
    }
    $communityYouthCornerAchievementsForJs = collect($initialAchievements)->map(function ($entry) {
        return [
            'achievement_title' => (string) data_get($entry, 'achievement_title', ''),
            'year' => (string) data_get($entry, 'year', ''),
            'existing_certificate_path' => (string) data_get($entry, 'certificate.path', data_get($entry, 'existing_certificate_path', '')),
            'existing_certificate_name' => (string) data_get($entry, 'certificate.name', ''),
            'existing_certificate_url' => (string) data_get($entry, 'certificate.url', ''),
        ];
    })->values()->all();
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Achievements</h5>
            <p class="text-muted mb-0 small">Optional awards, certificates, and recognitions.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div id="youthCornerAchievementEntries" class="d-flex flex-column gap-3"></div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-3 youth-corner-flow-field" id="addYouthCornerAchievementBtn">
        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add achievement
    </button>
    <template id="youthCornerAchievementTemplate">
        <div class="youth-corner-achievement-entry border rounded-3 p-3 bg-light" data-achievement-index="">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h6 class="mb-0 youth-corner-achievement-entry__title">Achievement</h6>
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-youth-corner-achievement-entry">Remove</button>
            </div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Achievement title</label>
                    <input type="text" class="form-control js-yc-achievement-title" maxlength="160" placeholder="Hackathon Winner" data-name="youth_corner_achievements[__INDEX__][achievement_title]">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <input type="text" class="form-control js-yc-achievement-year" maxlength="10" placeholder="2025" data-name="youth_corner_achievements[__INDEX__][year]">
                </div>
                <div class="col-12">
                    <label class="form-label">Certificate upload</label>
                    <input type="file" class="form-control js-yc-achievement-certificate" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" data-name="youth_corner_achievements[__INDEX__][certificate]">
                    <input type="hidden" class="js-yc-achievement-existing-certificate-path" data-name="youth_corner_achievements[__INDEX__][existing_certificate_path]">
                    <div class="js-yc-achievement-certificate-preview mt-2 small text-muted" style="display:none;"></div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    window.communityYouthCornerAchievements = @json($communityYouthCornerAchievementsForJs);
</script>
