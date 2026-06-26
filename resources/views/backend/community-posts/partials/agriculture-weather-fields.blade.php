<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Weather information</h5>
            <p class="text-muted mb-0 small">Optional weather impact context for your farming story.</p>
        </div>
        <span class="badge bg-secondary text-white">Optional</span>
    </div>
    <label class="form-label" for="agricultureWeatherImpact">Weather impact</label>
    <select name="agriculture_weather_impact" id="agricultureWeatherImpact" class="form-select agriculture-flow-field">
        <option value="">Select weather impact</option>
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureWeatherImpacts() as $impact)
            <option value="{{ $impact }}" @selected(old('agriculture_weather_impact', data_get($post->meta, 'agriculture_weather_impact')) === $impact)>{{ $impact }}</option>
        @endforeach
    </select>
</div>
