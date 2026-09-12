@php
  $editorPrefix = $editorPrefix ?? 'note';
  $editorFieldName = $editorFieldName ?? 'meta[custom_note_html]';
  $languageFieldName = $languageFieldName ?? 'meta[editor_language]';
  $textareaId = $editorPrefix.'BodyEditor';
  $mountId = $editorPrefix.'BodyEditorMount';
  $languageSelectId = $editorPrefix.'EditorLanguageSelect';
  $languageHiddenId = $editorPrefix.'EditorLanguageHidden';
  $transliterationHintId = $editorPrefix.'EditorTransliterationHint';
  $initialContent = $initialContent ?? '';
  $initialLanguage = $initialLanguage ?? 'en';
@endphp

<div id="{{ $mountId }}" class="community-body-editor-mount border rounded-3 bg-white p-2">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1">
    <div>
      <label for="{{ $languageSelectId }}" class="form-label mb-0 small fw-semibold">Editor language</label>
      <small class="text-muted d-block">Default is English. Choose Hinglish for in-editor phonetic typing, or Hindi to type with the Windows Hindi keyboard.</small>
    </div>
    <select id="{{ $languageSelectId }}" class="form-select form-select-sm community-editor-language-select">
      @foreach(\App\Support\CommunityContentTaxonomy::standardEditorLanguages() as $code => $label)
        <option value="{{ $code }}" @selected($initialLanguage === $code)>{{ $label }}</option>
      @endforeach
    </select>
    <input type="hidden" name="{{ $languageFieldName }}" id="{{ $languageHiddenId }}" value="{{ $initialLanguage }}">
  </div>
  <div id="{{ $transliterationHintId }}" class="alert alert-info py-2 px-3 small mb-2 d-none" role="status"></div>
  <textarea name="{{ $editorFieldName }}" id="{{ $textareaId }}" class="form-control" rows="12">{{ $initialContent }}</textarea>
</div>
