(function () {
    const config = window.soilnwaterDiscussion || {};

    if (!config.broadcastEnabled || !window.Pusher || !window.Echo) {
        return;
    }

    const pusher = config.pusher || {};
    const echoOptions = {
        broadcaster: 'pusher',
        key: pusher.key,
        authEndpoint: config.authEndpoint,
        auth: {
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
            },
        },
    };

    if (pusher.host) {
        const scheme = pusher.scheme || 'https';
        const port = pusher.port || 443;

        echoOptions.wsHost = pusher.host;
        echoOptions.wsPort = port;
        echoOptions.wssPort = port;
        echoOptions.forceTLS = scheme === 'https';
        echoOptions.enabledTransports = ['ws', 'wss'];
    } else {
        echoOptions.cluster = pusher.cluster || 'mt1';
        echoOptions.forceTLS = true;
    }

    window.Pusher = Pusher;
    window.Echo = new Echo(echoOptions);

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

            const topicId = payload.reply.discussion_topic_id;
            if (activeTopicId() && Number(topicId) === Number(activeTopicId())) {
                ui().appendReply?.(payload.reply);
                return;
            }

            ui().incrementTopicUnread?.(topicId, 1);
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
