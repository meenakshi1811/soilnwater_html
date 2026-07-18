(function () {
    const config = window.soilnwaterDiscussion || {};

    if (!config.broadcastEnabled || !window.Pusher || !window.Echo) {
        return;
    }

    const scheme = config.reverb?.scheme || 'http';
    const port = config.reverb?.port || 8080;

    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: config.reverb.key,
        wsHost: config.reverb.host,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: config.authEndpoint,
        auth: {
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
            },
        },
    });

    const currentUserId = config.currentUserId;

    function ui() {
        return window.soilnwaterDiscussionUi || {};
    }

    function activeTopicId() {
        return window.soilnwaterDiscussion?.topicId || null;
    }

    function shouldIgnoreOwnReply(reply) {
        return reply?.author?.id === currentUserId;
    }

    window.Echo.private('discussion')
        .listen('.topic.created', (payload) => {
            if (payload.topic?.author?.id === currentUserId) {
                return;
            }

            ui().prependTopic?.(payload.topic);
        })
        .listen('.topic.pinned', (payload) => {
            ui().reorderTopicPin?.(payload.topic_id, payload.is_pinned);
            if (activeTopicId() && Number(payload.topic_id) === Number(activeTopicId())) {
                ui().updateTopicPinButton?.(payload.is_pinned);
            }
        })
        .listen('.reply.created', (payload) => {
            if (!payload.reply || shouldIgnoreOwnReply(payload.reply)) {
                return;
            }

            if (activeTopicId() && Number(payload.reply.discussion_topic_id) === Number(activeTopicId())) {
                ui().appendReply?.(payload.reply);
            }
        })
        .listen('.reaction.updated', (payload) => {
            const helpers = ui();
            const container = helpers.findReactionContainer?.(payload.reactable_type, payload.reactable_id);
            if (!container) {
                return;
            }

            helpers.updateReactionButtons?.(container, payload.reaction, payload.active, payload.counts || {});
        });
})();
