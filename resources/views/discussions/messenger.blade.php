@extends('frontend.layouts.messenger')

@section('meta_title', 'Chats – SoilnWater')

@section('content')
<div class="discussion-messenger-page">
    @include('discussions.partials.widget-shell', ['standalone' => true])
</div>
@endsection

@push('scripts')
    @include('discussions.partials.chat-scripts', [
        'discussionPageMode' => true,
        'discussionInitialTopicId' => $initialTopicId,
    ])
@endpush
