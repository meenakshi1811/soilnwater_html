@php
    $prefix = $prefix ?? 'discussionWidget';
    $mode = $mode ?? 'topic';
    $titleInputId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicTitle' : "{$prefix}Title";
    $bodyInputId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicBody' : "{$prefix}Body";
    $attachImageBtnId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachImageBtn' : "{$prefix}AttachImageBtn";
    $attachVideoBtnId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachVideoBtn' : "{$prefix}AttachVideoBtn";
    $attachDocumentBtnId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachDocumentBtn' : "{$prefix}AttachDocumentBtn";
    $attachmentsInputId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachments' : "{$prefix}Attachments";
    $attachmentsPreviewId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicPreview' : "{$prefix}AttachmentsPreview";
    $fieldClass = $fieldClass ?? 'discussion-widget__field';
    $selectedSummaryId = in_array($prefix, ['discussionWidgetGroup', 'newTopicGroup'], true)
        ? "{$prefix}SelectedSummary"
        : "{$prefix}GroupSelectedSummary";
@endphp

<input type="hidden" name="is_group" value="{{ $mode === 'group' ? '1' : '0' }}">

@if($mode === 'group')
    <div class="{{ $fieldClass }} discussion-group-pick__summary" id="{{ $selectedSummaryId }}"></div>
@endif

<div class="{{ $fieldClass }}">
    <label for="{{ $titleInputId }}">{{ $mode === 'group' ? 'Group subject' : 'Topic name' }}</label>
    <input type="text"
           id="{{ $titleInputId }}"
           name="title"
           maxlength="200"
           required
           placeholder="{{ $mode === 'group' ? 'e.g. Farm planning team' : 'e.g. Soil health tips' }}">
</div>

<div class="{{ $fieldClass }}">
    <label for="{{ $bodyInputId }}">First message <span>(optional)</span></label>
    <textarea id="{{ $bodyInputId }}"
              name="body"
              rows="4"
              maxlength="5000"
              placeholder="Write your first message…"></textarea>
</div>

<div class="{{ $fieldClass }}">
    <label>Attachments</label>
    <div class="discussion-widget__attach-group discussion-widget__attach-group--stack">
        <button type="button" class="discussion-widget__attach-type-btn" id="{{ $attachImageBtnId }}">
            <i class="fa-solid fa-image"></i>
            <span>Image</span>
        </button>
        <button type="button" class="discussion-widget__attach-type-btn" id="{{ $attachVideoBtnId }}">
            <i class="fa-solid fa-video"></i>
            <span>Video</span>
        </button>
        <button type="button" class="discussion-widget__attach-type-btn" id="{{ $attachDocumentBtnId }}">
            <i class="fa-solid fa-file-lines"></i>
            <span>Document</span>
        </button>
    </div>
    <input type="file" id="{{ $attachmentsInputId }}" name="attachments[]" class="visually-hidden" multiple>
    <div class="discussion-media-preview" id="{{ $attachmentsPreviewId }}" hidden></div>
</div>
