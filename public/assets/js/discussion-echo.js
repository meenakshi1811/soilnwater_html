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

    const ui = window.soilnwaterDiscussionUi || {};
    const currentUserId = config.currentUserId;
    const topicId = config.topicId;

    function shouldIgnoreOwnReply(reply) {
        return reply?.author?.id === currentUserId;
    }

    window.Echo.private('discussion')
        .listen('.topic.created', (payload) => {
            if (payload.topic?.author?.id === currentUserId) {
                return;
            }

            ui.prependTopic?.(payload.topic);
        })
        .listen('.topic.pinned', (payload) => {
            ui.reorderTopicPin?.(payload.topic_id, payload.is_pinned);
            if (topicId && payload.topic_id === topicId) {
                ui.updateTopicPinButton?.(payload.is_pinned);
            }
        })
        .listen('.reply.created', (payload) => {
            if (!payload.reply || shouldIgnoreOwnReply(payload.reply)) {
                return;
            }

            if (topicId && payload.reply.discussion_topic_id === topicId) {
                ui.appendReply?.(payload.reply);
            }
        })
        .listen('.reaction.updated', (payload) => {
            const container = ui.findReactionContainer?.(payload.reactable_type, payload.reactable_id);
            if (!container) {
                return;
            }

            ui.updateReactionButtons?.(container, payload.reaction, payload.active, payload.counts || {});
        });

    if (topicId) {
        window.Echo.private(`discussion.topic.${topicId}`)
            .listen('.reply.created', (payload) => {
                if (!payload.reply || shouldIgnoreOwnReply(payload.reply)) {
                    return;
                }

                ui.appendReply?.(payload.reply);
            })
            .listen('.topic.pinned', (payload) => {
                ui.updateTopicPinButton?.(payload.is_pinned);
            })
            .listen('.reaction.updated', (payload) => {
                const container = ui.findReactionContainer?.(payload.reactable_type, payload.reactable_id);
                if (!container) {
                    return;
                }

                ui.updateReactionButtons?.(container, payload.reaction, payload.active, payload.counts || {});
            });
    }
})();
