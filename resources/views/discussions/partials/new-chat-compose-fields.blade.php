@php
    $prefix = $prefix ?? 'discussionWidget';
    $titleInputId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicTitle' : "{$prefix}Title";
    $bodyInputId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicBody' : "{$prefix}Body";
    $membersFieldId = $prefix === 'discussionWidget' ? 'discussionWidgetMembersField' : "{$prefix}MembersField";
    $memberSearchId = $prefix === 'discussionWidget' ? 'discussionWidgetMemberSearch' : "{$prefix}MemberSearch";
    $memberResultsId = $prefix === 'discussionWidget' ? 'discussionWidgetMemberResults' : "{$prefix}MemberResults";
    $memberChipsId = $prefix === 'discussionWidget' ? 'discussionWidgetMemberChips' : "{$prefix}MemberChips";
    $attachImageBtnId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachImageBtn' : "{$prefix}AttachImageBtn";
    $attachVideoBtnId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachVideoBtn' : "{$prefix}AttachVideoBtn";
    $attachDocumentBtnId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachDocumentBtn' : "{$prefix}AttachDocumentBtn";
    $attachmentsInputId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicAttachments' : "{$prefix}Attachments";
    $attachmentsPreviewId = $prefix === 'discussionWidget' ? 'discussionWidgetTopicPreview' : "{$prefix}AttachmentsPreview";
    $fieldClass = $fieldClass ?? 'discussion-widget__field';
@endphp

<div class="{{ $fieldClass }}">
    <label>Chat type</label>
    <div class="discussion-widget__type-toggle" role="radiogroup" aria-label="Chat type">
        <label class="discussion-widget__type-option">
            <input type="radio" name="is_group" value="0" checked>
            <span><i class="fa-solid fa-hashtag"></i> Public topic</span>
        </label>
        <label class="discussion-widget__type-option">
            <input type="radio" name="is_group" value="1">
            <span><i class="fa-solid fa-users"></i> Private group</span>
        </label>
    </div>
</div>
<div class="{{ $fieldClass }}">
    <label for="{{ $titleInputId }}">Group / topic name</label>
    <input type="text"
           id="{{ $titleInputId }}"
           name="title"
           maxlength="200"
           required
           placeholder="e.g. Soil health tips">
</div>
<div class="{{ $fieldClass }}" id="{{ $membersFieldId }}" hidden>
    <label for="{{ $memberSearchId }}">Add members</label>
    <div class="discussion-widget__member-search-wrap">
        <input type="search"
               id="{{ $memberSearchId }}"
               class="form-control"
               placeholder="Search members by name or email"
               autocomplete="off">
        <div class="discussion-widget__member-results" id="{{ $memberResultsId }}" hidden></div>
    </div>
    <div class="discussion-widget__member-chips" id="{{ $memberChipsId }}"></div>
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
        <button type="button"
                class="discussion-widget__attach-type-btn"
                id="{{ $attachImageBtnId }}">
            <i class="fa-solid fa-image"></i>
            <span>Image</span>
        </button>
        <button type="button"
                class="discussion-widget__attach-type-btn"
                id="{{ $attachVideoBtnId }}">
            <i class="fa-solid fa-video"></i>
            <span>Video</span>
        </button>
        <button type="button"
                class="discussion-widget__attach-type-btn"
                id="{{ $attachDocumentBtnId }}">
            <i class="fa-solid fa-file-lines"></i>
            <span>Document</span>
        </button>
    </div>
    <input type="file"
           id="{{ $attachmentsInputId }}"
           name="attachments[]"
           class="visually-hidden"
           multiple>
    <div class="discussion-media-preview" id="{{ $attachmentsPreviewId }}" hidden></div>
</div>
