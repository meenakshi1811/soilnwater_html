@php
    $metaLabels = \App\Support\CommunityPostFormFields::scienceTechnologyDetailMetaOrder();
    $skipKeys = [
        'science_technology_post_type',
        'science_technology_category',
        'science_technology_technologies_used',
        'science_technology_water_soil_topics',
        'science_technology_ask_community',
    ];
    $orderedMeta = collect($metaLabels)
        ->reject(fn ($label, $key) => in_array($key, $skipKeys, true))
        ->mapWithKeys(fn ($label, $key) => [$key => data_get($post->meta, $key)])
        ->filter(fn ($value) => filled($value) || is_bool($value));
    $pillKeys = [
        'science_technology_target_audience',
        'science_technology_scientific_fields',
        'science_technology_technologies_used',
        'science_technology_programming_languages',
        'science_technology_water_soil_topics',
        'science_technology_renewable_energy',
        'science_technology_funding_types',
        'science_technology_application_areas',
        'science_technology_reference_types',
        'science_technology_open_innovation',
        'science_technology_challenge_themes',
        'science_technology_collaboration_requests',
        'science_technology_comment_settings',
        'science_technology_poll_options',
    ];
    $textareaKeys = [
        'science_technology_project_objective',
        'science_technology_project_components',
        'science_technology_project_working_principle',
        'science_technology_project_results',
        'science_technology_project_future_improvements',
        'science_technology_research_abstract',
        'science_technology_research_methodology',
        'science_technology_research_results',
        'science_technology_research_conclusion',
        'science_technology_research_references',
        'science_technology_experiment_objective',
        'science_technology_experiment_materials',
        'science_technology_experiment_procedure',
        'science_technology_experiment_observations',
        'science_technology_experiment_results',
        'science_technology_experiment_safety',
        'science_technology_problem_solved',
        'science_technology_novel_features',
        'science_technology_innovation_technology',
        'science_technology_innovation_benefits',
        'science_technology_commercial_potential',
        'science_technology_hardware_components',
        'science_technology_bom',
        'science_technology_references',
        'science_technology_ask_community',
    ];
@endphp

@if($post->isScienceTechnologyPost() && ($orderedMeta->isNotEmpty() || ($includeAdmin ?? false)))
    <div class="about-box mt-4 business-meta-grid chart-card p-3 p-lg-4">
        <h4>{{ $heading ?? (($includeAdmin ?? false) ? 'Saved Science & Technology metadata' : 'Science & Technology details') }}</h4>

        @if($orderedMeta->isNotEmpty())
            <div class="row g-3">
                @foreach($orderedMeta as $key => $value)
                    <div class="col-md-6">
                        <div class="business-meta-item h-100">
                            <span class="business-meta-item__label">{{ $metaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            @if(in_array($key, $pillKeys, true) && is_array($value))
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($value as $item)
                                        <span class="badge bg-light text-dark border">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @elseif(in_array($key, $textareaKeys, true))
                                <span>{!! nl2br(e((string) $value)) !!}</span>
                            @elseif(is_bool($value))
                                <span>{{ $value ? 'Enabled' : 'Disabled' }}</span>
                            @elseif($key === 'science_technology_github_repo' && filled($value))
                                <a href="{{ $value }}" target="_blank" rel="noopener">{{ $value }}</a>
                            @else
                                <span>{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
