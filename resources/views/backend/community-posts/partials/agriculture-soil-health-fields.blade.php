@php
    $soilTestConducted = old('agriculture_soil_test_conducted', data_get($post->meta, 'agriculture_soil_test_conducted'));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Soil health</h5>
            <p class="text-muted mb-0 small">Optional soil test details and recommendations for fellow farmers.</p>
        </div>
        <span class="badge bg-secondary text-white">Optional</span>
    </div>
    <label class="form-label d-block mb-2">Soil test conducted?</label>
    <div class="d-flex flex-wrap gap-3 mb-3">
        <label class="form-check">
            <input type="radio" name="agriculture_soil_test_conducted" value="yes" class="form-check-input agriculture-flow-field" @checked($soilTestConducted === 'yes')>
            <span class="form-check-label">Yes</span>
        </label>
        <label class="form-check">
            <input type="radio" name="agriculture_soil_test_conducted" value="no" class="form-check-input agriculture-flow-field" @checked($soilTestConducted === 'no')>
            <span class="form-check-label">No</span>
        </label>
    </div>
    <div id="agricultureSoilParametersSection" style="display:none;">
        <p class="small fw-semibold mb-2">Soil parameters</p>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="agricultureSoilPh">pH</label>
                <input type="text" name="agriculture_soil_ph" id="agricultureSoilPh" class="form-control agriculture-flow-field" maxlength="40" value="{{ old('agriculture_soil_ph', data_get($post->meta, 'agriculture_soil_ph')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="agricultureSoilOrganicCarbon">Organic carbon</label>
                <input type="text" name="agriculture_soil_organic_carbon" id="agricultureSoilOrganicCarbon" class="form-control agriculture-flow-field" maxlength="40" value="{{ old('agriculture_soil_organic_carbon', data_get($post->meta, 'agriculture_soil_organic_carbon')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="agricultureSoilNitrogen">Nitrogen</label>
                <input type="text" name="agriculture_soil_nitrogen" id="agricultureSoilNitrogen" class="form-control agriculture-flow-field" maxlength="40" value="{{ old('agriculture_soil_nitrogen', data_get($post->meta, 'agriculture_soil_nitrogen')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="agricultureSoilPhosphorus">Phosphorus</label>
                <input type="text" name="agriculture_soil_phosphorus" id="agricultureSoilPhosphorus" class="form-control agriculture-flow-field" maxlength="40" value="{{ old('agriculture_soil_phosphorus', data_get($post->meta, 'agriculture_soil_phosphorus')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="agricultureSoilPotassium">Potassium</label>
                <input type="text" name="agriculture_soil_potassium" id="agricultureSoilPotassium" class="form-control agriculture-flow-field" maxlength="40" value="{{ old('agriculture_soil_potassium', data_get($post->meta, 'agriculture_soil_potassium')) }}">
            </div>
        </div>
    </div>
    <div class="mt-3">
        <label class="form-label" for="agricultureSoilRecommendations">Soil health recommendations</label>
        <textarea name="agriculture_soil_recommendations" id="agricultureSoilRecommendations" class="form-control agriculture-flow-field" rows="3" maxlength="3000" placeholder="Share soil improvement advice or follow-up actions">{{ old('agriculture_soil_recommendations', data_get($post->meta, 'agriculture_soil_recommendations')) }}</textarea>
    </div>
</div>
