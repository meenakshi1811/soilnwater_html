<div class="modal fade" id="newTopicModal" tabindex="-1" aria-labelledby="newTopicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTopicModalLabel">New chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="discussion-modal-compose-tabs">
                    <button type="button" class="discussion-modal-compose-tabs__btn is-active" data-modal-tab="topic">
                        <i class="fa-solid fa-hashtag"></i> Topic
                    </button>
                    <button type="button" class="discussion-modal-compose-tabs__btn" data-modal-tab="group">
                        <i class="fa-solid fa-user-group"></i> Group
                    </button>
                </div>

                <div class="discussion-modal-compose-panel is-active" data-modal-panel="topic">
                    <form id="newTopicForm" class="p-3" data-url="{{ route('discussions.store') }}" data-compose-mode="topic" enctype="multipart/form-data">
                        @csrf
                        @include('discussions.partials.new-chat-compose-fields', [
                            'prefix' => 'newTopic',
                            'mode' => 'topic',
                            'fieldClass' => 'mb-3',
                        ])
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa-solid fa-hashtag"></i> Create topic
                        </button>
                    </form>
                </div>

                <div class="discussion-modal-compose-panel" data-modal-panel="groupPick">
                    @include('discussions.partials.group-member-picker', ['prefix' => 'newTopic'])
                </div>

                <div class="discussion-modal-compose-panel" data-modal-panel="group">
                    <form id="newGroupForm" class="p-3" data-url="{{ route('discussions.store') }}" data-compose-mode="group" enctype="multipart/form-data">
                        @csrf
                        @include('discussions.partials.new-chat-compose-fields', [
                            'prefix' => 'newTopicGroup',
                            'mode' => 'group',
                            'fieldClass' => 'mb-3',
                        ])
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="newTopicGroupBackBtn">Back</button>
                            <button type="submit" class="btn btn-success flex-grow-1">
                                <i class="fa-solid fa-user-group"></i> Create group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
