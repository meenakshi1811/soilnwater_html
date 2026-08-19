@php
    $shareUrl = $post->shareUrl();
    $shareLabel = $post->title;
    $whatsappText = 'Check out this community post: '.$shareUrl;
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
        href="https://www.instagram.com/?url={{ urlencode($shareUrl) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="community-news-social__icon community-news-social__icon--instagram"
        title="Share on Instagram"
        aria-label="Share on Instagram"
    >
        <i class="fa-brands fa-instagram" aria-hidden="true"></i>
    </a>
    <a
        href="https://wa.me/?text={{ urlencode($whatsappText) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="community-news-social__icon community-news-social__icon--whatsapp"
        title="Share on WhatsApp"
        aria-label="Share on WhatsApp"
    >
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>
</div>
