<div id="environmentIssueSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-danger-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Environmental issue</h5>
            <p class="text-muted mb-0 small">Classify the environmental problem you are reporting.</p>
        </div>
        <span class="badge bg-danger text-white">Issue reporting</span>
    </div>
    <label class="form-label" for="environmentIssueType">Issue type</label>
    <select name="environment_issue_type" id="environmentIssueType" class="form-select environment-flow-field">
        <option value="">Select issue type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::environmentIssueTypes() as $issueType)
            <option value="{{ $issueType }}" @selected(old('environment_issue_type', data_get($post->meta, 'environment_issue_type')) === $issueType)>{{ $issueType }}</option>
        @endforeach
    </select>
</div>

<div id="environmentInitiativeSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community initiative</h5>
            <p class="text-muted mb-0 small">Describe the type of community environmental action.</p>
        </div>
        <span class="badge bg-success text-white">Initiative</span>
    </div>
    <label class="form-label" for="environmentInitiativeType">Initiative type</label>
    <select name="environment_initiative_type" id="environmentInitiativeType" class="form-select environment-flow-field">
        <option value="">Select initiative type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::environmentInitiativeTypes() as $initiativeType)
            <option value="{{ $initiativeType }}" @selected(old('environment_initiative_type', data_get($post->meta, 'environment_initiative_type')) === $initiativeType)>{{ $initiativeType }}</option>
        @endforeach
    </select>
</div>

<div id="environmentWaterConservationSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-info-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Water conservation</h5>
            <p class="text-muted mb-0 small">A flagship SoilnWater feature — document water sources, conservation methods, and estimated savings.</p>
        </div>
        <span class="badge bg-info text-dark">SoilnWater flagship</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="environmentWaterSource">Water source</label>
            <select name="environment_water_source" id="environmentWaterSource" class="form-select environment-flow-field">
                <option value="">Select water source</option>
                @foreach(\App\Support\CommunityContentTaxonomy::environmentWaterSources() as $source)
                    <option value="{{ $source }}" @selected(old('environment_water_source', data_get($post->meta, 'environment_water_source')) === $source)>{{ $source }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentConservationMethod">Conservation method</label>
            <select name="environment_conservation_method" id="environmentConservationMethod" class="form-select environment-flow-field">
                <option value="">Select conservation method</option>
                @foreach(\App\Support\CommunityContentTaxonomy::environmentConservationMethods() as $method)
                    <option value="{{ $method }}" @selected(old('environment_conservation_method', data_get($post->meta, 'environment_conservation_method')) === $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentWaterSaved">Estimated water saved</label>
            <input type="text" name="environment_water_saved" id="environmentWaterSaved" class="form-control environment-flow-field" maxlength="120" value="{{ old('environment_water_saved', data_get($post->meta, 'environment_water_saved')) }}" placeholder="e.g. 50,000 litres per year">
            <small class="text-muted">Optional</small>
        </div>
    </div>
</div>

<div id="environmentSoilConservationSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Soil conservation</h5>
            <p class="text-muted mb-0 small">Document soil protection practices used or observed.</p>
        </div>
        <span class="badge bg-warning text-dark">SoilnWater</span>
    </div>
    <label class="form-label d-block">Conservation methods</label>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentSoilConservationMethods() as $method)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="environment_soil_conservation_methods[]" value="{{ $method }}" class="form-check-input environment-flow-field" @checked(in_array($method, (array) old('environment_soil_conservation_methods', data_get($post->meta, 'environment_soil_conservation_methods', [])), true))>
                <span class="form-check-label">{{ $method }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="environmentTreePlantationSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tree plantation details</h5>
            <p class="text-muted mb-0 small">Record plantation specifics. GPS coordinates come from the location map above.</p>
        </div>
        <span class="badge bg-success text-white">If applicable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="environmentTreeCount">Number of trees</label>
            <input type="number" name="environment_tree_count" id="environmentTreeCount" class="form-control environment-flow-field" min="0" value="{{ old('environment_tree_count', data_get($post->meta, 'environment_tree_count')) }}" placeholder="e.g. 250">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="environmentTreeSpecies">Species</label>
            <input type="text" name="environment_tree_species" id="environmentTreeSpecies" class="form-control environment-flow-field" maxlength="255" value="{{ old('environment_tree_species', data_get($post->meta, 'environment_tree_species')) }}" placeholder="e.g. Neem, Peepal, Banyan">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="environmentTreePlantationDate">Plantation date</label>
            <input type="date" name="environment_tree_plantation_date" id="environmentTreePlantationDate" class="form-control environment-flow-field" value="{{ old('environment_tree_plantation_date', data_get($post->meta, 'environment_tree_plantation_date')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentTreeOrganization">Organization</label>
            <input type="text" name="environment_tree_organization" id="environmentTreeOrganization" class="form-control environment-flow-field" maxlength="160" value="{{ old('environment_tree_organization', data_get($post->meta, 'environment_tree_organization')) }}" placeholder="NGO, school, panchayat, etc.">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentTreeSurvivalStatus">Survival status</label>
            <select name="environment_tree_survival_status" id="environmentTreeSurvivalStatus" class="form-select environment-flow-field">
                <option value="">Select status</option>
                @foreach(\App\Support\CommunityContentTaxonomy::environmentTreeSurvivalStatuses() as $status)
                    <option value="{{ $status }}" @selected(old('environment_tree_survival_status', data_get($post->meta, 'environment_tree_survival_status')) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="environmentTreeMaintenancePlan">Maintenance plan</label>
            <textarea name="environment_tree_maintenance_plan" id="environmentTreeMaintenancePlan" class="form-control environment-flow-field" rows="2" maxlength="2000" placeholder="Watering schedule, protection measures, follow-up inspections">{{ old('environment_tree_maintenance_plan', data_get($post->meta, 'environment_tree_maintenance_plan')) }}</textarea>
        </div>
    </div>
</div>

<div id="environmentWasteManagementSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Waste management</h5>
            <p class="text-muted mb-0 small">Select the waste types relevant to this post.</p>
        </div>
        <span class="badge bg-secondary text-white">Waste</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentWasteTypes() as $wasteType)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="environment_waste_types[]" value="{{ $wasteType }}" class="form-check-input environment-flow-field" @checked(in_array($wasteType, (array) old('environment_waste_types', data_get($post->meta, 'environment_waste_types', [])), true))>
                <span class="form-check-label">{{ $wasteType }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="environmentBiodiversitySection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Biodiversity</h5>
            <p class="text-muted mb-0 small">Document species or biodiversity categories observed or protected.</p>
        </div>
        <span class="badge bg-success text-white">Biodiversity</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentBiodiversityTypes() as $bioType)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="environment_biodiversity_types[]" value="{{ $bioType }}" class="form-check-input environment-flow-field" @checked(in_array($bioType, (array) old('environment_biodiversity_types', data_get($post->meta, 'environment_biodiversity_types', [])), true))>
                <span class="form-check-label">{{ $bioType }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="environmentClimateImpactSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-danger-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Climate impact</h5>
            <p class="text-muted mb-0 small">Optional — note climate-related events or risks linked to this post.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentClimateImpacts() as $impact)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="environment_climate_impacts[]" value="{{ $impact }}" class="form-check-input environment-flow-field" @checked(in_array($impact, (array) old('environment_climate_impacts', data_get($post->meta, 'environment_climate_impacts', [])), true))>
                <span class="form-check-label">{{ $impact }}</span>
            </label>
        @endforeach
    </div>
</div>
