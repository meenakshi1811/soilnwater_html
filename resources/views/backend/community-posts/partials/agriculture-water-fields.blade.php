<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-info-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Water management</h5>
            <p class="text-muted mb-0 small">A flagship SoilnWater feature — share irrigation methods, water sources, and conservation practices.</p>
        </div>
        <span class="badge bg-info text-dark">SoilnWater flagship</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureIrrigationMethod">Irrigation method</label>
            <select name="agriculture_irrigation_method" id="agricultureIrrigationMethod" class="form-select agriculture-flow-field">
                <option value="">Select irrigation method</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureIrrigationMethods() as $method)
                    <option value="{{ $method }}" @selected(old('agriculture_irrigation_method', data_get($post->meta, 'agriculture_irrigation_method')) === $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureWaterSource">Water source</label>
            <select name="agriculture_water_source" id="agricultureWaterSource" class="form-select agriculture-flow-field">
                <option value="">Select water source</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureWaterSources() as $source)
                    <option value="{{ $source }}" @selected(old('agriculture_water_source', data_get($post->meta, 'agriculture_water_source')) === $source)>{{ $source }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label d-block">Water conservation practice</label>
            <div class="d-flex flex-wrap gap-2">
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureWaterConservationPractices() as $practice)
                    <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                        <input
                            type="checkbox"
                            name="agriculture_water_conservation_practices[]"
                            value="{{ $practice }}"
                            class="form-check-input agriculture-flow-field"
                            @checked(in_array($practice, (array) old('agriculture_water_conservation_practices', data_get($post->meta, 'agriculture_water_conservation_practices', [])), true))
                        >
                        <span class="form-check-label">{{ $practice }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>
