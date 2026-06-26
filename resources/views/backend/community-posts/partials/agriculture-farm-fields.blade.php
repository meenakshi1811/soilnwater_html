<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Farm details</h5>
            <p class="text-muted mb-0 small">Optional context about your farm size and farming approach.</p>
        </div>
        <span class="badge bg-secondary text-white">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureFarmSize">Farm size</label>
            <select name="agriculture_farm_size" id="agricultureFarmSize" class="form-select agriculture-flow-field">
                <option value="">Select farm size</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureFarmSizes() as $farmSize)
                    <option value="{{ $farmSize }}" @selected(old('agriculture_farm_size', data_get($post->meta, 'agriculture_farm_size')) === $farmSize)>{{ $farmSize }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureFarmingType">Farming type</label>
            <select name="agriculture_farming_type" id="agricultureFarmingType" class="form-select agriculture-flow-field">
                <option value="">Select farming type</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureFarmingTypes() as $farmingType)
                    <option value="{{ $farmingType }}" @selected(old('agriculture_farming_type', data_get($post->meta, 'agriculture_farming_type')) === $farmingType)>{{ $farmingType }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
