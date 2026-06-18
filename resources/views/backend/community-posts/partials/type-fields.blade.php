@foreach(\App\Support\CommunityPostFormFields::sections() as $typeKey => $section)
    @continue(empty($section['fields']))
    <div class="col-12 type-extra type-fields-flow" data-for="{{ $typeKey }}">
        <div class="type-fields-card border rounded-3 p-3 bg-light">
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
                <div>
                    <h5 class="mb-1">{{ $section['title'] }}</h5>
                    <p class="text-muted mb-0 small">{{ $section['description'] }}</p>
                </div>
                <span class="badge bg-secondary text-white">{{ $section['badge'] }}</span>
            </div>
            <div class="row g-3">
                @foreach($section['fields'] as $field)
                    @php
                        $fieldName = $field['name'];
                        $fieldValue = old($fieldName, data_get($post->meta, $fieldName));
                        $isRequired = (bool) ($field['required'] ?? false);
                        $requiredClass = $isRequired ? ' type-field-required' : '';
                    @endphp
                    <div class="{{ $field['col'] ?? 'col-md-6' }}">
                        @if(($field['type'] ?? 'text') === 'checkbox')
                            <div class="form-check mt-2">
                                <input
                                    type="checkbox"
                                    name="{{ $fieldName }}"
                                    value="1"
                                    class="form-check-input{{ $requiredClass }}"
                                    id="{{ $fieldName }}"
                                    @checked(filter_var($fieldValue, FILTER_VALIDATE_BOOLEAN))
                                >
                                <label class="form-check-label" for="{{ $fieldName }}">{{ $field['label'] }}</label>
                            </div>
                        @else
                            <label class="form-label" for="{{ $fieldName }}">
                                {{ $field['label'] }}
                                @if($isRequired)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @switch($field['type'])
                                @case('textarea')
                                    <textarea
                                        name="{{ $fieldName }}"
                                        id="{{ $fieldName }}"
                                        class="form-control{{ $requiredClass }}"
                                        rows="{{ $field['rows'] ?? 3 }}"
                                        maxlength="{{ $field['max'] ?? 2000 }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                    >{{ $fieldValue }}</textarea>
                                    @break

                                @case('select')
                                    <select name="{{ $fieldName }}" id="{{ $fieldName }}" class="form-select{{ $requiredClass }}">
                                        <option value="">Select {{ strtolower($field['label']) }}</option>
                                        @foreach($field['options'] as $option)
                                            <option value="{{ $option }}" @selected((string) $fieldValue === (string) $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('url')
                                    <input
                                        type="url"
                                        name="{{ $fieldName }}"
                                        id="{{ $fieldName }}"
                                        class="form-control{{ $requiredClass }}"
                                        value="{{ $fieldValue }}"
                                        maxlength="255"
                                        placeholder="https://example.com"
                                    >
                                    @break

                                @case('date')
                                    <input
                                        type="date"
                                        name="{{ $fieldName }}"
                                        id="{{ $fieldName }}"
                                        class="form-control{{ $requiredClass }}"
                                        value="{{ $fieldValue }}"
                                    >
                                    @break

                                @case('datetime-local')
                                    <input
                                        type="datetime-local"
                                        name="{{ $fieldName }}"
                                        id="{{ $fieldName }}"
                                        class="form-control{{ $requiredClass }}"
                                        value="{{ $fieldValue }}"
                                    >
                                    @break

                                @default
                                    <input
                                        type="text"
                                        name="{{ $fieldName }}"
                                        id="{{ $fieldName }}"
                                        class="form-control{{ $requiredClass }}"
                                        value="{{ $fieldValue }}"
                                        maxlength="{{ $field['max'] ?? 255 }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                    >
                            @endswitch

                            @if(!empty($field['placeholder']) && ($field['type'] ?? 'text') !== 'textarea')
                                <small class="text-muted">{{ $field['placeholder'] }}</small>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
