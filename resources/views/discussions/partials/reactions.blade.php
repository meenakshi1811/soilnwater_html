@include('discussions.partials.reactions-menu', [
    'reactableType' => $reactableType,
    'reactableId' => $reactableId,
    'reactUrl' => $reactUrl,
    'counts' => $counts,
    'userReactions' => $userReactions,
])
