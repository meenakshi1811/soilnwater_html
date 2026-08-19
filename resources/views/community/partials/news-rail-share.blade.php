@php
    $shareUrl = $post->shareUrl();
    $shareLabel = $post->title;
@endphp

@include('community.partials.share-panel', [
    'post' => $post,
    'shareUrl' => $shareUrl,
    'shareLabel' => $shareLabel,
])

<div class="community-news-social community-news-social--share">
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="community-news-social__icon community-news-social__icon--facebook"
        title="Share on Facebook"
        aria-label="Share on Facebook"
    >
        <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
    </a>
    <a
        href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareLabel) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="community-news-social__icon community-news-social__icon--x"
        title="Share on X"
        aria-label="Share on X"
    >
        <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
    </a>
    <a
        href="https://wa.me/?text={{ urlencode($shareLabel.' '.$shareUrl) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="community-news-social__icon community-news-social__icon--whatsapp"
        title="Share on WhatsApp"
        aria-label="Share on WhatsApp"
    >
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>
    <a
        href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="community-news-social__icon community-news-social__icon--linkedin"
        title="Share on LinkedIn"
        aria-label="Share on LinkedIn"
    >
        <i class="fa-brands fa-linkedin-in" aria-hidden="true"></i>
    </a>
    <button
        type="button"
        class="community-news-social__icon community-news-social__icon--copy js-community-share-trigger"
        data-bs-toggle="modal"
        data-bs-target="#communityShareModal"
        data-share-url="{{ $shareUrl }}"
        data-share-title="{{ $shareLabel }}"
        data-share-track-url="{{ route('community.share.track', $post) }}"
        title="Copy link"
        aria-label="Copy link"
    >
        <i class="fa-solid fa-link" aria-hidden="true"></i>
    </button>
</div>
