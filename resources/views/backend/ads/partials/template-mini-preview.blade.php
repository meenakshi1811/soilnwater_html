@php
    /** @var \App\Models\AdTemplate $template */
    $placeholder = asset('assets/images/ad-sample.png');
    $previewHtml = \App\Support\AdTemplatePreview::render(
        $template->layout_html,
        \App\Support\AdTemplatePreview::sampleFieldsForTemplateName($template->name),
        $placeholder
    );
@endphp

<div class="ads-template-preview ads-mini-preview" style="aspect-ratio: {{ $size['ratio'] }};">
    <div class="ads-mini-preview-inner">
        {!! $previewHtml !!}
    </div>
</div>

