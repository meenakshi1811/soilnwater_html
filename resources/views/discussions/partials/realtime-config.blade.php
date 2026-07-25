@php
    $broadcastEnabled = config('broadcasting.default') !== 'log' && config('broadcasting.default') !== 'null';
    $replyReactTemplate = str_replace('999999999', '__REPLY__', route('discussions.replies.react', ['reply' => 999999999]));
@endphp
<script>
    window.soilnwaterDiscussion = {
        broadcastEnabled: @json($broadcastEnabled),
        pusher: {
            key: @json(config('broadcasting.connections.pusher.key')),
            cluster: @json(config('broadcasting.connections.pusher.options.cluster')),
            host: @json(env('PUSHER_HOST')),
            port: @json(config('broadcasting.connections.pusher.options.port', 443)),
            scheme: @json(config('broadcasting.connections.pusher.options.scheme', 'https')),
        },
        authEndpoint: @json(url('/broadcasting/auth')),
        csrfToken: @json(csrf_token()),
        routes: {
            discussionsIndex: @json(route('discussions.index')),
            replyReactTemplate: @json($replyReactTemplate),
        },
        currentUserId: @json(auth()->id()),
        topicId: @json(isset($topic) ? $topic->id : null),
        reactionLabels: @json(\App\Support\DiscussionReactions::labels()),
    };
</script>
@if($broadcastEnabled)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.19.0/dist/echo.iife.js"></script>
    <script src="{{ asset('assets/js/discussion-echo.js') }}?v={{ now()->timestamp }}" defer></script>
    @endpush
@endif
