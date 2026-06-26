<div id="agricultureCropSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Crop details</h5>
            <p class="text-muted mb-0 small">Shown when your category or share type is crop-related.</p>
        </div>
        <span class="badge bg-secondary text-white">When relevant</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureCropName">Crop name</label>
            <input
                type="text"
                name="agriculture_crop_name"
                id="agricultureCropName"
                class="form-control agriculture-flow-field"
                maxlength="120"
                value="{{ old('agriculture_crop_name', data_get($post->meta, 'agriculture_crop_name')) }}"
                placeholder="e.g. Wheat, Tomato, Mango"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureCropVariety">Variety</label>
            <input
                type="text"
                name="agriculture_crop_variety"
                id="agricultureCropVariety"
                class="form-control agriculture-flow-field"
                maxlength="120"
                value="{{ old('agriculture_crop_variety', data_get($post->meta, 'agriculture_crop_variety')) }}"
                placeholder="e.g. HD-2967, Pusa Ruby"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureSowingDate">Sowing date</label>
            <input
                type="date"
                name="agriculture_sowing_date"
                id="agricultureSowingDate"
                class="form-control agriculture-flow-field"
                value="{{ old('agriculture_sowing_date', data_get($post->meta, 'agriculture_sowing_date')) }}"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureHarvestDate">Harvest date</label>
            <input
                type="date"
                name="agriculture_harvest_date"
                id="agricultureHarvestDate"
                class="form-control agriculture-flow-field"
                value="{{ old('agriculture_harvest_date', data_get($post->meta, 'agriculture_harvest_date')) }}"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureGrowingSeason">Growing season</label>
            <select name="agriculture_growing_season" id="agricultureGrowingSeason" class="form-select agriculture-flow-field">
                <option value="">Select growing season</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureGrowingSeasons() as $season)
                    <option value="{{ $season }}" @selected(old('agriculture_growing_season', data_get($post->meta, 'agriculture_growing_season')) === $season)>{{ $season }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
