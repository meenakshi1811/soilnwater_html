@php
    /** @var \App\Models\AdTemplate $template */
    $placeholder = asset('assets/images/ad-sample.png');
    $previewImage = !empty($template->preview_image) ? asset($template->preview_image) : null;
    $schema = is_array($template->schema_json) ? $template->schema_json : [];
    $schemaFields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];

    $previewHtml = \App\Support\AdTemplatePreview::render(
        $template->layout_html,
        \App\Support\AdTemplatePreview::sampleFieldsForSchema($schemaFields, (string) $template->name),
        $placeholder
    );
@endphp

<div
    class="ads-template-preview ads-mini-preview js-ads-scaled-preview"
    style="aspect-ratio: {{ $size['ratio'] }};"
    data-source-width="{{ $size['w'] }}"
    data-source-height="{{ $size['h'] }}"
>
    @if($previewImage)
        <img src="{{ $previewImage }}" alt="{{ $template->name }} preview" class="w-100 h-100 object-fit-contain rounded p-1 bg-white">
    @else
        <div class="ads-mini-preview-inner">
            <div class="ad-canvas">
                {!! $previewHtml !!}
            </div>
        </div>
    @endif
</div>
