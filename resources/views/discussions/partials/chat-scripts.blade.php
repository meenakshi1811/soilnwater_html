@php
    use App\Support\DiscussionAttachments;
    $broadcastEnabled = config('broadcasting.default') !== 'log' && config('broadcasting.default') !== 'null';
    $discussionPageMode = $discussionPageMode ?? request()->routeIs('discussions.messenger');
    $discussionInitialTopicId = $discussionInitialTopicId ?? ($discussionPageMode ? request()->route('topic')?->id : null);
    $discussionInitialCompose = $discussionInitialCompose ?? ($discussionPageMode && request()->boolean('compose'));
    $replyReactTemplate = str_replace('999999999', '__REPLY__', route('discussions.replies.react', ['reply' => 999999999]));
    $topicShowTemplate = str_replace('999999999', '__TOPIC__', route('discussions.show', ['topic' => 999999999]));
    $topicRepliesTemplate = str_replace('999999999', '__TOPIC__', route('discussions.replies.store', ['topic' => 999999999]));
    $topicPinTemplate = str_replace('999999999', '__TOPIC__', route('discussions.pin', ['topic' => 999999999]));
    $topicReactTemplate = str_replace('999999999', '__TOPIC__', route('discussions.react', ['topic' => 999999999]));
    $topicReadTemplate = str_replace('999999999', '__TOPIC__', route('discussions.read', ['topic' => 999999999]));
    $topicMembersTemplate = str_replace('999999999', '__TOPIC__', route('discussions.members.index', ['topic' => 999999999]));
    $topicMembersStoreTemplate = str_replace('999999999', '__TOPIC__', route('discussions.members.store', ['topic' => 999999999]));
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
            discussionsStore: @json(route('discussions.store')),
            messenger: @json(route('discussions.messenger')),
            topicShowTemplate: @json($topicShowTemplate),
            topicRepliesTemplate: @json($topicRepliesTemplate),
            topicPinTemplate: @json($topicPinTemplate),
            topicReactTemplate: @json($topicReactTemplate),
            topicReadTemplate: @json($topicReadTemplate),
            topicMembersTemplate: @json($topicMembersTemplate),
            topicMembersStoreTemplate: @json($topicMembersStoreTemplate),
            usersSearch: @json(route('discussions.users.search')),
            unreadSummary: @json(route('discussions.unread-summary')),
            replyReactTemplate: @json($replyReactTemplate),
        },
        currentUserId: @json(auth()->id()),
        currentUserName: @json(auth()->user()?->name ?? auth()->user()?->full_name ?? 'You'),
        canPin: @json(auth()->user()?->isAdmin() ?? false),
        topicId: null,
        reactionLabels: @json(\App\Support\DiscussionReactions::labels()),
        reactionIcons: @json(\App\Support\DiscussionReactions::icons()),
        unreadTopics: {},
        globalUnread: 0,
        pageMode: @json($discussionPageMode),
        initialTopicId: @json($discussionInitialTopicId),
        initialCompose: @json($discussionInitialCompose ?? false),
        widgetSize: 'default',
        attachments: {
            acceptImages: @json(DiscussionAttachments::acceptImages()),
            acceptVideos: @json(DiscussionAttachments::acceptVideos()),
            acceptDocuments: @json(DiscussionAttachments::acceptDocuments()),
            documentIcons: @json(DiscussionAttachments::documentIconMap()),
        },
    };
</script>
<script src="{{ asset('assets/js/discussion-attachments.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/discussion-compose.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/discussion.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/discussion-widget.js') }}?v={{ now()->timestamp }}" defer></script>
@if($broadcastEnabled)
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.19.0/dist/echo.iife.js" defer></script>
<script src="{{ asset('assets/js/discussion-echo.js') }}?v={{ now()->timestamp }}" defer></script>
@endif
