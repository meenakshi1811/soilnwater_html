@extends('frontend.layouts.app')

@section('meta_title', 'Chats – SoilnWater')

@section('content')
<div class="discussion-messenger-page">
    <div class="discussion-messenger-page__toolbar">
        <div class="discussion-messenger-page__toolbar-start">
            <a href="{{ route('discussions.index') }}" class="discussion-btn discussion-btn--outline discussion-btn--sm">
                <i class="fa-solid fa-list"></i> All chats
            </a>
        </div>
        <div class="discussion-messenger-page__toolbar-end">
            <span class="discussion-messenger-page__hint">Full-screen messenger</span>
        </div>
    </div>
    <div class="discussion-messenger-page__mount" id="discussionMessengerMount" aria-hidden="true"></div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('discussion-messenger-page');
    });
</script>
@endpush
