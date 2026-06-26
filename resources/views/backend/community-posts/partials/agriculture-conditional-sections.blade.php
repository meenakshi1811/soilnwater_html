<div id="agricultureProblemSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-danger-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Problem reporting</h5>
            <p class="text-muted mb-0 small">For farmers seeking help — describe the issue and upload crop problem photos.</p>
        </div>
        <span class="badge bg-danger text-white">Seeking help</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureProblemType">Problem type</label>
            <select name="agriculture_problem_type" id="agricultureProblemType" class="form-select agriculture-flow-field">
                <option value="">Select problem type</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureProblemTypes() as $problemType)
                    <option value="{{ $problemType }}" @selected(old('agriculture_problem_type', data_get($post->meta, 'agriculture_problem_type')) === $problemType)>{{ $problemType }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label d-block">Expert assistance required</label>
            <div class="d-flex flex-wrap gap-3 mt-2">
                <label class="form-check">
                    <input type="radio" name="agriculture_expert_assistance" value="yes" class="form-check-input agriculture-flow-field" @checked(old('agriculture_expert_assistance', data_get($post->meta, 'agriculture_expert_assistance')) === 'yes')>
                    <span class="form-check-label">Yes</span>
                </label>
                <label class="form-check">
                    <input type="radio" name="agriculture_expert_assistance" value="no" class="form-check-input agriculture-flow-field" @checked(old('agriculture_expert_assistance', data_get($post->meta, 'agriculture_expert_assistance')) === 'no')>
                    <span class="form-check-label">No</span>
                </label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label" for="agricultureProblemPhotos">Upload images</label>
            <input type="file" name="agriculture_problem_photos[]" id="agricultureProblemPhotos" class="form-control agriculture-flow-field" accept="image/*" multiple>
            <small class="text-muted d-block mt-2">Photos of crop issues. JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
            @if(!empty(data_get($post->meta, 'agriculture_problem_photos')))
                <div class="mt-3 d-flex flex-column gap-2">
                    @foreach(data_get($post->meta, 'agriculture_problem_photos', []) as $photo)
                        <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                            <input type="checkbox" name="removed_agriculture_problem_photos[]" value="{{ data_get($photo, 'path') }}" class="form-check-input agriculture-flow-field">
                            <span class="form-check-label">Remove {{ data_get($photo, 'name', 'photo') }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div id="agricultureMachinerySection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Farm machinery</h5>
            <p class="text-muted mb-0 small">Share equipment experience, cost, and benefits.</p>
        </div>
        <span class="badge bg-secondary text-white">If applicable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureEquipmentName">Equipment name</label>
            <input type="text" name="agriculture_equipment_name" id="agricultureEquipmentName" class="form-control agriculture-flow-field" maxlength="160" value="{{ old('agriculture_equipment_name', data_get($post->meta, 'agriculture_equipment_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureEquipmentManufacturer">Manufacturer</label>
            <input type="text" name="agriculture_equipment_manufacturer" id="agricultureEquipmentManufacturer" class="form-control agriculture-flow-field" maxlength="160" value="{{ old('agriculture_equipment_manufacturer', data_get($post->meta, 'agriculture_equipment_manufacturer')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureEquipmentCost">Cost</label>
            <input type="text" name="agriculture_equipment_cost" id="agricultureEquipmentCost" class="form-control agriculture-flow-field" maxlength="120" value="{{ old('agriculture_equipment_cost', data_get($post->meta, 'agriculture_equipment_cost')) }}" placeholder="e.g. ₹2,50,000">
        </div>
        <div class="col-12">
            <label class="form-label" for="agricultureEquipmentExperience">Experience / review</label>
            <textarea name="agriculture_equipment_experience" id="agricultureEquipmentExperience" class="form-control agriculture-flow-field" rows="3" maxlength="3000">{{ old('agriculture_equipment_experience', data_get($post->meta, 'agriculture_equipment_experience')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="agricultureEquipmentBenefits">Benefits</label>
            <textarea name="agriculture_equipment_benefits" id="agricultureEquipmentBenefits" class="form-control agriculture-flow-field" rows="2" maxlength="2000">{{ old('agriculture_equipment_benefits', data_get($post->meta, 'agriculture_equipment_benefits')) }}</textarea>
        </div>
    </div>
</div>

<div id="agricultureSchemeSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Government scheme</h5>
            <p class="text-muted mb-0 small">Help farmers discover schemes, eligibility, and application details.</p>
        </div>
        <span class="badge bg-secondary text-white">If applicable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureSchemeName">Scheme name</label>
            <input type="text" name="agriculture_scheme_name" id="agricultureSchemeName" class="form-control agriculture-flow-field" maxlength="160" value="{{ old('agriculture_scheme_name', data_get($post->meta, 'agriculture_scheme_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureSchemeDepartment">Department</label>
            <input type="text" name="agriculture_scheme_department" id="agricultureSchemeDepartment" class="form-control agriculture-flow-field" maxlength="160" value="{{ old('agriculture_scheme_department', data_get($post->meta, 'agriculture_scheme_department')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureSchemeSubsidy">Subsidy amount</label>
            <input type="text" name="agriculture_scheme_subsidy" id="agricultureSchemeSubsidy" class="form-control agriculture-flow-field" maxlength="120" value="{{ old('agriculture_scheme_subsidy', data_get($post->meta, 'agriculture_scheme_subsidy')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureSchemeLastDate">Last date</label>
            <input type="date" name="agriculture_scheme_last_date" id="agricultureSchemeLastDate" class="form-control agriculture-flow-field" value="{{ old('agriculture_scheme_last_date', data_get($post->meta, 'agriculture_scheme_last_date')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="agricultureSchemeEligibility">Eligibility</label>
            <textarea name="agriculture_scheme_eligibility" id="agricultureSchemeEligibility" class="form-control agriculture-flow-field" rows="2" maxlength="2000">{{ old('agriculture_scheme_eligibility', data_get($post->meta, 'agriculture_scheme_eligibility')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="agricultureSchemeApplicationLink">Application link</label>
            <input type="url" name="agriculture_scheme_application_link" id="agricultureSchemeApplicationLink" class="form-control agriculture-flow-field" maxlength="255" value="{{ old('agriculture_scheme_application_link', data_get($post->meta, 'agriculture_scheme_application_link')) }}" placeholder="https://">
        </div>
    </div>
</div>

<div id="agricultureMarketSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Market information</h5>
            <p class="text-muted mb-0 small">Share commodity prices and market trends with the farming community.</p>
        </div>
        <span class="badge bg-secondary text-white">If applicable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureMarketCommodity">Commodity</label>
            <input type="text" name="agriculture_market_commodity" id="agricultureMarketCommodity" class="form-control agriculture-flow-field" maxlength="120" value="{{ old('agriculture_market_commodity', data_get($post->meta, 'agriculture_market_commodity')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureMarketName">Market name</label>
            <input type="text" name="agriculture_market_name" id="agricultureMarketName" class="form-control agriculture-flow-field" maxlength="160" value="{{ old('agriculture_market_name', data_get($post->meta, 'agriculture_market_name')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="agricultureMarketPrice">Price</label>
            <input type="text" name="agriculture_market_price" id="agricultureMarketPrice" class="form-control agriculture-flow-field" maxlength="80" value="{{ old('agriculture_market_price', data_get($post->meta, 'agriculture_market_price')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="agricultureMarketDate">Date</label>
            <input type="date" name="agriculture_market_date" id="agricultureMarketDate" class="form-control agriculture-flow-field" value="{{ old('agriculture_market_date', data_get($post->meta, 'agriculture_market_date')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="agricultureMarketPriceTrend">Price trend</label>
            <select name="agriculture_market_price_trend" id="agricultureMarketPriceTrend" class="form-select agriculture-flow-field">
                <option value="">Select trend</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agriculturePriceTrends() as $trend)
                    <option value="{{ $trend }}" @selected(old('agriculture_market_price_trend', data_get($post->meta, 'agriculture_market_price_trend')) === $trend)>{{ $trend }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div id="agricultureLivestockSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Livestock</h5>
            <p class="text-muted mb-0 small">Select livestock types covered in this post.</p>
        </div>
        <span class="badge bg-secondary text-white">If applicable</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureLivestockTypes() as $livestockType)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input
                    type="checkbox"
                    name="agriculture_livestock_types[]"
                    value="{{ $livestockType }}"
                    class="form-check-input agriculture-flow-field"
                    @checked(in_array($livestockType, (array) old('agriculture_livestock_types', data_get($post->meta, 'agriculture_livestock_types', [])), true))
                >
                <span class="form-check-label">{{ $livestockType }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="agricultureInnovationSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Agricultural innovation</h5>
            <p class="text-muted mb-0 small">For new ideas, techniques, and field innovations.</p>
        </div>
        <span class="badge bg-secondary text-white">If applicable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="agricultureInnovationName">Innovation name</label>
            <input type="text" name="agriculture_innovation_name" id="agricultureInnovationName" class="form-control agriculture-flow-field" maxlength="160" value="{{ old('agriculture_innovation_name', data_get($post->meta, 'agriculture_innovation_name')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="agricultureInnovationDescription">Description</label>
            <textarea name="agriculture_innovation_description" id="agricultureInnovationDescription" class="form-control agriculture-flow-field" rows="3" maxlength="3000">{{ old('agriculture_innovation_description', data_get($post->meta, 'agriculture_innovation_description')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureInnovationBenefits">Benefits</label>
            <textarea name="agriculture_innovation_benefits" id="agricultureInnovationBenefits" class="form-control agriculture-flow-field" rows="2" maxlength="2000">{{ old('agriculture_innovation_benefits', data_get($post->meta, 'agriculture_innovation_benefits')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureInnovationResults">Results</label>
            <textarea name="agriculture_innovation_results" id="agricultureInnovationResults" class="form-control agriculture-flow-field" rows="2" maxlength="2000">{{ old('agriculture_innovation_results', data_get($post->meta, 'agriculture_innovation_results')) }}</textarea>
        </div>
    </div>
</div>

<div id="agricultureAgriBusinessSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Agri-business</h5>
            <p class="text-muted mb-0 small">Optional business context for suppliers, dealers, and processors.</p>
        </div>
        <span class="badge bg-secondary text-white">Optional</span>
    </div>
    <label class="form-label" for="agricultureAgriBusinessType">Business type</label>
    <select name="agriculture_agri_business_type" id="agricultureAgriBusinessType" class="form-select agriculture-flow-field">
        <option value="">Select business type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureAgriBusinessTypes() as $businessType)
            <option value="{{ $businessType }}" @selected(old('agriculture_agri_business_type', data_get($post->meta, 'agriculture_agri_business_type')) === $businessType)>{{ $businessType }}</option>
        @endforeach
    </select>
</div>
