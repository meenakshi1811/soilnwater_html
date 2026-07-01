<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Creation type</h5>
            <p class="text-muted mb-0 small">How would you describe this work?</p>
        </div>
    </div>
    <label class="form-label" for="creativeCornerCreationType">Creation type</label>
    <select name="creative_corner_creation_type" id="creativeCornerCreationType" class="form-select creative-corner-flow-field">
        <option value="">Select creation type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerCreationTypes() as $type)
            <option value="{{ $type }}" @selected(old('creative_corner_creation_type', data_get($post->meta, 'creative_corner_creation_type')) === $type)>{{ $type }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Medium used</h5>
            <p class="text-muted mb-0 small">Select all mediums that apply.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerMediums() as $medium)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="creative_corner_mediums[]" value="{{ $medium }}" class="form-check-input creative-corner-flow-field" @checked(in_array($medium, (array) old('creative_corner_mediums', data_get($post->meta, 'creative_corner_mediums', [])), true))>
                <span class="form-check-label">{{ $medium }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Software / tools used</h5>
            <p class="text-muted mb-0 small">Optional — select tools used in creating this work.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerSoftwareTools() as $tool)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="creative_corner_software_tools[]" value="{{ $tool }}" class="form-check-input creative-corner-flow-field" @checked(in_array($tool, (array) old('creative_corner_software_tools', data_get($post->meta, 'creative_corner_software_tools', [])), true))>
                <span class="form-check-label">{{ $tool }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Materials used</h5>
            <p class="text-muted mb-0 small">Optional — physical materials used.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerMaterials() as $material)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="creative_corner_materials[]" value="{{ $material }}" class="form-check-input creative-corner-flow-field" @checked(in_array($material, (array) old('creative_corner_materials', data_get($post->meta, 'creative_corner_materials', [])), true))>
                <span class="form-check-label">{{ $material }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="creativeCornerCreationDate">Creation date</label>
            <input type="date" name="creative_corner_creation_date" id="creativeCornerCreationDate" class="form-control creative-corner-flow-field" value="{{ old('creative_corner_creation_date', data_get($post->meta, 'creative_corner_creation_date')) }}">
            <small class="text-muted">Useful for portfolio building.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="creativeCornerTimeTaken">Time taken</label>
            <input type="text" name="creative_corner_time_taken" id="creativeCornerTimeTaken" class="form-control creative-corner-flow-field" maxlength="120" value="{{ old('creative_corner_time_taken', data_get($post->meta, 'creative_corner_time_taken')) }}" placeholder="e.g. 2 Hours, 3 Days">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="creativeCornerDifficultyLevel">Difficulty level</label>
            <select name="creative_corner_difficulty_level" id="creativeCornerDifficultyLevel" class="form-select creative-corner-flow-field">
                <option value="">Select difficulty (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerDifficultyLevels() as $level)
                    <option value="{{ $level }}" @selected(old('creative_corner_difficulty_level', data_get($post->meta, 'creative_corner_difficulty_level')) === $level)>{{ $level }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Theme</h5>
            <p class="text-muted mb-0 small">Multiple selection — themes reflected in this work.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerThemes() as $theme)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="creative_corner_themes[]" value="{{ $theme }}" class="form-check-input creative-corner-flow-field" @checked(in_array($theme, (array) old('creative_corner_themes', data_get($post->meta, 'creative_corner_themes', [])), true))>
                <span class="form-check-label">{{ $theme }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Useful for local creative communities.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="ccLocationCountry">Country</label>
            <input type="text" name="creative_corner_location_country" id="ccLocationCountry" class="form-control creative-corner-flow-field" maxlength="120" value="{{ old('creative_corner_location_country', data_get($post->meta, 'creative_corner_location_country')) }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="ccLocationState">State</label>
            <input type="text" name="creative_corner_location_state" id="ccLocationState" class="form-control creative-corner-flow-field" maxlength="120" value="{{ old('creative_corner_location_state', data_get($post->meta, 'creative_corner_location_state')) }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="ccLocationDistrict">District</label>
            <input type="text" name="creative_corner_location_district" id="ccLocationDistrict" class="form-control creative-corner-flow-field" maxlength="120" value="{{ old('creative_corner_location_district', data_get($post->meta, 'creative_corner_location_district')) }}">
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label" for="ccLocationCity">City</label>
            <input type="text" name="creative_corner_location_city" id="ccLocationCity" class="form-control creative-corner-flow-field" maxlength="120" value="{{ old('creative_corner_location_city', data_get($post->meta, 'creative_corner_location_city')) }}">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Project cost</h5>
            <p class="text-muted mb-0 small">Optional — useful for DIY projects.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="ccMaterialCost">Material cost</label>
            <input type="text" name="creative_corner_material_cost" id="ccMaterialCost" class="form-control creative-corner-flow-field" maxlength="60" value="{{ old('creative_corner_material_cost', data_get($post->meta, 'creative_corner_material_cost')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="ccEquipmentCost">Equipment cost</label>
            <input type="text" name="creative_corner_equipment_cost" id="ccEquipmentCost" class="form-control creative-corner-flow-field" maxlength="60" value="{{ old('creative_corner_equipment_cost', data_get($post->meta, 'creative_corner_equipment_cost')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="ccTotalCost">Total cost</label>
            <input type="text" name="creative_corner_total_cost" id="ccTotalCost" class="form-control creative-corner-flow-field" maxlength="60" value="{{ old('creative_corner_total_cost', data_get($post->meta, 'creative_corner_total_cost')) }}">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Competition entry</h5>
            <p class="text-muted mb-0 small">Submit this work to a creative competition.</p>
        </div>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="ccSubmitToCompetition">
        <input type="checkbox" name="creative_corner_submit_to_competition" value="1" class="form-check-input creative-corner-flow-field" id="ccSubmitToCompetition" @checked(old('creative_corner_submit_to_competition', data_get($post->meta, 'creative_corner_submit_to_competition', false)))>
        <span class="form-check-label">Submit to Creative Competition</span>
    </label>
    <div id="ccCompetitionFields" style="display:none;">
        <label class="form-label d-block">Competition categories</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerCompetitionCategories() as $category)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="creative_corner_competition_categories[]" value="{{ $category }}" class="form-check-input creative-corner-flow-field" @checked(in_array($category, (array) old('creative_corner_competition_categories', data_get($post->meta, 'creative_corner_competition_categories', [])), true))>
                    <span class="form-check-label">{{ $category }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Sell this creation</h5>
            <p class="text-muted mb-0 small">Unique SoilnWater feature — direct integration with the e-commerce platform.</p>
        </div>
        <span class="badge bg-warning text-dark">SoilnWater unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="ccAvailableForSale">
        <input type="checkbox" name="creative_corner_available_for_sale" value="1" class="form-check-input creative-corner-flow-field" id="ccAvailableForSale" @checked(old('creative_corner_available_for_sale', data_get($post->meta, 'creative_corner_available_for_sale', false)))>
        <span class="form-check-label">Available for Sale</span>
    </label>
    <div id="ccSaleFields" style="display:none;">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="ccSalePrice">Price</label>
                <input type="text" name="creative_corner_sale_price" id="ccSalePrice" class="form-control creative-corner-flow-field" maxlength="60" value="{{ old('creative_corner_sale_price', data_get($post->meta, 'creative_corner_sale_price')) }}">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="d-flex flex-wrap gap-3">
                    <label class="form-check">
                        <input type="checkbox" name="creative_corner_custom_orders_accepted" value="1" class="form-check-input creative-corner-flow-field" @checked(old('creative_corner_custom_orders_accepted', data_get($post->meta, 'creative_corner_custom_orders_accepted', false)))>
                        <span class="form-check-label">Custom orders accepted</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="creative_corner_limited_edition" value="1" class="form-check-input creative-corner-flow-field" @checked(old('creative_corner_limited_edition', data_get($post->meta, 'creative_corner_limited_edition', false)))>
                        <span class="form-check-label">Limited edition</span>
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="creative_corner_shipping_available" value="1" class="form-check-input creative-corner-flow-field" @checked(old('creative_corner_shipping_available', data_get($post->meta, 'creative_corner_shipping_available', false)))>
                        <span class="form-check-label">Shipping available</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Commission work</h5>
            <p class="text-muted mb-0 small">Let the community know what services you offer.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerCommissionOptions() as $option)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="creative_corner_commission_options[]" value="{{ $option }}" class="form-check-input creative-corner-flow-field" @checked(in_array($option, (array) old('creative_corner_commission_options', data_get($post->meta, 'creative_corner_commission_options', [])), true))>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-label" for="creativeCornerCopyright">Copyright</label>
    <select name="creative_corner_copyright" id="creativeCornerCopyright" class="form-select creative-corner-flow-field">
        <option value="">Select copyright type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerCopyrightOptions() as $copyright)
            <option value="{{ $copyright }}" @selected(old('creative_corner_copyright', data_get($post->meta, 'creative_corner_copyright')) === $copyright)>{{ $copyright }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Social links</h5>
            <p class="text-muted mb-0 small">Optional — share your portfolio and profiles.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="ccSocialPortfolio">Portfolio</label>
            <input type="text" name="creative_corner_social_portfolio" id="ccSocialPortfolio" class="form-control creative-corner-flow-field" maxlength="255" value="{{ old('creative_corner_social_portfolio', data_get($post->meta, 'creative_corner_social_portfolio')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="ccSocialInstagram">Instagram</label>
            <input type="text" name="creative_corner_social_instagram" id="ccSocialInstagram" class="form-control creative-corner-flow-field" maxlength="255" value="{{ old('creative_corner_social_instagram', data_get($post->meta, 'creative_corner_social_instagram')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="ccSocialYoutube">YouTube</label>
            <input type="text" name="creative_corner_social_youtube" id="ccSocialYoutube" class="form-control creative-corner-flow-field" maxlength="255" value="{{ old('creative_corner_social_youtube', data_get($post->meta, 'creative_corner_social_youtube')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="ccSocialWebsite">Website</label>
            <input type="url" name="creative_corner_social_website" id="ccSocialWebsite" class="form-control creative-corner-flow-field" maxlength="255" value="{{ old('creative_corner_social_website', data_get($post->meta, 'creative_corner_social_website')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="ccSocialVendorProfile">SoilnWater vendor profile</label>
            <input type="text" name="creative_corner_social_vendor_profile" id="ccSocialVendorProfile" class="form-control creative-corner-flow-field" maxlength="255" value="{{ old('creative_corner_social_vendor_profile', data_get($post->meta, 'creative_corner_social_vendor_profile')) }}" placeholder="Link to your SoilnWater vendor profile">
        </div>
    </div>
</div>
