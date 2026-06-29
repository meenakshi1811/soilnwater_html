<div id="stProjectSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-info-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Project details</h5>
            <p class="text-muted mb-0 small">For project showcase posts.</p>
        </div>
        <span class="badge bg-info text-dark">Project Showcase</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="stProjectName">Project name</label>
            <input type="text" name="science_technology_project_name" id="stProjectName" class="form-control science-technology-flow-field" maxlength="160" value="{{ old('science_technology_project_name', data_get($post->meta, 'science_technology_project_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stProjectCategory">Project category</label>
            <select name="science_technology_project_category" id="stProjectCategory" class="form-select science-technology-flow-field">
                <option value="">Select category</option>
                @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyProjectCategories() as $cat)
                    <option value="{{ $cat }}" @selected(old('science_technology_project_category', data_get($post->meta, 'science_technology_project_category')) === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="stProjectObjective">Objective</label>
            <textarea name="science_technology_project_objective" id="stProjectObjective" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_project_objective', data_get($post->meta, 'science_technology_project_objective')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stProjectComponents">Components used</label>
            <textarea name="science_technology_project_components" id="stProjectComponents" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ old('science_technology_project_components', data_get($post->meta, 'science_technology_project_components')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stProjectWorkingPrinciple">Working principle</label>
            <textarea name="science_technology_project_working_principle" id="stProjectWorkingPrinciple" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_project_working_principle', data_get($post->meta, 'science_technology_project_working_principle')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stProjectResults">Results</label>
            <textarea name="science_technology_project_results" id="stProjectResults" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_project_results', data_get($post->meta, 'science_technology_project_results')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stProjectFutureImprovements">Future improvements</label>
            <textarea name="science_technology_project_future_improvements" id="stProjectFutureImprovements" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ old('science_technology_project_future_improvements', data_get($post->meta, 'science_technology_project_future_improvements')) }}</textarea>
        </div>
    </div>
</div>

<div id="stResearchSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Research details</h5>
            <p class="text-muted mb-0 small">For research articles, summaries, and scientific discoveries.</p>
        </div>
        <span class="badge bg-primary text-white">Research</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="stResearchArea">Research area</label>
            <input type="text" name="science_technology_research_area" id="stResearchArea" class="form-control science-technology-flow-field" maxlength="160" value="{{ old('science_technology_research_area', data_get($post->meta, 'science_technology_research_area')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stResearchInstitution">Institution</label>
            <input type="text" name="science_technology_research_institution" id="stResearchInstitution" class="form-control science-technology-flow-field" maxlength="160" value="{{ old('science_technology_research_institution', data_get($post->meta, 'science_technology_research_institution')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stResearchDuration">Research duration</label>
            <input type="text" name="science_technology_research_duration" id="stResearchDuration" class="form-control science-technology-flow-field" maxlength="120" value="{{ old('science_technology_research_duration', data_get($post->meta, 'science_technology_research_duration')) }}" placeholder="e.g. Jan 2024 – Dec 2025">
        </div>
        <div class="col-12">
            <label class="form-label" for="stResearchAbstract">Abstract</label>
            <textarea name="science_technology_research_abstract" id="stResearchAbstract" class="form-control science-technology-flow-field" rows="4" maxlength="5000">{{ old('science_technology_research_abstract', data_get($post->meta, 'science_technology_research_abstract')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stResearchKeywords">Keywords</label>
            <input type="text" name="science_technology_research_keywords" id="stResearchKeywords" class="form-control science-technology-flow-field" maxlength="500" value="{{ old('science_technology_research_keywords', data_get($post->meta, 'science_technology_research_keywords')) }}" placeholder="Comma-separated">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stResearchMethodology">Methodology</label>
            <textarea name="science_technology_research_methodology" id="stResearchMethodology" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_research_methodology', data_get($post->meta, 'science_technology_research_methodology')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stResearchResults">Results</label>
            <textarea name="science_technology_research_results" id="stResearchResults" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_research_results', data_get($post->meta, 'science_technology_research_results')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stResearchConclusion">Conclusion</label>
            <textarea name="science_technology_research_conclusion" id="stResearchConclusion" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_research_conclusion', data_get($post->meta, 'science_technology_research_conclusion')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="stResearchReferences">References</label>
            <textarea name="science_technology_research_references" id="stResearchReferences" class="form-control science-technology-flow-field" rows="4" maxlength="5000">{{ old('science_technology_research_references', data_get($post->meta, 'science_technology_research_references')) }}</textarea>
        </div>
    </div>
</div>

<div id="stExperimentSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Experiment details</h5>
            <p class="text-muted mb-0 small">Document your experiment step by step.</p>
        </div>
        <span class="badge bg-warning text-dark">Experiment</span>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="stExperimentObjective">Objective</label>
            <textarea name="science_technology_experiment_objective" id="stExperimentObjective" class="form-control science-technology-flow-field" rows="2" maxlength="2000">{{ old('science_technology_experiment_objective', data_get($post->meta, 'science_technology_experiment_objective')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stExperimentMaterials">Materials required</label>
            <textarea name="science_technology_experiment_materials" id="stExperimentMaterials" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ old('science_technology_experiment_materials', data_get($post->meta, 'science_technology_experiment_materials')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stExperimentProcedure">Procedure</label>
            <textarea name="science_technology_experiment_procedure" id="stExperimentProcedure" class="form-control science-technology-flow-field" rows="3" maxlength="4000">{{ old('science_technology_experiment_procedure', data_get($post->meta, 'science_technology_experiment_procedure')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stExperimentObservations">Observations</label>
            <textarea name="science_technology_experiment_observations" id="stExperimentObservations" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_experiment_observations', data_get($post->meta, 'science_technology_experiment_observations')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stExperimentResults">Results</label>
            <textarea name="science_technology_experiment_results" id="stExperimentResults" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_experiment_results', data_get($post->meta, 'science_technology_experiment_results')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="stExperimentSafety">Safety precautions</label>
            <textarea name="science_technology_experiment_safety" id="stExperimentSafety" class="form-control science-technology-flow-field" rows="2" maxlength="2000">{{ old('science_technology_experiment_safety', data_get($post->meta, 'science_technology_experiment_safety')) }}</textarea>
        </div>
    </div>
</div>

<div id="stInnovationSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Innovation section</h5>
            <p class="text-muted mb-0 small">One of the strongest sections — describe your innovation clearly.</p>
        </div>
        <span class="badge bg-success text-white">Innovation</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="stInnovationName">Innovation name</label>
            <input type="text" name="science_technology_innovation_name" id="stInnovationName" class="form-control science-technology-flow-field" maxlength="160" value="{{ old('science_technology_innovation_name', data_get($post->meta, 'science_technology_innovation_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stPatentFiled">Patent filed?</label>
            <select name="science_technology_patent_filed" id="stPatentFiled" class="form-select science-technology-flow-field">
                <option value="">Select status</option>
                @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyPatentStatuses() as $status)
                    <option value="{{ $status }}" @selected(old('science_technology_patent_filed', data_get($post->meta, 'science_technology_patent_filed')) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="stProblemSolved">Problem solved</label>
            <textarea name="science_technology_problem_solved" id="stProblemSolved" class="form-control science-technology-flow-field" rows="2" maxlength="2000">{{ old('science_technology_problem_solved', data_get($post->meta, 'science_technology_problem_solved')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stNovelFeatures">Novel features</label>
            <textarea name="science_technology_novel_features" id="stNovelFeatures" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ old('science_technology_novel_features', data_get($post->meta, 'science_technology_novel_features')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stInnovationTechnology">Technology used</label>
            <textarea name="science_technology_innovation_technology" id="stInnovationTechnology" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ old('science_technology_innovation_technology', data_get($post->meta, 'science_technology_innovation_technology')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stInnovationBenefits">Benefits</label>
            <textarea name="science_technology_innovation_benefits" id="stInnovationBenefits" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ old('science_technology_innovation_benefits', data_get($post->meta, 'science_technology_innovation_benefits')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stCommercialPotential">Commercial potential</label>
            <textarea name="science_technology_commercial_potential" id="stCommercialPotential" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ old('science_technology_commercial_potential', data_get($post->meta, 'science_technology_commercial_potential')) }}</textarea>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Technology used</h5>
            <p class="text-muted mb-0 small">Select all technologies relevant to this post.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyTechnologiesUsed() as $tech)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="science_technology_technologies_used[]" value="{{ $tech }}" class="form-check-input science-technology-flow-field" @checked(in_array($tech, (array) old('science_technology_technologies_used', data_get($post->meta, 'science_technology_technologies_used', [])), true))>
                <span class="form-check-label">{{ $tech }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="stSoftwareSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Software section</h5>
            <p class="text-muted mb-0 small">If applicable — programming languages, repository, and source code.</p>
        </div>
        <span class="badge bg-dark text-white">Software</span>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label d-block">Programming language</label>
            <div class="d-flex flex-wrap gap-2">
                @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyProgrammingLanguages() as $lang)
                    <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                        <input type="checkbox" name="science_technology_programming_languages[]" value="{{ $lang }}" class="form-check-input science-technology-flow-field" @checked(in_array($lang, (array) old('science_technology_programming_languages', data_get($post->meta, 'science_technology_programming_languages', [])), true))>
                        <span class="form-check-label">{{ $lang }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stGithubRepo">GitHub repository (optional)</label>
            <input type="url" name="science_technology_github_repo" id="stGithubRepo" class="form-control science-technology-flow-field" maxlength="255" value="{{ old('science_technology_github_repo', data_get($post->meta, 'science_technology_github_repo')) }}" placeholder="https://github.com/...">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stSourceCodeUpload">Source code upload (ZIP)</label>
            <input type="file" name="science_technology_source_code" id="stSourceCodeUpload" class="form-control science-technology-flow-field" accept=".zip,application/zip">
            @if(data_get($post->meta, 'science_technology_source_code.path'))
                <label class="form-check border rounded py-2 px-3 bg-white mb-0 mt-2">
                    <input type="checkbox" name="removed_science_technology_source_code" value="1" class="form-check-input science-technology-flow-field">
                    <span class="form-check-label">Remove {{ data_get($post->meta, 'science_technology_source_code.name', 'source code') }}</span>
                </label>
            @endif
        </div>
    </div>
</div>

<div id="stHardwareSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Hardware section</h5>
            <p class="text-muted mb-0 small">Components, circuit diagrams, PCB design, and bill of materials.</p>
        </div>
        <span class="badge bg-warning text-dark">Hardware</span>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="stHardwareComponents">Components used</label>
            <textarea name="science_technology_hardware_components" id="stHardwareComponents" class="form-control science-technology-flow-field" rows="3" maxlength="3000">{{ old('science_technology_hardware_components', data_get($post->meta, 'science_technology_hardware_components')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stCircuitDiagram">Circuit diagram upload</label>
            <input type="file" name="science_technology_circuit_diagram" id="stCircuitDiagram" class="form-control science-technology-flow-field" accept="image/*,.pdf">
            @if(data_get($post->meta, 'science_technology_circuit_diagram.path'))
                <label class="form-check border rounded py-2 px-3 bg-white mb-0 mt-2">
                    <input type="checkbox" name="removed_science_technology_circuit_diagram" value="1" class="form-check-input science-technology-flow-field">
                    <span class="form-check-label">Remove circuit diagram</span>
                </label>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stPcbDesign">PCB design</label>
            <input type="file" name="science_technology_pcb_design" id="stPcbDesign" class="form-control science-technology-flow-field" accept="image/*,.pdf,.zip">
            @if(data_get($post->meta, 'science_technology_pcb_design.path'))
                <label class="form-check border rounded py-2 px-3 bg-white mb-0 mt-2">
                    <input type="checkbox" name="removed_science_technology_pcb_design" value="1" class="form-check-input science-technology-flow-field">
                    <span class="form-check-label">Remove PCB design</span>
                </label>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stBom">BOM (bill of materials)</label>
            <textarea name="science_technology_bom" id="stBom" class="form-control science-technology-flow-field" rows="3" maxlength="4000">{{ old('science_technology_bom', data_get($post->meta, 'science_technology_bom')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="stHardwareCost">Cost</label>
            <input type="text" name="science_technology_hardware_cost" id="stHardwareCost" class="form-control science-technology-flow-field" maxlength="120" value="{{ old('science_technology_hardware_cost', data_get($post->meta, 'science_technology_hardware_cost')) }}" placeholder="e.g. ₹2,500">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-info-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Water &amp; soil technology</h5>
            <p class="text-muted mb-0 small">Flagship SoilnWater section — select relevant topics.</p>
        </div>
        <span class="badge bg-info text-dark">SoilnWater flagship</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyWaterSoilTopics() as $topic)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="science_technology_water_soil_topics[]" value="{{ $topic }}" class="form-check-input science-technology-flow-field" @checked(in_array($topic, (array) old('science_technology_water_soil_topics', data_get($post->meta, 'science_technology_water_soil_topics', [])), true))>
                <span class="form-check-label">{{ $topic }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Renewable energy</h5>
            <p class="text-muted mb-0 small">Select applicable renewable energy types.</p>
        </div>
        <span class="badge bg-success text-white">Optional</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyRenewableEnergyTypes() as $energy)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="science_technology_renewable_energy[]" value="{{ $energy }}" class="form-check-input science-technology-flow-field" @checked(in_array($energy, (array) old('science_technology_renewable_energy', data_get($post->meta, 'science_technology_renewable_energy', [])), true))>
                <span class="form-check-label">{{ $energy }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Application areas</h5>
            <p class="text-muted mb-0 small">Where can this technology or research be applied?</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyApplicationAreas() as $area)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="science_technology_application_areas[]" value="{{ $area }}" class="form-check-input science-technology-flow-field" @checked(in_array($area, (array) old('science_technology_application_areas', data_get($post->meta, 'science_technology_application_areas', [])), true))>
                <span class="form-check-label">{{ $area }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Patent / IPR details</h5>
            <p class="text-muted mb-0 small">Optional intellectual property information.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="stPatentNumber">Patent number</label>
            <input type="text" name="science_technology_patent_number" id="stPatentNumber" class="form-control science-technology-flow-field" maxlength="120" value="{{ old('science_technology_patent_number', data_get($post->meta, 'science_technology_patent_number')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="stApplicationNumber">Application number</label>
            <input type="text" name="science_technology_application_number" id="stApplicationNumber" class="form-control science-technology-flow-field" maxlength="120" value="{{ old('science_technology_application_number', data_get($post->meta, 'science_technology_application_number')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="stPatentStatus">Patent status</label>
            <select name="science_technology_patent_status" id="stPatentStatus" class="form-select science-technology-flow-field">
                <option value="">Select status</option>
                @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyPatentIprStatuses() as $status)
                    <option value="{{ $status }}" @selected(old('science_technology_patent_status', data_get($post->meta, 'science_technology_patent_status')) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Funding details</h5>
            <p class="text-muted mb-0 small">Optional — how was this work funded?</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyFundingTypes() as $funding)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="science_technology_funding_types[]" value="{{ $funding }}" class="form-check-input science-technology-flow-field" @checked(in_array($funding, (array) old('science_technology_funding_types', data_get($post->meta, 'science_technology_funding_types', [])), true))>
                <span class="form-check-label">{{ $funding }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">References</h5>
            <p class="text-muted mb-0 small">Very important — research papers, DOI, books, reports, standards, and web references.</p>
        </div>
        <span class="badge bg-danger text-white">Important</span>
    </div>
    <div class="mb-3">
        <label class="form-label d-block">Reference types included</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyReferenceTypes() as $refType)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="science_technology_reference_types[]" value="{{ $refType }}" class="form-check-input science-technology-flow-field" @checked(in_array($refType, (array) old('science_technology_reference_types', data_get($post->meta, 'science_technology_reference_types', [])), true))>
                    <span class="form-check-label">{{ $refType }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <label class="form-label" for="stReferencesContent">References</label>
    <textarea name="science_technology_references" id="stReferencesContent" class="form-control science-technology-flow-field" rows="5" maxlength="8000" placeholder="List citations, DOI links, URLs, etc.">{{ old('science_technology_references', data_get($post->meta, 'science_technology_references')) }}</textarea>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">License</h5>
            <p class="text-muted mb-0 small">Optional — how others may use your work.</p>
        </div>
    </div>
    <select name="science_technology_license" id="stLicense" class="form-select science-technology-flow-field">
        <option value="">Select license (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyLicenseOptions() as $license)
            <option value="{{ $license }}" @selected(old('science_technology_license', data_get($post->meta, 'science_technology_license')) === $license)>{{ $license }}</option>
        @endforeach
    </select>
</div>
