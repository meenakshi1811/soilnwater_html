<div id="astroHoroscopeSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Zodiac section</h5>
            <p class="text-muted mb-0 small">For horoscope posts — select the sign and period covered.</p>
        </div>
        <span class="badge bg-warning text-dark">Horoscope</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="astroConsultancyZodiacSign">Zodiac sign</label>
            <select name="astro_consultancy_zodiac_sign" id="astroConsultancyZodiacSign" class="form-select astro-consultancy-flow-field">
                <option value="">Select zodiac sign</option>
                @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyZodiacSigns() as $sign)
                    <option value="{{ $sign }}" @selected(old('astro_consultancy_zodiac_sign', data_get($post->meta, 'astro_consultancy_zodiac_sign')) === $sign)>{{ $sign }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="astroConsultancyHoroscopePeriod">Horoscope period</label>
            <select name="astro_consultancy_horoscope_period" id="astroConsultancyHoroscopePeriod" class="form-select astro-consultancy-flow-field">
                <option value="">Select period</option>
                @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyHoroscopePeriods() as $period)
                    <option value="{{ $period }}" @selected(old('astro_consultancy_horoscope_period', data_get($post->meta, 'astro_consultancy_horoscope_period')) === $period)>{{ $period }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div id="astroVastuSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-info-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Vastu section</h5>
            <p class="text-muted mb-0 small">Property type and areas covered in this Vastu guidance.</p>
        </div>
        <span class="badge bg-info text-dark">Vastu</span>
    </div>
    <div class="mb-3">
        <label class="form-label d-block">Property type</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyVastuPropertyTypes() as $propertyType)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="astro_consultancy_vastu_property_types[]" value="{{ $propertyType }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($propertyType, (array) old('astro_consultancy_vastu_property_types', data_get($post->meta, 'astro_consultancy_vastu_property_types', [])), true))>
                    <span class="form-check-label">{{ $propertyType }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div>
        <label class="form-label d-block">Area</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyVastuAreas() as $area)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="astro_consultancy_vastu_areas[]" value="{{ $area }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($area, (array) old('astro_consultancy_vastu_areas', data_get($post->meta, 'astro_consultancy_vastu_areas', [])), true))>
                    <span class="form-check-label">{{ $area }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<div id="astroNumerologySection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Numerology section</h5>
            <p class="text-muted mb-0 small">Optional numerology details for educational numerology posts.</p>
        </div>
        <span class="badge bg-primary text-white">Numerology</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="astroLifePathNumber">Life path number</label>
            <input type="text" name="astro_consultancy_life_path_number" id="astroLifePathNumber" class="form-control astro-consultancy-flow-field" maxlength="40" value="{{ old('astro_consultancy_life_path_number', data_get($post->meta, 'astro_consultancy_life_path_number')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="astroDestinyNumber">Destiny number</label>
            <input type="text" name="astro_consultancy_destiny_number" id="astroDestinyNumber" class="form-control astro-consultancy-flow-field" maxlength="40" value="{{ old('astro_consultancy_destiny_number', data_get($post->meta, 'astro_consultancy_destiny_number')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="astroNameNumber">Name number</label>
            <input type="text" name="astro_consultancy_name_number" id="astroNameNumber" class="form-control astro-consultancy-flow-field" maxlength="40" value="{{ old('astro_consultancy_name_number', data_get($post->meta, 'astro_consultancy_name_number')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="astroLuckyNumber">Lucky number</label>
            <input type="text" name="astro_consultancy_lucky_number" id="astroLuckyNumber" class="form-control astro-consultancy-flow-field" maxlength="40" value="{{ old('astro_consultancy_lucky_number', data_get($post->meta, 'astro_consultancy_lucky_number')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="astroCompatibility">Compatibility</label>
            <input type="text" name="astro_consultancy_compatibility" id="astroCompatibility" class="form-control astro-consultancy-flow-field" maxlength="160" value="{{ old('astro_consultancy_compatibility', data_get($post->meta, 'astro_consultancy_compatibility')) }}" placeholder="e.g. Number 1 with Number 5">
        </div>
    </div>
</div>

<div id="astroGemstoneSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Gemstone section</h5>
            <p class="text-muted mb-0 small">Optional — share traditional gemstone guidance with appropriate cautions.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="astroGemstone">Gemstone</label>
            <input type="text" name="astro_consultancy_gemstone" id="astroGemstone" class="form-control astro-consultancy-flow-field" maxlength="120" value="{{ old('astro_consultancy_gemstone', data_get($post->meta, 'astro_consultancy_gemstone')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="astroGemstonePlanet">Planet</label>
            <input type="text" name="astro_consultancy_gemstone_planet" id="astroGemstonePlanet" class="form-control astro-consultancy-flow-field" maxlength="80" value="{{ old('astro_consultancy_gemstone_planet', data_get($post->meta, 'astro_consultancy_gemstone_planet')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="astroGemstoneBenefits">Traditional benefits</label>
            <textarea name="astro_consultancy_gemstone_benefits" id="astroGemstoneBenefits" class="form-control astro-consultancy-flow-field" rows="3" maxlength="2000">{{ old('astro_consultancy_gemstone_benefits', data_get($post->meta, 'astro_consultancy_gemstone_benefits')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="astroGemstonePrecautions">Precautions</label>
            <textarea name="astro_consultancy_gemstone_precautions" id="astroGemstonePrecautions" class="form-control astro-consultancy-flow-field" rows="3" maxlength="2000">{{ old('astro_consultancy_gemstone_precautions', data_get($post->meta, 'astro_consultancy_gemstone_precautions')) }}</textarea>
        </div>
    </div>
</div>

<div id="astroFestivalSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Festival &amp; muhurat section</h5>
            <p class="text-muted mb-0 small">Festival dates, muhurat timings, and traditional significance.</p>
        </div>
        <span class="badge bg-success text-white">Festival &amp; Muhurat</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="astroFestivalName">Festival name</label>
            <input type="text" name="astro_consultancy_festival_name" id="astroFestivalName" class="form-control astro-consultancy-flow-field" maxlength="160" value="{{ old('astro_consultancy_festival_name', data_get($post->meta, 'astro_consultancy_festival_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="astroMuhuratType">Muhurat type</label>
            <input type="text" name="astro_consultancy_muhurat_type" id="astroMuhuratType" class="form-control astro-consultancy-flow-field" maxlength="120" value="{{ old('astro_consultancy_muhurat_type', data_get($post->meta, 'astro_consultancy_muhurat_type')) }}" placeholder="e.g. Marriage, Griha Pravesh">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="astroMuhuratDate">Date</label>
            <input type="date" name="astro_consultancy_muhurat_date" id="astroMuhuratDate" class="form-control astro-consultancy-flow-field" value="{{ old('astro_consultancy_muhurat_date', data_get($post->meta, 'astro_consultancy_muhurat_date')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="astroMuhuratTime">Time</label>
            <input type="text" name="astro_consultancy_muhurat_time" id="astroMuhuratTime" class="form-control astro-consultancy-flow-field" maxlength="120" value="{{ old('astro_consultancy_muhurat_time', data_get($post->meta, 'astro_consultancy_muhurat_time')) }}" placeholder="e.g. 10:30 AM – 12:15 PM">
        </div>
        <div class="col-12">
            <label class="form-label" for="astroFestivalSignificance">Traditional significance</label>
            <textarea name="astro_consultancy_festival_significance" id="astroFestivalSignificance" class="form-control astro-consultancy-flow-field" rows="3" maxlength="3000">{{ old('astro_consultancy_festival_significance', data_get($post->meta, 'astro_consultancy_festival_significance')) }}</textarea>
        </div>
    </div>
</div>
